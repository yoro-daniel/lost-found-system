<?php
declare(strict_types=1);

namespace App\Core;

class Validator
{
    public static function required(array $input, array $fields): array
    {
        $errors = [];
        foreach ($fields as $field => $label) {
            if (!isset($input[$field]) || trim((string) $input[$field]) === '') {
                $errors[] = $label . ' is required.';
            }
        }
        return $errors;
    }

    public static function email(?string $value, string $label): array
    {
        if (!filter_var($value ?? '', FILTER_VALIDATE_EMAIL)) {
            return [$label . ' must be a valid email address.'];
        }
        return [];
    }
}
