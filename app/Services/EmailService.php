<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PHPMailer\PHPMailer\PHPMailer;
use Throwable;

class EmailService
{
    public function sendLoginOtp(array $user, string $otp): bool
    {
        return $this->send(
            $user['email'],
            'Your Lost and Found login OTP',
            $this->otpTemplate($user['name'], $otp)
        );
    }

    public function sendReportConfirmation(array $item, string $recipient): bool
    {
        return $this->send(
            $recipient,
            'Report received: ' . $item['title'],
            $this->template('Report Confirmation', 'Your ' . $item['type'] . ' item report has been received.', $item)
        );
    }

    public function sendMatchFound(array $item, array $match, string $recipient): bool
    {
        return $this->send(
            $recipient,
            'Possible match found for ' . $item['title'],
            $this->template('Possible Match Found', 'A possible matching item was found in the system.', $item, $match)
        );
    }

    public function sendClaimDecision(array $claim, string $decision): bool
    {
        return $this->send(
            $claim['claimant_email'],
            'Claim ' . ucfirst($decision) . ': ' . $claim['item_title'],
            $this->template('Claim ' . ucfirst($decision), 'Your claim has been ' . $decision . '.', [
                'title' => $claim['item_title'],
                'tracking_code' => $claim['tracking_code'],
                'status' => $decision,
                'type' => 'claim',
            ])
        );
    }

    private function send(string $to, string $subject, string $html): bool
    {
        if (!class_exists(PHPMailer::class)) {
            $this->log($to, $subject, $html, 'failed', 'PHPMailer is not installed. Run composer install.');
            return false;
        }

        $mailConfig = config('mail');
        if (!$mailConfig['username'] || !$mailConfig['password']) {
            $this->log($to, $subject, $html, 'failed', 'Gmail SMTP credentials are missing.');
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $mailConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $mailConfig['username'];
            $mail->Password = $mailConfig['password'];
            $mail->SMTPSecure = $mailConfig['encryption'] === 'ssl'
                ? PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $mailConfig['port'];
            $mail->Timeout = max(5, (int) $mailConfig['timeout']);
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];
            $mail->setFrom($mailConfig['from_address'] ?: $mailConfig['username'], $mailConfig['from_name']);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html));
            $mail->send();

            $this->log($to, $subject, $html, 'sent', null);
            return true;
        } catch (Throwable $exception) {
            $this->log($to, $subject, $html, 'failed', $exception->getMessage());
            return false;
        }
    }

    private function template(string $headline, string $message, array $item, ?array $match = null): string
    {
        $tracking = h($item['tracking_code'] ?? 'Pending');
        $title = h($item['title'] ?? 'Item');
        $status = h($item['status'] ?? 'open');
        $matchBlock = '';

        if ($match) {
            $matchBlock = '<div style="margin-top:16px;padding:14px;border-radius:12px;background:#eef6ff">
                <strong>Matched with:</strong> ' . h($match['title']) . '<br>
                <span>Tracking: ' . h($match['tracking_code']) . '</span>
            </div>';
        }

        return '<div style="margin:0;padding:28px;background:#f3f6fb;font-family:Arial,sans-serif;color:#101828">
            <div style="max-width:620px;margin:auto;background:#ffffff;border-radius:18px;padding:28px;border:1px solid #e4e7ec">
                <p style="margin:0 0 6px;color:#2563eb;font-weight:700;text-transform:uppercase;font-size:12px">Lost and Found</p>
                <h1 style="margin:0 0 10px;font-size:24px">' . h($headline) . '</h1>
                <p style="font-size:15px;color:#475467">' . h($message) . '</p>
                <div style="margin-top:18px;padding:16px;border-radius:14px;background:#f8fafc">
                    <strong>' . $title . '</strong><br>
                    <span>Tracking: ' . $tracking . '</span><br>
                    <span>Status: ' . $status . '</span>
                </div>
                ' . $matchBlock . '
                <p style="margin-top:22px;color:#667085;font-size:13px">Please contact the Lost and Found office if you need help with this record.</p>
            </div>
        </div>';
    }

    private function otpTemplate(string $name, string $otp): string
    {
        return '<div style="margin:0;padding:28px;background:#f3f6fb;font-family:Arial,sans-serif;color:#101828">
            <div style="max-width:560px;margin:auto;background:#ffffff;border-radius:18px;padding:28px;border:1px solid #e4e7ec">
                <p style="margin:0 0 6px;color:#2563eb;font-weight:700;text-transform:uppercase;font-size:12px">Lost and Found Security</p>
                <h1 style="margin:0 0 10px;font-size:24px">Login verification code</h1>
                <p style="font-size:15px;color:#475467">Hi ' . h($name) . ', use this one-time password to finish signing in.</p>
                <div style="margin:22px 0;padding:18px;border-radius:14px;background:#eff6ff;text-align:center">
                    <strong style="font-size:32px;letter-spacing:8px;color:#1d4ed8">' . h($otp) . '</strong>
                </div>
                <p style="margin:0;color:#667085;font-size:13px">This code expires in 10 minutes. If you did not try to sign in, you can ignore this email.</p>
            </div>
        </div>';
    }

    private function log(string $to, string $subject, string $body, string $status, ?string $error): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO email_logs (recipient_email, subject, body, status, error_message) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$to, $subject, $body, $status, $error]);
    }
}
