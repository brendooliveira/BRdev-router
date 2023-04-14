<?php

namespace BRdev\Router;

class Dispatch
{
    private static array $routes = [];
    private static string $separator = "@";

    
    public static function addRoute(string $method, string $uri, $action): void
    {
        $uriPattern = self::convertUriToPattern($uri);
        self::$routes[$method][$uriPattern] = $action;
    }

    private static function convertUriToPattern(string $uri): string
    {
        $pattern = preg_replace('/{([\w-]+)}/', '(?P<\1>[^\/]+)', $uri);
        return '@^' . $pattern . '\/?$@';
    }

    public static function redirect(string $url): void
    {
        header("Location: $url");
    }

    public static function error(string $message, int $code): void
    {
        http_response_code($code);
        echo $message;
        exit;
    }

    public static function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $requestUri = str_replace($basePath, '', $requestUri);


        if (!isset(self::$routes[$requestMethod])) {
            self::error('Método de requisição não suportado.', 405);
        }

        foreach (self::$routes[$requestMethod] as $pattern => $action) {

            if (preg_match($pattern, $requestUri, $matches)) {

                $result = array_filter($matches, function ($value, $key) {
                    return is_string($key);
                }, ARRAY_FILTER_USE_BOTH);
                
                if($result){
                    $data["data"] = $result;
                }else{
                    $data["data"] = $matches[0];
                }

                if (is_callable($action)) {
                    call_user_func_array($action, $data);
                } else {
                    [$controller, $method] = explode(self::$separator, $action);
                    $controllerInstance = new $controller($data);
                    call_user_func_array([$controllerInstance, $method], $data);
                }

                return;
            }
            
        }

        self::error('Rota não encontrada', 404);
    }
}
