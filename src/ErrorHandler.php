<?php

namespace BRdev\Router;

class ErrorHandler
{
    private static $errorCode = 0;

    /**
     * @param int $code
     * @return int
     */
    public static function setCode(int $code): int
    {
        return self::$errorCode = $code;
    }

    /**
     * @return int
     */
    public static function getCode(): int
    {
        return self::$errorCode;
    }

    /**
     * @param string $message
     * @param int $code
     * @return int
     */
    public static function sendError(string $message, int $code): int
    {
        http_response_code($code);
        header("X-Error-Message: $message");
        return self::setCode($code);
    }
}
