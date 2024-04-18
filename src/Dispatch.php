<?php

namespace BRdev\Router;

/**
 * Author Brendo Olveira
 * Email brendo.dev@outlook.com
 */

class Dispatch
{

    /** @var array */
    private static $routes = [];

    /** @var string */
    private static $separator = "@";

    /** @var string */
    public static  $namespace = '';

    /** @var string */
    public static $url = '';

    /** @var string */
    private static $prefix = '';

    /** @var string */
    protected static $httpMethod;

    /** @var array|null */
    protected static $data = null;

    /**
     * @param string $method
     * @param string $uri
     * @param [type] $action
     */
    public static function addRoute(string $method, string $uri, $action)
    {
        $uri = self::getPrefix() . $uri;
        self::$httpMethod = $_SERVER['REQUEST_METHOD'];

        $uriPattern = self::convertUriToPattern($uri);
        if (self::getNamespace()) {
            self::$routes[$method][$uriPattern] = [$action, self::getNamespace()];
        } else {
            self::$routes[$method][$uriPattern] = [$action, null];
        }
    }

    protected static function formSpoofing()
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

    /**
     * @return null|array
     */
    public function data(): array
    {
        return self::$data;
    }

    /**
     * @param string $prefix
     */
    public static function setPrefix(string $prefix = '')
    {
        self::$prefix = $prefix;
    }


    public static function getPrefix()
    {
        self::$prefix;
    }

    /**
     * @param string $uri
     * @return string
     */
    private static function convertUriToPattern(string $uri): string
    {
        $pattern = preg_replace('/{([\w-]+)}/', '(?P<\1>[^\/]+)', $uri);
        return '@^' . $pattern . '\/?$@';
    }

    /**
     * @param string $url
     */
    public static function redirect(string $url)
    {
        header("Location:" . self::url($url));
        exit;
    }

    /**
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
     * @param string $namespace
     */
    public static function namespace(string $namespace) 
    {
        if (is_string($namespace)) {
            return self::$namespace = ($namespace ? ucwords($namespace) : null);
        }
    }


    /**
     * @return string
     */
    public static function getNamespace(): string
    {
        return self::$namespace;
    }

    /**
     * @param callable|string $handler
     * @param string|null $namespace
     * @return string
     */
    private static function handler(string $handler, string $namespace): string
    {
        return (!is_string($handler) ? $handler : "{$namespace}\\" . explode(self::$separator, $handler)[0]);
    }


    public static function dispatch()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $requestUri = str_replace($basePath, '', $requestUri);

        if (!isset(self::$routes[$requestMethod])) {
            ErrorHandler::sendError('Método de requisição não suportado.', 405);
        }

        self::formSpoofing();

        foreach (self::$routes[$requestMethod] as $pattern => $action) {

            if (preg_match($pattern, $requestUri, $matches)) {

                $result = array_filter($matches, function($value, $key) {
                    return is_string($key);
                }, ARRAY_FILTER_USE_BOTH);

                if ($result) {
                    $data = array_merge($result, self::$data ?? []);
                } else {
                    $data = array_merge(["url" => $matches[0]], self::$data ?? []);
                }

                if(is_callable($action[0])){
                    call_user_func($action[0], (object) $data);
                    return;
                }

                if(is_string($action[0])){
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

                if(is_array($action[0])){
                    if(class_exists($action[0][0])){
                        $controllerInstance = new $action[0][0]();
                        if(method_exists($controllerInstance, $action[0][1])){
                            call_user_func([$controllerInstance, $action[0][1]], (object) $data);
                            return;
                        }
                        ErrorHandler::sendError('Metodo da Class não existente', 405);
                        return;
                    }
                    ErrorHandler::sendError('Class não existente', 405);
                    return;
                }

                ErrorHandler::sendError('Erro', 405);
                return;
            }
        }

        ErrorHandler::sendError('Rota não encontrada', 404);
    }
}
