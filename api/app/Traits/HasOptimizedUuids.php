<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

trait HasOptimizedUuids
{
    use HasUuids;

    /**
     * Generate a new UUID for the model using the optimized database function.
     *
     * @return string
     */
    public function newUniqueId()
    {
        if (DB::getDriverName() === 'sqlite') {
            return (string) \Illuminate\Support\Str::uuid();
        }
        return DB::selectOne('SELECT f_new_uuid() as uuid')->uuid;
    }
    
    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds()
    {
        return [$this->getKeyName()];
    }
    
    /**
     * Configure the primary key for the model.
     * 
     * @return void
     */
    protected function initializeHasOptimizedUuids()
    {
        $this->keyType = 'string';
        $this->incrementing = false;
    }
}
