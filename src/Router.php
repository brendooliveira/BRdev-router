<?php

namespace BRdev\Router;

class Router extends Dispatch
{

    /**
     * Undocumented function
     *
     * @param string $uri
     * @param [type] $action
     * @return void
     */
    public static function get(string $uri, $action): void
    {
       self::addRoute('GET', $uri, $action);
    }

        /**
     * Undocumented function
     *
     * @param string $uri
     * @param [type] $action
     * @return void
     */
    public static function post(string $uri, $action): void
    {
       self::addRoute('POST', $uri, $action);
    }

    /**
     * Undocumented function
     *
     * @param string $uri
     * @param [type] $action
     * @return void
     */
    public static function put(string $uri, $action): void
    {
       self::addRoute('PUT', $uri, $action);
    }

    /**
     * Undocumented function
     *
     * @param string $uri
     * @param [type] $action
     * @return void
     */
    public static function delete(string $uri, $action): void
    {
       self::addRoute('DELETE', $uri, $action);
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public static function error(): bool
    {
        if(ErrorHandler::getCode() == 0){
            return false;
        }
        return true;
    }

    /**
     * Undocumented function
     *
     * @return integer
     */
    public static function geterror(): int
    {
        return ErrorHandler::getCode();
    }

    public static function group(string $prefix): void
    {
        Dispatch::setPrefix($prefix);
    }

    public static function endgroup(): void
    {
        Dispatch::setPrefix('');
    }
}
