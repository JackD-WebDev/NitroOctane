<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Blueprint::macro('optimizedUuid', function ($column = 'id') {
            return $this->char($column, 36);
        });
    }
};
