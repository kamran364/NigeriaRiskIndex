<?php

namespace App\Utils;

class Error {

    const NotFoundException = 0;
    const ErrorException = 1;
    const AuthException = 2;

    public static $isException = 0;
    public static $isError = 1;
    public static $isAuthError = 0;
    public static $isTrialExpired = 0;
    public static $message = '';

    public static function setError($exceptionCode, $msg) {

        self::$isException = 1;
        self::$message = $msg;

        switch ($exceptionCode) {
            case self::ErrorException:
                break;

            case self::AuthException:
                self::$isAuthError = 1;
                break;

            case self::NotFoundException:
                break;

            default:
                break;
        }
    }

    public static function unsetError() {
        self::$isException = 0;
        self::$isError = 0;
    }

}
