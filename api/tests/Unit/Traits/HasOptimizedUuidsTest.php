<?php

use App\Traits\HasOptimizedUuids;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class);

// Create a simple class that uses the trait so we can exercise its methods
class UuidTestModel {
    use HasOptimizedUuids;

    public $keyType = null;
    public $incrementing = null;

    public function getKeyName()
    {
        return 'id';
    }
}

it('uses db function for non-sqlite drivers', function () {
    // Mock DB::getDriverName and DB::selectOne
    DB::shouldReceive('getDriverName')->andReturn('mysql');
    DB::shouldReceive('selectOne')->with('SELECT f_new_uuid() as uuid')->andReturn((object) ['uuid' => 'db-uuid-123']);

    $model = new UuidTestModel();

    $id = $model->newUniqueId();

    expect($id)->toBe('db-uuid-123');
});

it('falls back to Str::uuid for sqlite', function () {
    DB::shouldReceive('getDriverName')->andReturn('sqlite');

    // Stub Str::uuid to return a predictable value
    $uuid = Str::uuid();

    $model = new UuidTestModel();

    $id = $model->newUniqueId();

    expect($id)->toBeString();
    expect(strlen($id))->toBeGreaterThan(0);
});

it('returns uniqueIds as primary key name', function () {
    $model = new UuidTestModel();
    expect($model->uniqueIds())->toBe(['id']);
});

it('initializes key type and incrementing correctly', function () {
    $model = new UuidTestModel();
    $ref = new \ReflectionMethod(UuidTestModel::class, 'initializeHasOptimizedUuids');
    $ref->setAccessible(true);
    $ref->invoke($model);

    expect($model->keyType)->toBe('string');
    expect($model->incrementing)->toBeFalse();
});
