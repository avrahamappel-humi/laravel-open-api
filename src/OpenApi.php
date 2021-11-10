<?php

namespace Asseco\OpenApi;

use Closure;

class OpenApi
{
    /**
     * Configure how you want your validation rules to be discovered.
     *
     * $fn should be a callback that takes one parameter, an instance of
     * \Illuminate\Database\Eloquent\Model, and returns an array of validation rules.
     */
    public static function determineValidationRulesBy(callable $fn): void
    {
        RequestGenerator::$getValidationRules = Closure::fromCallable($fn);
    }

    /**
     * Configure how you want your resource classes to be discovered.
     *
     * $fn should be a callback that takes a single parameter, an instance of
     * \Illuminate\Database\Eloquent\Model, and returns a string class name which extends
     * \Illuminate\Http\Resources\Json\JsonResource.
     */
    public static function determineResourceClassesBy(callable $fn): void
    {
        ResponseGenerator::$getResourceClass = Closure::fromCallable($fn);
    }
}
