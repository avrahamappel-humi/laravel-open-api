<?php

declare(strict_types=1);

namespace Asseco\OpenApi\Specification\Shared;

use Asseco\OpenApi\Contracts\Serializable;

class Properties implements Serializable
{
    private array $modelColumns;

    public function __construct(array $modelColumns)
    {
        $this->modelColumns = $modelColumns;
    }

    public function toSchema(): array
    {
        [$properties, $required] = $this->parseColumns();

        // When simple value is returned, it will have 'example' as
        // a top level array instead of nested object values.
        if (array_key_exists('example', $properties)) {
            return $properties;
        }

        return [
            'properties' => $properties,
            'required' => $required,
        ];
    }

    private function parseColumns(): array
    {
        $properties = [];
        $required = [];

        foreach ($this->modelColumns as $column) {
            if (is_string($column)) {
                $columnValues = [
                    'type' => 'string',
                    'example' => $column,
                ];
                $properties = array_merge_recursive($properties, $columnValues);
                continue;
            }

            $columnValues = [
                $column->name => $this->getColumnValue($column),
            ];

            $properties = array_merge_recursive($properties, $columnValues);

            if ($column->required) {
                $required[] = $column->name;
            }
        }

        return [$properties, $required];
    }

    private function getColumnValue(Column $column): array
    {
        $columnValue = [
            'type' => $column->type,
            'description' => $column->description,
            //'format' => 'map something',
        ];

        if ($column->children) {
            foreach ($column->children as $child) {
                if ($column->type === 'object') {
                    $columnValue = array_merge_recursive($columnValue, [
                        'properties' => [
                            $child->name => $this->getColumnValue($child),
                        ],
                    ]);

                    continue;
                }

                $columnValue = array_merge_recursive($columnValue, [
                    'items' => $this->getColumnValue($child),
                ]);
            }
        }

        return $columnValue;
    }
}
