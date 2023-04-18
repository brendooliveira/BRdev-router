<?php

namespace BRdev\Router;

class Dispatch
{
    /** @var array */
    protected static array $routes = [];

    /** @var string */
    protected static string $separator = "@";

    /** @var string */
    protected static string $namespace = '';

    /** @var int */
    public static int $setError = 0;

    /** @var array|null */
    protected static ?array $data = null;

    /** @var string */
    protected static string $httpMethod;

    public static function addRoute(string $method, string $uri, $action): void
    {
        self::$httpMethod = $_SERVER['REQUEST_METHOD'];
        $uriPattern = self::convertUriToPattern($uri);
        if (self::getNamespace()) {
            self::$routes[$method][$uriPattern] = [$action, self::getNamespace()];
        } else {
            self::$routes[$method][$uriPattern] = [$action, null];
        }
    }

     /**
     * @return string
     */
    private static function convertUriToPattern(string $uri): string
    {
        $pattern = preg_replace('/{([\w-]+)}/', '(?P<\1>[^\/]+)', $uri);
        return '@^' . $pattern . '\/?$@';
    }

    /**
     * @return null|array
     */
    public function data(): ?array
    {
        return self::$data;
    }


    public static function redirect(string $url): void
    {
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        header("Location: ".$basePath.$url);
        exit;
    }

    public static function error(string $message, int $code): void
    {
        http_response_code($code);
        header("X-Error-Message: $message");
        self::setError($code);
    }

    public static function setError(int $code): int
    {
        return self::$setError = $code;
    }

    public static function getError(): int
    {
        if (self::$setError != 0) {
            return self::$setError;
        }

        return false;
    }

    public static function namespace(string $namespace): string
    {
        if (is_string($namespace)) {
            return self::$namespace = ($namespace ? ucwords($namespace) : null);
        }
    }

    protected static function getNamespace(): string
    {
        return self::$namespace;
    }

    /**
     * @param callable|string $handler
     * @param string|null $namespace
     * @return callable|string
     */
    protected static function handler(callable|string $handler, ?string $namespace): callable|string
    {
        return (!is_string($handler) ? $handler : "{$namespace}\\" . explode(self::$separator, $handler)[0]);
    }

    /**
     * httpMethod form spoofing
    */
    protected static function formSpoofing(): void
    {
        $post = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        
        if (!empty($post['_method']) && in_array($post['_method'], ["PUT", "PATCH", "DELETE"])) {
            self::$httpMethod = $post['_method'];
            self::$data = $post;

            unset(self::$data["_method"]);
            return;
        }

        if (self::$httpMethod == "POST") {
            self::$data = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            unset(self::$data["_method"]);
            return;
        }

        if (in_array(self::$httpMethod, ["PUT", "PATCH", "DELETE"]) && !empty($_SERVER['CONTENT_LENGTH'])) {
            parse_str(file_get_contents('php://input', false, null, 0, $_SERVER['CONTENT_LENGTH']), $putPatch);
            self::$data = $putPatch;

            unset(self::$data["_method"]);
            return;
        }

        self::$data = [];
    }

    public static function dispatch(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $requestUri = str_replace($basePath, '', $requestUri);

        self::formSpoofing();

        if (!isset(self::$routes[$requestMethod])) {
            self::error('Método de requisição não suportado.', 405);
        }

        foreach (self::$routes[$requestMethod] as $pattern => $action) {

            if (preg_match($pattern, $requestUri, $matches)) {

                $result = array_filter($matches, function ($value, $key) {
                    return is_string($key);
                }, ARRAY_FILTER_USE_BOTH);

                if ($result) {
                    $data = array_merge($result, self::$data);
                } else {
                    $data = array_merge(["url" => $matches[0]], self::$data);
                }

                if (is_callable($action[0])) {
                    call_user_func($action[0], $data);
                    return;
                } else {
                    [$c, $method] = explode(self::$separator, $action[0]);
                    $controller = self::handler($action[0], $action[1]);

                    if (class_exists($controller)) {
                        $controllerInstance = new $controller();
                        if (method_exists($controllerInstance, $method)) {
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
