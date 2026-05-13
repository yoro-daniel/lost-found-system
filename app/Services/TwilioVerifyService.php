<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use Throwable;
use Twilio\Rest\Client;

class TwilioVerifyService
{
    public function start(string $phone): bool
    {
        $phone = (new SmsService())->normalizePhone($phone);
        if (!$phone) {
            $this->log($phone ?? '', 'verify_start', 'failed', null, 'Phone number must use E.164 format, e.g. +639171234567.');
            return false;
        }

        try {
            $verification = $this->client()
                ->verify
                ->v2
                ->services($this->serviceSid())
                ->verifications
                ->create($phone, 'sms');

            $this->log($phone, 'verify_start', 'sent', $verification->sid ?? null, null);
            return true;
        } catch (Throwable $exception) {
            $this->log($phone, 'verify_start', 'failed', null, $exception->getMessage());
            return false;
        }
    }

    public function check(string $phone, string $code): bool
    {
        $phone = (new SmsService())->normalizePhone($phone);
        if (!$phone || trim($code) === '') {
            $this->log($phone ?? '', 'verify_check', 'failed', null, 'Phone number and OTP code are required.');
            return false;
        }

        try {
            $check = $this->client()
                ->verify
                ->v2
                ->services($this->serviceSid())
                ->verificationChecks
                ->create([
                    'to' => $phone,
                    'code' => trim($code),
                ]);

            $approved = ($check->status ?? '') === 'approved';
            $this->log($phone, 'verify_check', $approved ? 'sent' : 'failed', $check->sid ?? null, $approved ? null : 'Invalid or expired OTP code.');
            return $approved;
        } catch (Throwable $exception) {
            $this->log($phone, 'verify_check', 'failed', null, $exception->getMessage());
            return false;
        }
    }

    private function client(): Client
    {
        $config = config('twilio');
        if (empty($config['account_sid']) || empty($config['auth_token'])) {
            throw new \RuntimeException('Twilio Account SID or Auth Token is missing.');
        }

        return new Client($config['account_sid'], $config['auth_token']);
    }

    private function serviceSid(): string
    {
        $sid = (string) config('twilio.verify_service_sid', '');
        if ($sid === '') {
            throw new \RuntimeException('Twilio Verify Service SID is missing.');
        }

        return $sid;
    }

    private function log(string $to, string $action, string $status, ?string $providerSid, ?string $error): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO sms_logs (recipient_phone, message, provider_sid, status, error_message) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$to, 'Twilio Verify ' . $action, $providerSid, $status, $error]);
    }
}
