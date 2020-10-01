<?php


namespace Voice\OpenApi;


use Closure;
use Exception;
use Illuminate\Routing\Route;

class RouteWrapper
{
    private Route    $route;
    protected string $controllerName;
    protected string $controllerMethod;

    public function __construct(Route $route)
    {
        if (!array_key_exists('uses', $route->getAction())) {
            throw new Exception("Route {$route->getName()} is missing mandatory data.");
        }

        $this->route = $route;
    }

    public function uri(): string
    {
        return "/{$this->route->uri()}";
    }

    public function action(): array
    {
        return $this->route->getAction();
    }

    public function controllerName()
    {
        if (isset($this->controllerName)) {
            return $this->controllerName;
        }

        $this->controllerName = $this->explodeAction()[0];

        return $this->controllerName;
    }

    public function controllerMethod()
    {
        if (isset($this->controllerMethod)) {
            return $this->controllerMethod;
        }

        $this->controllerMethod = $this->explodeAction()[1];

        return $this->controllerMethod;
    }

    protected function explodeAction()
    {
        $exploded = explode('@', $this->action()['uses']);

        if (sizeof($exploded) < 2) {
            throw new Exception("Exploding {$this->route->getName()} route controller@action resulted in error.");
        }

        return $exploded;
    }

    public function isClosure(): bool
    {
        return $this->action()['uses'] instanceof Closure;
    }

    public function requestMethods(): array
    {
        return array_map(function ($method) {
            return strtolower($method);
        }, array_diff($this->route->methods(), ['HEAD']));
    }
}
