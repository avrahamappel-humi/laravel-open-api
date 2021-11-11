<?php

namespace Asseco\OpenApi;

use Asseco\OpenApi\Specification\Paths\Operations\RequestBody;
use Asseco\OpenApi\Specification\Shared\Column;
use Asseco\OpenApi\Specification\Shared\Content\Content;
use Asseco\OpenApi\Specification\Shared\Content\JsonSchema;
use Asseco\OpenApi\Specification\Shared\ReferencedSchema;
use Asseco\OpenApi\Specification\Shared\StandardSchema;
use Closure;
use Illuminate\Database\Eloquent\Model;

class RequestGenerator
{
    public static Closure $getValidationRules;

    private TagExtractor $tagExtractor;
    private string $schemaName;
    private RouteWrapper $route;

    public function __construct(TagExtractor $tagExtractor, string $schemaName, RouteWrapper $route)
    {
        $this->tagExtractor = $tagExtractor;
        $this->schemaName = $schemaName;
        $this->route = $route;
    }

    public function createSchema(string $namespace, ?Model $model): ?StandardSchema
    {
        $requestColumns = $this->getRequestColumns($namespace, $model);

        $schema = new StandardSchema($this->schemaName);
        $schema->generateProperties($requestColumns);

        return $schema;
    }

    public function getBody(): RequestBody
    {
        $schema = new ReferencedSchema($this->schemaName);

        $jsonRequestSchema = new JsonSchema();
        $jsonRequestSchema->append($schema);

        $requestContent = new Content();
        $requestContent->append($jsonRequestSchema);

        $requestBody = new RequestBody();
        $requestBody->append($requestContent);

        return $requestBody;
    }

    protected function getRequestColumns(string $namespace, ?Model $model): array
    {
        $methodRequestColumns = $this->tagExtractor->getRequest();

        if ($methodRequestColumns) {
            return $methodRequestColumns;
        }

        $appendedColumns = $this->getColumnsToAppend($namespace);

        if ($model) {
            $modelColumns = ModelColumns::get($model);

            $except = $this->tagExtractor->getExceptAttributes();

            return $this->extractRequestData($model, $modelColumns, $except, $appendedColumns);
        }

        return [];
    }

    protected function getColumnsToAppend(string $namespace): array
    {
        $toAppend = $this->tagExtractor->getRequestAppendAttributes($namespace);

        $appendedColumns = [];

        foreach ($toAppend as $item) {
            $appendedColumn = new Column($item['key'], 'object', true);

            $appendedModelColumns = ModelColumns::get($item['model']);
            $appendedModelRequestData = $this->extractRequestData($item['model'], $appendedModelColumns, []);

            foreach ($appendedModelRequestData as $child) {
                $appendedColumn->append($child);
            }

            $appendedColumns[] = $appendedColumn;
        }

        return $appendedColumns;
    }

    private function extractRequestData(Model $model, array $columns, array $except, array $append = []): array
    {
        if ($rules = $this->getValidationRules($model)) {
            return $this->getRequestDataFromValidator($rules);
        }

        $fillable = $model->getFillable();
        $guarded = $model->getGuarded();

        if (!empty($fillable)) {
            foreach ($columns as $key => $column) {
                if (!in_array($column->name, $fillable) || in_array($column->name, $except)) {
                    unset($columns[$key]);
                }
            }
        } elseif (!empty($guarded)) {
            foreach ($columns as $key => $column) {
                if (in_array($column->name, $guarded) || in_array($column->name, $except)) {
                    unset($columns[$key]);
                }
            }
        } else {
            $columns = [];
        }

        foreach ($append as $item) {
            $columns[] = $item;
        }

        return $columns;
    }

    private function getValidationRules(Model $model): ?array
    {
        if (!isset(self::$getValidationRules)) {
            return null;
        }

        return call_user_func(self::$getValidationRules, $model, $this->route);
    }

    private function getRequestDataFromValidator(array $rules): array
    {
        return collect($rules)->reduce(function ($columns, $rules, $attribute) {
            if (is_string($rules)) {
                $rules = explode('|', $rules);
            }

            $type = $this->getTypeFromRules($rules);
            $required = in_array('required', $rules);

            return $this->setColumnFromValidation(explode('.', $attribute), $type, $required, $columns);
        }, []);
    }

    private function getTypeFromRules(array $rules): string
    {
        foreach ($rules as $rule) {
            if ($type = $this->getTypeFromRule($rule)) {
                return $type;
            }
        }

        return 'string';
    }

    private function getTypeFromRule(string $rule): ?string
    {
        switch ($rule) {
            case 'array':
                return 'array';
            case 'boolean':
                return 'boolean';
            case 'integer':
                return 'integer';
            case 'numeric':
                return 'number';
            case 'string':
            case 'date':
                return 'string';
            default:
                return null;
        }
    }

    private function setColumnFromValidation(array $path, string $type, bool $required, array $columns): array
    {
        $name = array_shift($path);
        $key = count($columns);

        foreach ($columns as $i => $column) {
            if ($column->name === $name) {
                $key = $i;
                break;
            }
        }

        $columns[$key] = $columns[$key] ?? new Column($name, $type, $required);

        if (count($path) > 0) {
            $columns[$key]->type = $path[0] === '*' ? 'array' : 'object';
            $columns[$key]->children = $this->setColumnFromValidation(
                $path,
                $type,
                $required,
                $columns[$key]->children ?? []
            );
        }

        return $columns;
    }
}
