<?php
/* Smart Office Management System - by Anggi Dwi Saputra */
require_once __DIR__ . '/../config/database.php';

function validate(array $data, array $rules): array {
    $errors = [];

    foreach ($rules as $field => $ruleStr) {
        $value = $data[$field] ?? null;

        foreach (explode('|', $ruleStr) as $rule) {
            $params = [];

            if (str_contains($rule, ':')) {
                [$rule, $p] = explode(':', $rule, 2);
                $params = explode(',', $p);
            }

            switch ($rule) {
                case 'required':
                    if ($value === null || $value === '') {
                        $errors[$field][] = "$field wajib diisi";
                    }
                    break;

                case 'email':
                    if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field][] = "Format email tidak valid";
                    }
                    break;

                case 'min':
                    if (is_string($value) && strlen($value) < (int)$params[0]) {
                        $errors[$field][] = "$field minimal {$params[0]} karakter";
                    }
                    break;

                case 'max':
                    if (is_string($value) && strlen($value) > (int)$params[0]) {
                        $errors[$field][] = "$field maksimal {$params[0]} karakter";
                    }
                    break;

                case 'in':
                    if ($value !== null && $value !== '' && !in_array((string)$value, $params, true)) {
                        $errors[$field][] = "$field harus salah satu dari: " . implode(', ', $params);
                    }
                    break;

                case 'numeric':
                    if ($value !== null && $value !== '' && !is_numeric($value)) {
                        $errors[$field][] = "$field harus angka";
                    }
                    break;

                case 'integer':
                    if ($value !== null && $value !== '' && !ctype_digit((string)$value)) {
                        $errors[$field][] = "$field harus bilangan bulat";
                    }
                    break;

                case 'date':
                    if ($value !== null && $value !== '' && !strtotime($value)) {
                        $errors[$field][] = "Format tanggal tidak valid (YYYY-MM-DD)";
                    }
                    break;

                case 'time':
                    if ($value !== null && $value !== '' && !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value)) {
                        $errors[$field][] = "Format waktu tidak valid (HH:MM)";
                    }
                    break;

                case 'after_field':
                    $otherField = $params[0];
                    $otherValue = $data[$otherField] ?? null;
                    if ($value !== null && $otherValue !== null && $value <= $otherValue) {
                        $errors[$field][] = "$field harus setelah $otherField";
                    }
                    break;

                case 'exists':
                    $table  = $params[0] ?? '';
                    $column = $params[1] ?? $field;
                    if ($value !== null && $value !== '') {
                        $db = getConnection();
                        $stmt = $db->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
                        $stmt->execute([$value]);
                        if ($stmt->fetchColumn() == 0) {
                            $errors[$field][] = "$field tidak ditemukan";
                        }
                    }
                    break;

                case 'unique':
                    $table    = $params[0] ?? '';
                    $column   = $params[1] ?? $field;
                    $ignoreId = $params[2] ?? null;
                    if ($value !== null && $value !== '') {
                        $db = getConnection();
                        $sql = "SELECT COUNT(*) FROM $table WHERE $column = ?";
                        $val = [$value];
                        if ($ignoreId) {
                            $sql .= " AND id != ?";
                            $val[] = $ignoreId;
                        }
                        $stmt = $db->prepare($sql);
                        $stmt->execute($val);
                        if ($stmt->fetchColumn() > 0) {
                            $errors[$field][] = "$field sudah digunakan";
                        }
                    }
                    break;
            }
        }
    }

    return $errors;
}
