<?php

namespace Tests\Stubs\Validators;

class Validator
{
    public function rules(): array
    {
        return [
            'data' => 'array|required',
            'data.id' => 'integer',
            'data.type' => 'string|required',
            'data.attributes' => 'array|required',
            'data.attributes.foo' => 'string|required|max:255',
            'data.attributes.bar' => 'number|nullable',
        ];
    }
}
