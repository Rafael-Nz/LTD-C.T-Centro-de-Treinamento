<?php
namespace Core\Http;

class Router {
    private static array $routes = [];
    private static string $prefix = '';

    public static function get(string $path, string|array $handler, array $middlewares = []): void {
        self::addRoute('GET', $path, $handler, $middlewares);
    }

    public static function post(string $path, string|array $handler, array $middlewares = []): void {
        self::addRoute('POST', $path, $handler, $middlewares);
    }

    public static function put(string $path, string|array $handler, array $middlewares = []): void {
        self::addRoute('PUT', $path, $handler, $middlewares);
    }

    public static function delete(string $path, string|array $handler, array $middlewares = []): void {
        self::addRoute('DELETE', $path, $handler, $middlewares);
    }

    public static function group(string $prefix, callable $callback): void {
        $previousPrefix = self::$prefix;
        self::$prefix = $previousPrefix . $prefix;

        call_user_func($callback);

        self::$prefix = $previousPrefix;
    }

    private static function addRoute(string $method, string $path, string|array $handler, array $middlewares): void {
        $fullPath = self::$prefix . $path;
        $pattern = preg_replace('/\{([a-z_]+)\}/', '(?P<$1>[^/]+)', $fullPath);
        $pattern = '#^' . $pattern . '$#';

        if (!isset(self::$routes[$method])) {
            self::$routes[$method] = [];
        }

        self::$routes[$method][$pattern] = [
            'handler' => $handler,
            'middlewares' => $middlewares,
            'path' => $fullPath
        ];
    }

    public function dispatch(): void {
        $uri = $_GET['url'] ?? '';
        $uri = '/' . trim($uri, '/');
        $method = $_SERVER['REQUEST_METHOD'];

        if (isset(self::$routes[$method])) {
            foreach (self::$routes[$method] as $pattern => $route) {
                if (preg_match($pattern, $uri, $matches)) {
                    $params = array_filter($matches, static fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);

                    foreach ($route['middlewares'] as $middleware) {
                        $middlewareInstance = new $middleware();
                        if ($middlewareInstance->handle() === false) {
                            return;
                        }
                    }

                    $this->executeHandler($route['handler'], $params);
                    return;
                }
            }
        }

        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Rota nao encontrada',
            'method' => $method,
            'uri' => $uri
        ]);
    }

    private function executeHandler(string|array $handler, array $params): void {
        if (is_string($handler)) {
            call_user_func($handler, ...$params);
            return;
        }

        [$controllerClass, $methodName] = $handler;

        if (!class_exists($controllerClass)) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => "Controller {$controllerClass} nao encontrado"
            ]);
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $methodName)) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => "Metodo {$methodName} nao encontrado em {$controllerClass}"
            ]);
            return;
        }

        $controller->{$methodName}(...array_values($params));
    }
}
