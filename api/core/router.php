<?php

namespace Core;

class Router {
    private static array $routes = [];
    private static string $prefix = '';
    
    /**
     * Registra uma rota GET
     */
    public static function get(string $path, string|array $handler, array $middlewares = []): void
    {
        self::addRoute('GET', $path, $handler, $middlewares);
    }
    
    /**
     * Registra uma rota POST
     */
    public static function post(string $path, string|array $handler, array $middlewares = []): void
    {
        self::addRoute('POST', $path, $handler, $middlewares);
    }
    
    /**
     * Registra uma rota PUT
     */
    public static function put(string $path, string|array $handler, array $middlewares = []): void
    {
        self::addRoute('PUT', $path, $handler, $middlewares);
    }
    
    /**
     * Registra uma rota DELETE
     */
    public static function delete(string $path, string|array $handler, array $middlewares = []): void
    {
        self::addRoute('DELETE', $path, $handler, $middlewares);
    }
    
    /**
     * Registra múltiplas rotas com prefixo
     */
    public static function group(string $prefix, callable $callback): void
    {
        $previousPrefix = self::$prefix;
        self::$prefix = $previousPrefix . $prefix;
        
        call_user_func($callback);
        
        self::$prefix = $previousPrefix;
    }
    
    /**
     * Adiciona rota ao registro
     */
    private static function addRoute(string $method, string $path, string|array $handler, array $middlewares): void
    {
        $fullPath = self::$prefix . $path;
        
        // Converte path com placeholders para regex
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
    
    /**
     * Processa a requisição atual
     */
    public function dispatch(): void
    {
        $uri = $_GET['url'] ?? '';
        $uri = '/' . trim($uri, '/');
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Busca rota correspondente
        if (isset(self::$routes[$method])) {
            foreach (self::$routes[$method] as $pattern => $route) {
                if (preg_match($pattern, $uri, $matches)) {
                    // Remove matches nomeados duplicados
                    $params = array_filter($matches, static fn($key) => is_string($key), ARRAY_FILTER_USE_KEY);
                    
                    // Executa middlewares
                    foreach ($route['middlewares'] as $middleware) {
                        $middlewareInstance = new $middleware();
                        if ($middlewareInstance->handle() === false) {
                            return;
                        }
                    }
                    
                    // Executa o handler (controller)
                    $this->executeHandler($route['handler'], $params);
                    return;
                }
            }
        }
        
        // Rota não encontrada
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Rota não encontrada',
            'method' => $method,
            'uri' => $uri
        ]);
    }
    
    /**
     * Executa o handler da rota
     */
    private function executeHandler(string|array $handler, array $params): void
    {
        if (is_string($handler)) {
            // Handler é uma closure
            call_user_func($handler, ...$params);
            return;
        }
        
        // Handler é [ControllerClass::class, 'methodName']
        [$controllerClass, $methodName] = $handler;
        
        if (!class_exists($controllerClass)) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => "Controller {$controllerClass} não encontrado"
            ]);
            return;
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $methodName)) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => "Método {$methodName} não encontrado em {$controllerClass}"
            ]);
            return;
        }
        
        $controller->{$methodName}(...array_values($params));
    }
}