<?php

namespace Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Tests\Stubs\Http\Resources\ModelResource;

class ModelWithResource extends Model
{
    protected $table = 'models';

    public $resource = ModelResource::class;
}
