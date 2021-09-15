<?php
/**
 * @class Db_Validation
 *
 * This class has all the static methods to validate a field and return an error.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Base
 * @version 4.0.0
 */
class Db_Validation
{

    /**
     * Validate a strong password with at least 8 characters, one uppercase and a digit.
     */
    public static function validatePassword($password)
    {
        if (strlen($password) < 8) {
            return __('error_password_size');
        }
        if (!preg_match('@[A-Z]@', $password)) {
            return __('error_password_uppercase');
        }
        if (!preg_match('@[a-z]@', $password)) {
            return __('error_password_lowercase');
        }
        if (!preg_match('@[0-9]@', $password)) {
            return __('error_password_number');
        }
        return '';
    }

}
