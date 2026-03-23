<?php
/**
 * Input Validation Helper
 * Server-side validation functions for all user inputs.
 * Constants are defined in config.php / .env.
 */

if (!defined('MAX_EMAIL_LENGTH'))           define('MAX_EMAIL_LENGTH',           60);
if (!defined('MAX_NAME_LENGTH'))            define('MAX_NAME_LENGTH',            50);
if (!defined('MIN_NAME_LENGTH'))            define('MIN_NAME_LENGTH',            1);
if (!defined('MAX_PASSWORD_LENGTH'))        define('MAX_PASSWORD_LENGTH',        255);
if (!defined('MAX_PASSWORD_DISPLAY_LENGTH')) define('MAX_PASSWORD_DISPLAY_LENGTH', 30);
if (!defined('MIN_PASSWORD_LENGTH'))        define('MIN_PASSWORD_LENGTH',        8);

/**
 * Validate email format and length.
 * @return true|string  true on success, error message on failure
 */
function validateEmail(string $email) {
    if (strlen($email) > MAX_EMAIL_LENGTH) {
        return "Email must be no more than " . MAX_EMAIL_LENGTH . " characters";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format";
    }
    return true;
}

/**
 * Validate a name field (first/last name).
 * @return true|string
 */
function validateName(string $name) {
    if (strlen($name) < MIN_NAME_LENGTH) {
        return "Name is required";
    }
    if (strlen($name) > MAX_NAME_LENGTH) {
        return "Name must be no more than " . MAX_NAME_LENGTH . " characters";
    }
    if (!preg_match("/^[a-zA-Z\s'\-]+$/u", $name)) {
        return "Name can only contain letters, spaces, hyphens, and apostrophes";
    }
    return true;
}

/**
 * Validate password strength.
 * @return true|string
 */
function validatePasswordStrength(string $password) {
    if (strlen($password) < MIN_PASSWORD_LENGTH) {
        return "Password must be at least " . MIN_PASSWORD_LENGTH . " characters";
    }
    if (strlen($password) > MAX_PASSWORD_DISPLAY_LENGTH) {
        return "Password must be no more than " . MAX_PASSWORD_DISPLAY_LENGTH . " characters";
    }
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number";
    }
    if (!preg_match('/[@.$#!%*?&^]/', $password)) {
        return "Password must contain at least one special character (@, $, ., #, !, %, *, ?, &, ^)";
    }
    return true;
}

/**
 * Strip tags, trim whitespace, remove null bytes.
 */
function sanitizeString(string $input): string {
    $input = strip_tags($input);
    $input = trim($input);
    $input = str_replace("\0", '', $input);
    return $input;
}

/**
 * Lowercase and trim an email address.
 */
function sanitizeEmail(string $email): string {
    return strtolower(trim($email));
}

/**
 * Validate and cast to integer.
 * @return int|false
 */
function validateInteger($value, ?int $min = null, ?int $max = null) {
    if (!is_numeric($value)) {
        return false;
    }
    $int = intval($value);
    if ($min !== null && $int < $min) return false;
    if ($max !== null && $int > $max) return false;
    return $int;
}

/**
 * Check that a string does not exceed a maximum length.
 */
function validateLength(string $input, int $maxLength): bool {
    return strlen($input) <= $maxLength;
}
