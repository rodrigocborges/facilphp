<?php

namespace Facil\Support;

class Validate {
    /**
     * Validates an entire array of data against a set of rules.
     * Supports parameters via colon, e.g., 'min:8' or 'in:admin,user'.
     *
     * @param array $data The input data (e.g., from Request::body())
     * @param array $rules Associative array of rules
     * @return array Returns an array of errors. If empty, validation passed.
     */
    public static function check(array $data, array $rules): array {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $singleRule) {
                // Split the rule and its parameter (e.g., "min:8" -> rule: "min", param: "8")
                $parts = explode(':', $singleRule);
                $rule = $parts[0];
                $param = $parts[1] ?? null;

                if ($rule === 'required' && !self::required($value)) {
                    $errors[$field][] = "The {$field} field is required.";
                    continue 2; // Move to the next field if required fails
                }

                if ($value !== null && $value !== '') {
                    if ($rule === 'email' && !self::email($value)) {
                        $errors[$field][] = "The {$field} must be a valid email address.";
                    }
                    if ($rule === 'cpf' && !self::cpf($value)) {
                        $errors[$field][] = "The {$field} is not a valid CPF.";
                    }
                    if ($rule === 'cnpj' && !self::cnpj($value)) {
                        $errors[$field][] = "The {$field} is not a valid CNPJ.";
                    }
                    if ($rule === 'numeric' && !is_numeric($value)) {
                        $errors[$field][] = "The {$field} must be a number.";
                    }
                    if ($rule === 'url' && !self::url($value)) {
                        $errors[$field][] = "The {$field} must be a valid URL.";
                    }
                    if ($rule === 'password' && !self::password($value)) {
                        $errors[$field][] = "The {$field} must contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
                    }
                    if ($rule === 'min' && !self::min($value, $param)) {
                        $errors[$field][] = "The {$field} must be at least {$param}.";
                    }
                    if ($rule === 'max' && !self::max($value, $param)) {
                        $errors[$field][] = "The {$field} must not be greater than {$param}.";
                    }
                    if ($rule === 'in' && !self::in($value, $param)) {
                        $allowed = str_replace(',', ', ', $param);
                        $errors[$field][] = "The {$field} must be one of the following: {$allowed}.";
                    }
                    if ($rule === 'match') {
                        $matchValue = $data[$param] ?? null;
                        if ($value !== $matchValue) {
                            $errors[$field][] = "The {$field} must match the {$param} field.";
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Checks if a value is present and not an empty string.
     */
    public static function required($value): bool {
        return $value !== null && $value !== '';
    }

    /**
     * Validates an email address.
     */
    public static function email(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validates a URL.
     */
    public static function url(string $url): bool {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validates if a password is strong.
     * Must contain at least one uppercase, one lowercase, one number, and one special character.
     */
    public static function password(string $password): bool {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/', $password) === 1;
    }

    /**
     * Validates if a value is above or equal to a minimum.
     * Works for string length, array size, or numeric value.
     */
    public static function min($value, $min): bool {
        if (is_numeric($value)) {
            return $value >= $min;
        } elseif (is_string($value)) {
            return mb_strlen($value) >= $min;
        } elseif (is_array($value)) {
            return count($value) >= $min;
        }
        return false;
    }

    /**
     * Validates if a value is below or equal to a maximum.
     * Works for string length, array size, or numeric value.
     */
    public static function max($value, $max): bool {
        if (is_numeric($value)) {
            return $value <= $max;
        } elseif (is_string($value)) {
            return mb_strlen($value) <= $max;
        } elseif (is_array($value)) {
            return count($value) <= $max;
        }
        return false;
    }

    /**
     * Checks if a value exists within a comma-separated list.
     * Example list: "admin,user,manager"
     */
    public static function in($value, string $list): bool {
        $allowed = explode(',', $list);
        return in_array((string)$value, $allowed, true);
    }

    /**
     * Validates a Brazilian CPF.
     */
    public static function cpf(string $cpf): bool {
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }

    /**
     * Validates a Brazilian CNPJ.
     */
    public static function cnpj(string $cnpj): bool {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
        if (strlen($cnpj) !== 14 || preg_match('/(\d)\1{13}/', $cnpj)) return false;

        for ($i = 0, $j = 5, $sum = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $remainder = $sum % 11;
        if ($cnpj[12] != ($remainder < 2 ? 0 : 11 - $remainder)) return false;

        for ($i = 0, $j = 6, $sum = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $remainder = $sum % 11;
        return $cnpj[13] == ($remainder < 2 ? 0 : 11 - $remainder);
    }
}