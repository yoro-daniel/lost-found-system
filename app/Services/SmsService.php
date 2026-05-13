<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use RuntimeException;
use Throwable;

class SmsService
{
    public function sendLoginOtp(array $user, string $otp, string $phone): bool
    {
        return $this->send($phone, 'Your Lost and Found login OTP is ' . $otp . '. It expires in 10 minutes.');
    }

    public function sendReportConfirmation(array $item, ?string $phone): bool
    {
        if (!$phone) {
            return false;
        }

        return $this->send(
            $phone,
            'Lost and Found: Your ' . $item['type'] . ' report "' . $item['title'] . '" was received. Tracking: ' . $item['tracking_code']
        );
    }

    public function sendMatchFound(array $item, array $match, ?string $phone): bool
    {
        if (!$phone) {
            return false;
        }

        return $this->send(
            $phone,
            'Lost and Found: Possible match for "' . $item['title'] . '". Matched with ' . $match['tracking_code'] . '.'
        );
    }

    public function sendClaimDecision(array $claim, string $decision): bool
    {
        if (empty($claim['claimant_phone'])) {
            return false;
        }

        return $this->send(
            $claim['claimant_phone'],
            'Lost and Found: Your claim for "' . $claim['item_title'] . '" was ' . $decision . '. Tracking: ' . $claim['tracking_code']
        );
    }

    public function send(string $to, string $message): bool
    {
        $to = $this->normalizePhone($to);
        if (!$to) {
            $this->log((string) $to, $message, 'failed', null, 'Phone number must use E.164 format, e.g. +639171234567.');
            return false;
        }

        $config = config('twilio');
        if (empty($config['account_sid']) || empty($config['auth_token'])) {
            $this->log($to, $message, 'failed', null, 'Twilio credentials are missing.');
            return false;
        }

        if (empty($config['from_number']) && empty($config['messaging_service_sid'])) {
            $this->log($to, $message, 'failed', null, 'Twilio sender number or Messaging Service SID is missing.');
            return false;
        }

        if (!function_exists('curl_init')) {
            $this->log($to, $message, 'failed', null, 'PHP cURL extension is required for Twilio SMS.');
            return false;
        }

        try {
            $endpoint = 'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($config['account_sid']) . '/Messages.json';
            $payload = [
                'To' => $to,
                'Body' => $message,
            ];

            if (!empty($config['messaging_service_sid'])) {
                $payload['MessagingServiceSid'] = $config['messaging_service_sid'];
            } else {
                $payload['From'] = $config['from_number'];
            }

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERPWD => $config['account_sid'] . ':' . $config['auth_token'],
                CURLOPT_TIMEOUT => max(5, (int) $config['timeout']),
            ]);

            $raw = curl_exec($ch);
            $error = curl_error($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if ($raw === false) {
                throw new RuntimeException($error ?: 'Unable to connect to Twilio.');
            }

            $response = json_decode($raw, true) ?: [];
            if ($status < 200 || $status >= 300) {
                throw new RuntimeException($response['message'] ?? 'Twilio rejected the SMS request.');
            }

            $this->log($to, $message, 'sent', $response['sid'] ?? null, null);
            return true;
        } catch (Throwable $exception) {
            $this->log($to, $message, 'failed', null, $exception->getMessage());
            return false;
        }
    }

    public function normalizePhone(?string $phone): ?string
    {
        $phone = trim((string) $phone);
        $phone = str_replace([' ', '-', '(', ')'], '', $phone);
        return preg_match('/^\+[1-9][0-9]{7,14}$/', $phone) ? $phone : null;
    }

    private function log(string $to, string $message, string $status, ?string $providerSid, ?string $error): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO sms_logs (recipient_phone, message, provider_sid, status, error_message) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$to, $message, $providerSid, $status, $error]);
    }
}
