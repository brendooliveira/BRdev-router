<?php

namespace BRdev\Router;

class Dispatch
{
    private static array $routes = [];
    private static string $separator = "@";
    private static string $namespace = '';
    
    public static function addRoute(string $method, string $uri, $action): void
    {

        $uriPattern = self::convertUriToPattern($uri);
        if(self::getNamespace()){
            self::$routes[$method][$uriPattern] = [$action, self::getNamespace()];
        }else{
            self::$routes[$method][$uriPattern] = [$action, null];
        }
       
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
    
    public static function namespace(string $namespace)
    {
        if (is_string($namespace)) {
            self::$namespace = ($namespace ? ucwords($namespace) : null);
        }
    }

    public static function getNamespace(): string
    {
        return self::$namespace;
    }

    /**
     * @param callable|string $handler
     * @param string|null $namespace
     * @return callable|string
     */
    private static function handler(callable|string $handler, ?string $namespace): callable|string
    {
        return (!is_string($handler) ? $handler : "{$namespace}\\" . explode(self::$separator, $handler)[0]);
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
                    $data = $result;
                }else{
                    $data = $matches[0];
                }

                if (is_callable($action[0])) {
                    call_user_func($action[0], $data);
                    return;
                }else{
                    [$c, $method] = explode(self::$separator, $action[0]);
                    $controller = self::handler($action[0], $action[1]);
               
                    if(class_exists($controller)){
                        $controllerInstance = new $controller();
                        if(method_exists($controllerInstance, $method)){
                            call_user_func([$controllerInstance, $method], $data);
                            return;
                        }

                        self::error('Metodo da Class não existente', 405);
                        return;
                    }

                    self::error('Class não existente', 405);
                    return;
                }
                
            
                return;
            }   
            
        }

        self::error('Rota não encontrada', 404);
    }
}
