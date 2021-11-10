<?php

namespace Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Tests\Stubs\Validators\Validator;

class ModelWithValidator extends Model
{
    public $validator = Validator::class;
}
