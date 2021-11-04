<?php

namespace Tests;

use Asseco\OpenApi\SchemaGenerator;
use Illuminate\Support\Facades\Route;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Yaml\Yaml;
use Tests\Stubs\ModelController;

class OpenApiTest extends TestCase
{
    /**
     * @test
     */
    public function it_generates_a_valid_openapi_schema_based_on_a_standard_controller()
    {
        Route::apiResource('model', ModelController::class);

        $expected = Yaml::parse(file_get_contents(__DIR__ . '/fixtures/standard-controller.yml'));
        $schema = $this->generateSchema();

        self::assertSame($expected, $schema);
    }

    // it generates a valid openapi schema based on a module controller

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
