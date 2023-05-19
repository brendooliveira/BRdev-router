<?php

namespace BRdev\Router;

/**
 * Author Brendo Olveira
 * Email brendo.dev@outlook.com
 */

class Dispatch
{
    private static array $routes = [];
    private static string $separator = "@";
    public static string $namespace = '';
    public static string $url = '';
    private static string $prefix = '';

    /**
     * Undocumented function
     *
     * @param string $method
     * @param string $uri
     * @param [type] $action
     * @return void
     */
    public static function addRoute(string $method, string $uri, $action): void
    {
        $uri = self::getPrefix() . $uri;
        
        $uriPattern = self::convertUriToPattern($uri);
        if (self::getNamespace()) {
            self::$routes[$method][$uriPattern] = [$action, self::getNamespace()];
        } else {
            self::$routes[$method][$uriPattern] = [$action, null];
        }
    }

    /**
     * Undocumented function
     *
     * @param string $prefix
     * @return void
     */
    public static function setPrefix(string $prefix): void
    {
        self::$prefix = $prefix;
    }   

    /**
     * Undocumented function
     *
     * @return string
     */
    public static function getPrefix(): string
    {
        return self::$prefix;
    }

    /**
     * Undocumented function
     *
     * @param string $uri
     * @return string
     */
    private static function convertUriToPattern(string $uri): string
    {
        $pattern = preg_replace('/{([\w-]+)}/', '(?P<\1>[^\/]+)', $uri);
        return '@^' . $pattern . '\/?$@';
    }

    /**
     * Undocumented function
     *
     * @param string $url
     * @return void
     */
    public static function redirect(string $url): void
    {
        header("Location:" .self::url($url));
        exit;
    }

    /**
     * Undocumented function
     *
     * @param string $url
     * @return string
     */
    public static function url(string $url): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME']; 
        $pathDir = str_replace('/index.php', '', $scriptName);
        $protocol = empty($_SERVER['HTTPS']) ? 'http' : 'https';
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = $protocol . "://" . $host . $pathDir;
        $path = ltrim($url, '/');

        return self::$url = $baseUrl . '/' . $path;
    }

    /**
     * Undocumented function
     *
     * @param string $namespace
     * @return string
     */
    public static function namespace(string $namespace): string
    {
        if (is_string($namespace)) {
            return self::$namespace = ($namespace ? ucwords($namespace) : null);
        }
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public static function getNamespace(): string
    {
        return self::$namespace;
    }

    /**
     * Undocumented function
     *
     * @param callable|string $handler
     * @param string|null $namespace
     * @return callable|string
     */
    private static function handler(callable|string $handler, ?string $namespace): callable|string
    {
        return (!is_string($handler) ? $handler : "{$namespace}\\" . explode(self::$separator, $handler)[0]);
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public static function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $requestUri = str_replace($basePath, '', $requestUri);


        if (!isset(self::$routes[$requestMethod])) {
            ErrorHandler::sendError('Método de requisição não suportado.', 405);
        }

        foreach (self::$routes[$requestMethod] as $pattern => $action) {

            if (preg_match($pattern, $requestUri, $matches)) {

                $result = array_filter($matches, function ($value, $key) {
                    return is_string($key);
                }, ARRAY_FILTER_USE_BOTH);

                if ($result) {
                    $data = $result;
                } else {
                    $data = $matches[0];
                }

                if (is_callable($action[0])) {
                    call_user_func($action[0], (object) $data);
                    return;
                } else {
                    [$c, $method] = explode(self::$separator, $action[0]);
                    $controller = self::handler($action[0], $action[1]);

                    if (class_exists($controller)) {
                        $controllerInstance = new $controller();
                        if (method_exists($controllerInstance, $method)) {
                            call_user_func([$controllerInstance, $method], (object) $data);
                            return;
                        }

                        ErrorHandler::sendError('Metodo da Class não existente', 405);
                        return;
                    }

                    ErrorHandler::sendError('Class não existente', 405);
                    return;
                }


                return;
            }
        }

        ErrorHandler::sendError('Rota não encontrada', 404);
    }
}
