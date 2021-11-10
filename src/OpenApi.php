<?php

namespace Asseco\OpenApi;

use Closure;

class OpenApi
{
    /**
     * Configure how you want your validation rules to be discovered.
     *
     * $fn should be a callback that takes one parameter, an instance
     * of \Illuminate\Database\Eloquent\Model.
     */
    public static function determineValidationRulesBy(callable $fn)
    {
        RequestGenerator::$getValidationRules = Closure::fromCallable($fn);
    }
}
