<?php

namespace Asseco\OpenApi;

use Closure;

class OpenApi
{
    /**
     * Configure how you want your validation rules to be discovered.
     *
     * @param callable(\Illuminate\Database\Eloquent\Model, \Asseco\OpenApi\RouteWrapper): ?array $fn
     */
    public static function determineValidationRulesBy(callable $fn): void
    {
        RequestGenerator::$getValidationRules = Closure::fromCallable($fn);
    }

    /**
     * Configure how you want your resource classes to be discovered.
     *
     * @param callable(\Illuminate\Database\Eloquent\Model): ?class-string<\Illuminate\Http\Resources\Json\JsonResource> $fn
     */
    public static function determineResourceClassesBy(callable $fn): void
    {
        ResponseGenerator::$getResourceClass = Closure::fromCallable($fn);
    }
}
