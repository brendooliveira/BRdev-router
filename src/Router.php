<?php

namespace BRdev\Router;

class Router extends Dispatch
{

    public static function get(string $uri, $action): void
    {
        self::addRoute('GET', $uri, $action);
    }

    public static function post(string $uri, $action): void
    {
        self::addRoute('POST', $uri, $action);
    }

    public static function put(string $uri, $action): void
    {
        self::addRoute('PUT', $uri, $action);
    }

    public static function delete(string $uri, $action): void
    {
        self::addRoute('DELETE', $uri, $action);
    }

}
