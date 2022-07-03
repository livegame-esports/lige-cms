<?php

namespace Core\Routing;

class Router
{
    private array $handlers;
    private mixed $fallback;

    protected const METHOD_GET = "GET";
    protected const METHOD_POST = "POST";
    protected const METHOD_PUT = "PUT";
    protected const METHOD_DELETE = "DELETE";

    /**
     * 
     * @param string $path
     * @param mixed $handler
     * @return void
     */
    public function get($path, $handler)
    {
        $this->storeHandler(self::METHOD_GET, $path, $handler);
    }

    /**
     * 
     * @param string $path
     * @param mixed $handler
     * @return void
     */
    public function post($path, $handler)
    {
        $this->storeHandler(self::METHOD_POST, $path, $handler);
    }

    /**
     * 
     * @param callable $handler
     * @return void
     */
    public function fallback($handler)
    {
        $this->fallback = $handler;
    }

    /**
     * 
     * @param string $method
     * @param string $path
     * @param mixed $handler
     * @return void
     */
    public function storeHandler($method, $path, $handler)
    {
        $this->handlers[$method . $path] = [
            "path" => $path,
            "method" => $method,
            "handler" => $handler,
        ];
    }

    /**
     * 
     * @return void
     */
    public function run()
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI']);
        $requestPath = $requestUri['path'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $callback = null;

        foreach ($this->handlers as $handler) {
            if ($requestPath === $handler['path'] && $requestMethod === $handler['method']) {
                $callback = $handler['handler'];
            }
        }

        if (is_array($callback)) {
            $handler = new $callback[0];
            $callback[0] = $handler;
        }

        if (is_string($callback)) {
            $parts = explode("::", $callback);
            if (is_array($parts)) {
                $className = array_shift($parts);
                $handler = new $className;

                $method = array_shift($parts);
                $callback = [$handler, $method];
            }
        }

        if (!$callback) {
            header("HTTP/1.0 404 Not Found");

            if (!empty($this->fallback)) {
                $callback = $this->fallback;
            } else return;
        }

        call_user_func_array($callback, [
            array_merge($_GET, $_POST)
        ]);
    }
}
