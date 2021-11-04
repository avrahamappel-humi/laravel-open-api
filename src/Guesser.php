<?php

declare(strict_types=1);

namespace Asseco\OpenApi;

use Illuminate\Support\Str;

class Guesser
{
    public static function module(string $namespace): string
    {
        $modulesNamespace = config('asseco-open-api.modules_namespace');

        if (!$modulesNamespace || !Str::contains($namespace, $modulesNamespace)) {
            return '';
        }
        return Str::after($namespace, $modulesNamespace);
    }

    public static function namespace(string $controller): string
    {
        return Str::contains($controller, 'Http')
            ? Str::before($controller, '\\Http')
            : Str::before($controller, '\\Controllers');
    }

    public static function modelName(string $controller): string
    {
        $controllerName = Str::afterLast($controller, '\\');

        return str_replace('Controller', '', $controllerName);
    }

    public static function groupName(string $candidate, string $module = ''): string
    {
        // Split words by uppercase letter.
        $split = preg_split('/(?=[A-Z])/', $candidate);
        // Unsetting first element because it is always empty.
        unset($split[0]);

        $groupName = Str::plural(implode(' ', $split));

        return $module ? $module . ' /  ' . $groupName : $groupName;
    }
}
