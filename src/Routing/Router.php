<?php

namespace Facil\Routing;

use Facil\Http\Request;
use Facil\Http\Response;

class Router {

    /**
     * Caches the mapped route names to their respective URI paths during the request lifecycle.
     * @var array
     */
    private static array $namedRoutes = [];

    public static function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        $basePath = realpath(__DIR__ . '/../../routes');
        
        $filePath = $basePath . ($uri === '/' ? '/index.php' : $uri . '.php');
        $params = [];

        // Lógica de File-based routing dinâmico (ex: /users/123 -> users/[id].php)
        if (!file_exists($filePath)) {
            $matched = self::matchDynamicRoute($basePath, $uri);
            if ($matched) {
                $filePath = $matched['file'];
                $params = $matched['params'];
            }
        }

        if (file_exists($filePath)) {
            $routeConfig = require $filePath;

            // Injeta os parâmetros na Request para facilitar o acesso
            Request::setParams($params); 

            // 1. Pega os middlewares da ROTA TODA
            $routeMiddlewares = $routeConfig['middleware'] ?? [];
            
            $handlerConfig = $routeConfig[$method] ?? null;

            if ($handlerConfig) {
                $verbMiddlewares = [];
                $handler = null;

                // 2. Verifica se o verbo tem configurações específicas (array) ou é só a closure
                if (is_callable($handlerConfig)) {
                    $handler = $handlerConfig;
                } elseif (is_array($handlerConfig) && isset($handlerConfig['handler'])) {
                    $handler = $handlerConfig['handler'];
                    $verbMiddlewares = $handlerConfig['middleware'] ?? [];
                }

                // 3. Executa todos os middlewares (Rota + Verbo)
                $allMiddlewares = array_merge($routeMiddlewares, $verbMiddlewares);
                self::runMiddlewares($allMiddlewares);

                // 4. Executa a rota e passa os parâmetros dinâmicos pra Closure
                if (is_callable($handler)) {
                    return call_user_func_array($handler, $params);
                }
            }

            return Response::json(['error' => 'Method Not Allowed'], 405);
        }

        return Response::json(['error' => 'Not Found'], 404);
    }

    private static function matchDynamicRoute(string $basePath, string $uri): ?array {
        $uriParts = explode('/', trim($uri, '/'));
        $currentPath = rtrim($basePath, '/');
        $params = [];

        foreach ($uriParts as $part) {
            $exactPath = $currentPath . '/' . $part;
            
            // Se o diretório/arquivo exato existir, continua descendo
            if (is_dir($exactPath)) {
                $currentPath = $exactPath;
                continue;
            }

            // Usa scandir() ao invés de glob() para evitar o bug dos colchetes no Windows/Linux
            if (is_dir($currentPath)) {
                $files = scandir($currentPath);
                
                foreach ($files as $file) {
                    // Ignora ponteiros de diretório
                    if ($file === '.' || $file === '..') continue;
                    
                    // Procura por arquivos no formato [variavel].php
                    if (preg_match('/^\[(.*?)\]\.php$/', $file, $matches)) {
                        $params[$matches[1]] = $part; // Extrai o nome da variável, ex: "id"
                        
                        return [
                            'file' => $currentPath . '/' . $file,
                            'params' => $params
                        ];
                    }
                }
            }
            
            return null; // Não achou rota válida
        }
        return null;
    }

    private static function runMiddlewares(array $middlewares) {
        foreach ($middlewares as $middleware) {
            // Aqui você pode instanciar as classes de middleware
            // Ex: (new $middleware())->handle();
            // Se o middleware falhar, ele deve dar um throw ou retornar um Response de erro.
        }
    }

    /**
     * Generates a full URL for a named route, replacing dynamic parameters.
     *
     * @param string $name The assigned name of the route
     * @param array $params Associative array of parameters to inject into the URL (e.g., ['id' => 123])
     * @return string The fully qualified URL
     * @throws \Exception If the route name is not found
     */
    public static function url(string $name, array $params = []): string {
        // Build the route map if it hasn't been built yet in this request
        if (empty(self::$namedRoutes)) {
            self::buildRouteMap();
        }

        if (!isset(self::$namedRoutes[$name])) {
            throw new \Exception("Facil Router Error: Route name [{$name}] not found.");
        }

        $path = self::$namedRoutes[$name];

        // Replace dynamic segments like [id] with actual values from $params
        foreach ($params as $key => $value) {
            $path = str_replace("[$key]", $value, $path);
        }

        // Determine the base URL dynamically
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        return $protocol . $host . $path;
    }

    /**
     * Recursively scans the routes directory to build a map of Route Names to URI Paths.
     *
     * @param string|null $dir Current directory being scanned
     * @param string $prefix The URI prefix for the current directory level
     * @return void
     */
    private static function buildRouteMap(?string $dir = null, string $prefix = ''): void {
        $dir = $dir ?? realpath(__DIR__ . '/../../routes');
        if (!$dir || !is_dir($dir)) return;

        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                self::buildRouteMap($path, $prefix . '/' . $file);
            } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
                $routeConfig = require $path;
                
                if (isset($routeConfig['name'])) {
                    // Convert physical file path to virtual URI path
                    // E.g., /users/[id].php -> /users/[id]
                    $uriPath = $prefix . '/' . str_replace('.php', '', $file);
                    
                    // Normalize index files (e.g., /index -> / or /users/index -> /users)
                    if ($uriPath === '/index') {
                        $uriPath = '/';
                    } elseif (str_ends_with($uriPath, '/index')) {
                        $uriPath = substr($uriPath, 0, -6);
                    }
                    
                    self::$namedRoutes[$routeConfig['name']] = $uriPath;
                }
            }
        }
    }
}