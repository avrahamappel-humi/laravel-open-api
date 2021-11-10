<?php

namespace Tests\Stubs\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModelResource extends JsonResource
{
    protected $type = 'models';

    public function toArray($request)
    {
        return [
            'data' => [
                'id' => $this->id,
                'type' => $this->type,
                'attributes' => [
                    'created_at' => $this->created_at,
                    'updated_at' => $this->updated_at,
                    'foo' => 'bar',
                ],
            ],
        ];
    }
}
