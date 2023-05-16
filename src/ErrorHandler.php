<?php

namespace BRdev\Router;

class ErrorHandler
{
    private static int $errorCode = 0;

    /**
     * Undocumented function
     *
     * @param integer $code
     * @return void
     */
    public static function setCode(int $code): void
    {
        self::$errorCode = $code;
    }

    /**
     * Undocumented function
     *
     * @return integer
     */
    public static function getCode(): int
    {
        return self::$errorCode;
    }

    /**
     * Undocumented function
     *
     * @param string $message
     * @param integer $code
     * @return void
     */
    public static function sendError(string $message, int $code): void
    {
        http_response_code($code);
        header("X-Error-Message: $message");
        self::setCode($code);
        return;
    }
}