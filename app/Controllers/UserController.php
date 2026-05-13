<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\SmsService;

class UserController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();
        view('users/index', ['title' => 'User Management', 'users' => User::all()]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        verify_csrf();
        $errors = Validator::required($_POST, ['name' => 'Name', 'email' => 'Email', 'phone' => 'Phone number', 'password' => 'Password']);
        $errors = array_merge($errors, Validator::email($_POST['email'] ?? '', 'Email'));
        if (!(new SmsService())->normalizePhone($_POST['phone'] ?? '')) {
            $errors[] = 'Phone number must use E.164 format, e.g. +639171234567.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                flash('danger', $error);
            }
            redirect('users');
        }

        User::create([
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone']),
            'password' => $_POST['password'],
            'role' => $_POST['role'] === 'admin' ? 'admin' : 'user',
            'status' => $_POST['status'] === 'inactive' ? 'inactive' : 'active',
        ]);
        ActivityLogger::log('user_created', 'Created user ' . trim($_POST['email']));
        flash('success', 'User created.');
        redirect('users');
    }

    public function update(): void
    {
        $this->requireAdmin();
        verify_csrf();
        if (trim($_POST['phone'] ?? '') !== '' && !(new SmsService())->normalizePhone($_POST['phone'] ?? '')) {
            flash('danger', 'Phone number must use E.164 format, e.g. +639171234567.');
            redirect('users');
        }

        User::update((int) $_POST['id'], [
            'name' => trim($_POST['name']),
            'phone' => trim($_POST['phone']),
            'role' => $_POST['role'] === 'admin' ? 'admin' : 'user',
            'status' => $_POST['status'] === 'inactive' ? 'inactive' : 'active',
        ]);
        ActivityLogger::log('user_updated', 'Updated user #' . (int) $_POST['id']);
        flash('success', 'User updated.');
        redirect('users');
    }
}
