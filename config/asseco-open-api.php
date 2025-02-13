<?php

use Asseco\OpenApi\Specification\Paths\Operations\Parameters\DataTypes\ArrayType;
use Asseco\OpenApi\Specification\Paths\Operations\Parameters\DataTypes\BooleanType;
use Asseco\OpenApi\Specification\Paths\Operations\Parameters\DataTypes\IntegerType;
use Asseco\OpenApi\Specification\Paths\Operations\Parameters\DataTypes\NumberType;
use Asseco\OpenApi\Specification\Paths\Operations\Parameters\DataTypes\ObjectType;
use Asseco\OpenApi\Specification\Paths\Operations\Parameters\DataTypes\StringType;

return [

    /**
     * Namespace for Laravel models.
     */
    'model_namespace'          => 'App\\Models\\',

    /**
     * General OpenApi properties to generate.
     */
    'general'                  => [
        'openapi' => '3.0.0',

        'info' => [
            'title'       => ucfirst(config('app.name')) . ' API',
            'description' => 'Generated with this [awesome package](https://github.com/asseco-voice/laravel-open-api)!',
            'version'     => '0.0.1',
        ],

        'servers' => [
            [
                'url'         => config('app.url'),
                'description' => 'Default server',
            ],
        ],
    ],

    /**
     * For models which can't be inferred from controller name.
     *
     * I.e. if model is User, and controller is SysUserController,
     * command will take SysUser as a relevant model. Remap here to
     * ensure right controller-model mapping
     */
    'controller_model_mapping' => [
        //        SysUserController::class => User::class
    ],

    /**
     * Name of generated file.
     */
    'file_name'                => 'open-api.yml',

    /**
     * Rules for excluding certain rules.
     */
    'exclude'                  => [
        // Partial match. Using 'index' will match '*index*'.
        'route_name'      => [
            'horizon',
        ],
        // Exact match by controller full namespace
        'controller_name' => [
            'Clockwork\Support\Laravel\ClockworkController',
        ],
    ],

    'global_headers' => '',

    'data_types' => [
        'string'  => StringType::class,
        'number'  => NumberType::class,
        'integer' => IntegerType::class,
        'boolean' => BooleanType::class,
        'array'   => ArrayType::class,
        'object'  => ObjectType::class,
    ],

    /**
     * Force re-caching. Used as a part of the command flag, not to be used manually.
     */
    'bust_cache' => false,

    /**
     * Get additional command output (for debugging purposes). Command flag, not to be used manually.
     */
    'verbose'    => false,

    /**
     * Prepend service name to operationId.
     */
    'prepend_service_name' => true,

    /**
     * Set ID path parameters to string instead of integer.
     */
    'service_uses_uuid' => false,
];
