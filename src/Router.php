<?php

namespace BRdev\Router;

class Router extends Dispatch
{

    /**
     * @param string $uri
     * @param [ type ] $action
     * @return void
     */
    public static function get(string $uri, $action): void
    {
        self::addRoute('GET', $uri, $action);
    }

    /**
     * @param string $uri
     * @param [ type ] $action
     * @return void
     */
    public static function post(string $uri, $action): void
    {
        self::addRoute('POST', $uri, $action);
    }

    /**
     * @param string $uri
     * @param [ type ] $action
     * @return void
     */
    public static function put(string $uri, $action): void
    {
        self::addRoute('PUT', $uri, $action);
    }

    /**
     * @param string $uri
     * @param [ type ] $action
     * @return void
     */
    public static function delete(string $uri, $action): void
    {
        self::addRoute('DELETE', $uri, $action);
    }

    /**
     * @return boolean
     */
    public static function error(): bool
    {
        if (ErrorHandler::getCode() == 0) {
            return false;
        }
        return true;
    }

    /**
     * @return integer
     */
    public static function geterror(): int
    {
        return ErrorHandler::getCode();
    }

    /**
     * @param string $prefix
     * @return void
     */
    public static function group(string $prefix): void
    {
        Dispatch::setPrefix($prefix);
    }

    /**
     * @return void
     */
    public static function endgroup(): void
    {
        Dispatch::setPrefix('');
    }
}
