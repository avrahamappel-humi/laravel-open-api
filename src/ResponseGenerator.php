<?php

namespace Asseco\OpenApi;

use Asseco\OpenApi\Specification\Paths\Operations\Response;
use Asseco\OpenApi\Specification\Paths\Operations\Responses;
use Asseco\OpenApi\Specification\Shared\Column;
use Asseco\OpenApi\Specification\Shared\Content\Content;
use Asseco\OpenApi\Specification\Shared\Content\JsonSchema;
use Asseco\OpenApi\Specification\Shared\ReferencedSchema;
use Asseco\OpenApi\Specification\Shared\StandardSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use stdClass;

class ResponseGenerator
{
    protected TagExtractor $tagExtractor;
    protected StandardSchema $schema;
    private string $schemaName;

    public function __construct(TagExtractor $tagExtractor, string $schemaName)
    {
        $this->tagExtractor = $tagExtractor;
        $this->schemaName = $schemaName;
    }

    public function generate(string $routeOperation, bool $routeHasPathParameters, bool $hasSchema): Responses
    {
        $response = new Response('200', 'Successful response.');

        if ($hasSchema) {
            $schema = new ReferencedSchema($this->schemaName);
            $schema->multiple = $this->isMultiple($routeOperation, $routeHasPathParameters);

            $jsonResponseSchema = new JsonSchema();
            $jsonResponseSchema->append($schema);

            $responseContent = new Content();
            $responseContent->append($jsonResponseSchema);

            $response->append($responseContent);
        }

        $responses = new Responses();
        $responses->append($response);

        return $responses;
    }

    public function createSchema(string $namespace, ?Model $model): ?StandardSchema
    {
        $responseColumns = $this->getResponseColumns($namespace, $model);

        if (empty($responseColumns)) {
            return null;
        }

        $schema = new StandardSchema($this->schemaName);
        $schema->generateProperties($responseColumns);

        return $schema;
    }

    protected function getResponseColumns(string $namespace, ?Model $model): array
    {
        $methodResponseColumns = $this->tagExtractor->getResponse();

        if ($methodResponseColumns) {
            return $methodResponseColumns;
        }

        $appendedColumns = $this->getColumnsToAppend($namespace);

        if ($model) {
            $modelColumns = ModelColumns::get($model);

            return $this->extractResponseData($model, $modelColumns, $appendedColumns);
        }

        return [];
    }

    protected function getColumnsToAppend(string $namespace): array
    {
        $modelsToAppend = $this->tagExtractor->getResponseAppendAttributes($namespace);
        $pivotToAppend = $this->tagExtractor->getPivotAttributes();

        $appendedColumns = [];

        foreach ($modelsToAppend as $item) {
            $appendedColumn = new Column($item['key'], 'object', true);

            $appendedModelColumns = ModelColumns::get($item['model']);
            $appendedModelRequestData = $this->extractResponseData($item['model'], $appendedModelColumns, []);

            foreach ($appendedModelRequestData as $child) {
                $appendedColumn->append($child);
            }

            $appendedColumns[] = $appendedColumn;
        }

        if (!$pivotToAppend || !Schema::hasTable($pivotToAppend)) {
            return $appendedColumns;
        }

        $appendedColumn = new Column('pivot', 'object', true);
        $appendedPivotColumns = ModelColumns::getPivot($pivotToAppend);

        foreach ($appendedPivotColumns as $child) {
            $appendedColumn->append($child);
        }

        $appendedColumns[] = $appendedColumn;

        return $appendedColumns;
    }

    protected function extractResponseData(Model $model, array $columns, array $append = []): array
    {
        // If model has associated resource, pass it through the resource
        if ($this->modelHasResource($model)) {
            return $this->getColumnsFromResource($model, $columns);
        }

        $hidden = $model->getHidden();

        foreach ($columns as $column => $type) {
            if (in_array($column, $hidden)) {
                unset($columns[$column]);
            }
        }

        foreach ($append as $item) {
            $columns[] = $item;
        }

        return $columns;
    }

    protected function modelHasResource(Model $model): bool
    {
        return property_exists($model, 'resource');
    }

    protected function getColumnsFromResource(Model $model, array $columns): array
    {
        $resourceGetter = fn() => $this->resource;
        $resourceClass = $resourceGetter->call($model);

        // Map columns by name and pass into resource class.
        $columns = collect($columns)->reduce(function (stdClass $object, Column $column) {
            $object->{$column->name} = $column;
            return $object;
        }, new \stdClass());

        $resource = (new $resourceClass($columns))->toArray(null);

        return $this->mapResourceToColumns($resource);
    }

    protected function mapResourceToColumns(array $resource): array
    {
        return collect($resource)
            ->map(function ($item, $key) {
                if ($item instanceof Column) {
                    return $item;
                }

                if (!is_array($item)) {
                    return new Column($key, gettype($item), true);
                }

                return $this->mapResourceToColumns($item);
            })
            ->all();
    }

    public function isMultiple(string $routeOperation, bool $routeHasPathParameters): bool
    {
        $hasMultipleTag = $this->tagExtractor->hasMultipleTag();

        if ($hasMultipleTag) {
            return $this->tagExtractor->isResponseMultiple();
        }

        return $routeOperation === 'get' && !$routeHasPathParameters;
    }
}
