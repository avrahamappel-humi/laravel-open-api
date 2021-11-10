<?php

namespace Tests;

use Asseco\OpenApi\SchemaGenerator;
use Illuminate\Console\OutputStyle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Yaml\Yaml;
use Tests\Stubs\Http\Controllers\ModelController;
use Tests\Stubs\Http\Controllers\ModelWithResourceController;
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

        $expected = Yaml::parse(file_get_contents(__DIR__ . '/fixtures/standard-controller.yml'));
        $schema = $this->generateSchema();

        self::assertSame($expected, $schema);
    }

    /**
     * @test
     */
    public function it_generates_a_valid_openapi_schema_with_response_data_based_on_the_model_fields()
    {
        Route::apiResource('model', ModelController::class);

        $expected = Yaml::parse(file_get_contents(__DIR__ . '/fixtures/model-controller.yml'));
        $schema = $this->generateSchema();

        self::assertSame($expected, $schema);
    }

    /**
     * @test
     */
    public function it_generates_a_valid_openapi_schema_with_response_data_based_on_the_associated_resource_fields()
    {
        Route::apiResource('model', ModelWithResourceController::class);

        $expected = file_get_contents(__DIR__ . '/fixtures/model-with-resource-controller.yml');
        $schema = Yaml::dump($this->generateSchema(), 10);

        /* file_put_contents(__DIR__ . '/fixtures/model-with-resource-controller.yml', $schema); */

        self::assertSame($expected, $schema);
    }

    // it generates a valid openapi schema with request data for read requests based on the associated filters
    // it generates a valid openapi schema with request data for write requests based on the associated validators

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
