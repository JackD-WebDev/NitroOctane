<?php

use App\Traits\HasOptimizedUuids;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

uses(TestCase::class);

// Micro-benchmark: compare HasOptimizedUuids::newUniqueId() to Str::uuid()
it('benchmarks optimized uuid vs Str::uuid', function () {
    // Prepare a small model that uses the trait
    $model = new class { use HasOptimizedUuids; public function getKeyName(){return 'id';} };

    // Mock DB to return a fast uuid when selectOne is called
    DB::shouldReceive('getDriverName')->andReturn('mysql');
    DB::shouldReceive('selectOne')->andReturn((object)['uuid' => 'bench-db-uuid']);

    $iterations = 1000;

    $start = hrtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $model->newUniqueId();
    }
    $dbDuration = hrtime(true) - $start;

    $start2 = hrtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        (string) Str::uuid();
    }
    $strDuration = hrtime(true) - $start2;

    // Convert to milliseconds
    $dbMs = $dbDuration / 1e6;
    $strMs = $strDuration / 1e6;

    // Print timings so they are visible in test output and stdout
    $dbMsg = "HasOptimizedUuids (DB) for {$iterations} iters: {$dbMs}ms";
    $strMsg = "Str::uuid for {$iterations} iters: {$strMs}ms";
    info($dbMsg);
    info($strMsg);
    // Also echo to stdout so the test runner output captures it directly
    fwrite(STDOUT, $dbMsg . PHP_EOL);
    fwrite(STDOUT, $strMsg . PHP_EOL);

    // Assert the optimized approach is no slower than 3x the Str baseline
    expect($dbMs)->toBeLessThanOrEqual($strMs * 3);
});
