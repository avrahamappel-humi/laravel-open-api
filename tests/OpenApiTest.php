<?php

namespace Tests;

use Asseco\OpenApi\OpenApi;
use Asseco\OpenApi\SchemaGenerator;
use Illuminate\Console\OutputStyle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Yaml\Yaml;
use Tests\Stubs\Http\Controllers\ModelController;
use Tests\Stubs\Http\Controllers\ModelWithResourceController;
use Tests\Stubs\Http\Controllers\ModelWithValidatorController;
use Tests\Stubs\Http\Controllers\StandardController;

class OpenApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_generates_a_valid_openapi_schema_based_on_a_standard_controller()
    {
        Route::get('standard', StandardController::class . '@standard');

        $expected = file_get_contents(__DIR__ . '/fixtures/standard-controller.yml');
        $schema = Yaml::dump($this->generateSchema(), 10);

        self::assertSame($expected, $schema);
    }

    /**
     * @test
     */
    public function it_generates_a_valid_openapi_schema_with_response_data_based_on_the_model_fields()
    {
        Route::apiResource('model', ModelController::class);

        $expected = file_get_contents(__DIR__ . '/fixtures/model-controller.yml');
        $schema = Yaml::dump($this->generateSchema(), 10);

        self::assertSame($expected, $schema);
    }

    /**
     * @test
     */
    public function it_generates_a_valid_openapi_schema_with_response_data_based_on_the_associated_resource_fields()
    {
        OpenApi::determineResourceClassesBy(fn($model): ?string => $model->resource);

        Route::apiResource('model', ModelWithResourceController::class);

        $expected = file_get_contents(__DIR__ . '/fixtures/model-with-resource-controller.yml');
        $schema = Yaml::dump($this->generateSchema(), 10);

        self::assertSame($expected, $schema);
    }

    /**
     * @test
     */
    public function it_generates_a_valid_openapi_schema_with_request_data_for_write_requests_based_on_the_associated_validators()
    {
        OpenApi::determineValidationRulesBy(function ($model): ?array {
            $validatorClass = $model->validator;

            if (!$validatorClass) {
                return null;
            }

            $validator = new $validatorClass();

            return $validator?->rules();
        });

        Route::apiResource('model', ModelWithValidatorController::class);

        $expected = file_get_contents(__DIR__ . '/fixtures/model-with-validator-controller.yml');
        $schema = Yaml::dump($this->generateSchema(), 10);

        self::assertSame($expected, $schema);
    }

    private function generateSchema(): array
    {
        return app()
            ->make(SchemaGenerator::class)
            ->generate(
                $this->mock(
                    OutputStyle::class,
                    fn($mock) => $mock
                        ->shouldReceive('createProgressBar')
                        ->andReturn(
                            $this->mock(
                                Bar::class,
                                fn($bar) => $bar->shouldReceive(['start' => null, 'advance' => null, 'finish' => null])
                            )
                        )
                )
            );
    }
}
