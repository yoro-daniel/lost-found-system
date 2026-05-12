<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class CloudinaryUploadService
{
    private const MAX_BYTES = 5242880;
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function uploadItemImage(array $file): ?string
    {
        if (empty($file['name'])) {
            return null;
        }

        $this->validate($file);
        $config = config('cloudinary');
        if (empty($config['cloud_name']) || empty($config['api_key']) || empty($config['api_secret'])) {
            throw new RuntimeException('Cloudinary credentials are missing. Add cloud name, API key, and API secret to .env.');
        }

        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is required for Cloudinary uploads.');
        }

        $endpoint = sprintf('https://api.cloudinary.com/v1_1/%s/image/upload', rawurlencode($config['cloud_name']));
        $postFields = [
            'file' => new \CURLFile($file['tmp_name'], mime_content_type($file['tmp_name']), $file['name']),
            'folder' => $config['folder'],
            'tags' => 'school_lost_found,item_upload',
            'use_filename' => 'true',
            'unique_filename' => 'true',
            'overwrite' => 'false',
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $config['api_key'] . ':' . $config['api_secret'],
            CURLOPT_TIMEOUT => 45,
        ]);

        $raw = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($raw === false) {
            throw new RuntimeException('Cloudinary upload failed: ' . $error);
        }

        $response = json_decode($raw, true);
        if ($status < 200 || $status >= 300 || empty($response['secure_url'])) {
            $message = $response['error']['message'] ?? 'Cloudinary upload was rejected.';
            throw new RuntimeException($message);
        }

        return $this->optimizedUrl($response['secure_url']);
    }

    private function validate(array $file): void
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Image upload failed before reaching Cloudinary.');
        }

        if (($file['size'] ?? 0) > self::MAX_BYTES) {
            throw new RuntimeException('Image must not exceed 5 MB.');
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!isset(self::ALLOWED_MIME_TYPES[$mime])) {
            throw new RuntimeException('Only JPG, PNG, and WEBP images are allowed.');
        }

        if (!getimagesize($file['tmp_name'])) {
            throw new RuntimeException('Uploaded file is not a valid image.');
        }
    }

    private function optimizedUrl(string $secureUrl): string
    {
        return str_replace('/upload/', '/upload/f_auto,q_auto,c_limit,w_1200/', $secureUrl);
    }
}
