<?php

namespace App\Models;

use App\Traits\HasOptimizedUuids;
use Illuminate\Database\Eloquent\Model as BaseModel;

abstract class Model extends BaseModel
{
    use HasOptimizedUuids;
}
