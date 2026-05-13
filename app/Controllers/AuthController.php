<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\SmsService;

class AuthController extends Controller
{
    public function login(): void
    {
        view('auth/login', ['title' => 'Sign In']);
    }

    public function authenticate(): void
    {
        verify_csrf();
        $errors = Validator::required($_POST, ['email' => 'Email', 'password' => 'Password']);
        $errors = array_merge($errors, Validator::email($_POST['email'] ?? '', 'Email'));
        $user = $errors ? null : User::findByEmail(trim($_POST['email']));

        if (!$user || !password_verify((string) $_POST['password'], $user['password_hash']) || $user['status'] !== 'active') {
            flash('danger', 'Invalid credentials or inactive account.');
            redirect('login');
        }

        $otp = (string) random_int(100000, 999999);
        $_SESSION['pending_login'] = [
            'user_id' => (int) $user['id'],
            'otp_hash' => password_hash($otp, PASSWORD_BCRYPT),
            'expires_at' => time() + 600,
            'attempts' => 0,
        ];

        $sms = new SmsService();
        $otpPhone = $sms->normalizePhone($user['phone'] ?? '') ?: $sms->normalizePhone(config('twilio.otp_fallback_phone'));

        if (!$otpPhone) {
            unset($_SESSION['pending_login']);
            flash('danger', 'No valid phone number is available for SMS OTP. Add a phone number in E.164 format, e.g. +639171234567.');
            redirect('login');
        }

        if (!$sms->sendLoginOtp($user, $otp, $otpPhone)) {
            unset($_SESSION['pending_login']);
            flash('danger', 'The password was correct, but the SMS OTP could not be sent. Check Twilio settings and SMS logs.');
            redirect('login');
        }

        ActivityLogger::log('login_otp_sent', 'Sent login OTP SMS.', (int) $user['id']);
        flash('success', 'We sent a login OTP to your registered phone number.');
        redirect('otp');
    }

    public function otp(): void
    {
        if (empty($_SESSION['pending_login'])) {
            flash('warning', 'Please sign in first.');
            redirect('login');
        }

        view('auth/otp', ['title' => 'Verify Login']);
    }

    public function verifyOtp(): void
    {
        verify_csrf();
        $pending = $_SESSION['pending_login'] ?? null;
        if (!$pending) {
            flash('warning', 'Please sign in first.');
            redirect('login');
        }

        if (($pending['expires_at'] ?? 0) < time()) {
            unset($_SESSION['pending_login']);
            flash('danger', 'Your OTP expired. Please sign in again.');
            redirect('login');
        }

        $_SESSION['pending_login']['attempts'] = (int) ($_SESSION['pending_login']['attempts'] ?? 0) + 1;
        if ($_SESSION['pending_login']['attempts'] > 5) {
            unset($_SESSION['pending_login']);
            flash('danger', 'Too many OTP attempts. Please sign in again.');
            redirect('login');
        }

        if (!password_verify(trim($_POST['otp'] ?? ''), $pending['otp_hash'])) {
            flash('danger', 'Invalid OTP code.');
            redirect('otp');
        }

        $user = User::find((int) $pending['user_id']);
        unset($_SESSION['pending_login']);
        if (!$user || $user['status'] !== 'active') {
            flash('danger', 'Account is no longer active.');
            redirect('login');
        }

        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        ActivityLogger::log('login', 'Signed in with OTP verification.', (int) $user['id']);
        flash('success', 'Welcome back, ' . $user['name'] . '.');
        redirect($user['role'] === 'admin' ? 'dashboard' : 'items');
    }

    public function register(): void
    {
        view('auth/register', ['title' => 'Create Account']);
    }

    public function store(): void
    {
        verify_csrf();
        $errors = Validator::required($_POST, [
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone number',
            'password' => 'Password',
        ]);
        $errors = array_merge($errors, Validator::email($_POST['email'] ?? '', 'Email'));
        if (!(new SmsService())->normalizePhone($_POST['phone'] ?? '')) {
            $errors[] = 'Phone number must use E.164 format, e.g. +639171234567.';
        }

        $password = (string) ($_POST['password'] ?? '');
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character.';
        }

        if (User::findByEmail(trim($_POST['email'] ?? ''))) {
            $errors[] = 'Email is already registered.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                flash('danger', $error);
            }
            redirect('register');
        }

        $id = User::create([
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'password' => $_POST['password'],
            'role' => 'user',
        ]);
        ActivityLogger::log('register', 'Created a new user account.', $id);
        flash('success', 'Account created. You can now sign in.');
        redirect('login');
    }

    public function logout(): void
    {
        ActivityLogger::log('logout', 'Signed out of the system.');
        unset($_SESSION['user']);
        flash('success', 'You have been signed out.');
        redirect('login');
    }
}
