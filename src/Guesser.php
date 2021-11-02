<?php

declare(strict_types=1);

namespace Asseco\OpenApi;

use Illuminate\Support\Str;

class Guesser
{
    public static function module(string $controller): string
    {
        $modulesNamespace = config('asseco-open-api.modules_namespace');

        if (!$modulesNamespace || !Str::contains($controller, $modulesNamespace)) {
            return '';
        }

        $moduleNamespace = Str::contains($controller, 'Http')
            ? Str::before($controller, '\\Http')
            : Str::before($controller, '\\Controllers');

        return Str::after($moduleNamespace, $modulesNamespace);
    }

    public static function modelNamespace(string $controller): string
    {
        $moduleNamespace = config('asseco-open-api.modules_namespace');

        if (empty($moduleNamespace)) {
            return config('asseco-open-api.model_namespace');
        }

        return $moduleNamespace . 'Models\\';
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
