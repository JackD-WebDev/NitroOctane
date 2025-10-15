<?php

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Traits\HasOptimizedUuids;
use Illuminate\Support\Facades\DB;

uses(TestCase::class);

it('benchmarks optimized uuid vs Str::uuid', function () {
    $model = new class
    {
        use HasOptimizedUuids;

        public function getKeyName()
        {
            return 'id';
        }
    };

    DB::shouldReceive('getDriverName')->andReturn('mysql');
    DB::shouldReceive('selectOne')->andReturn((object) ['uuid' => 'bench-db-uuid']);

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

    $dbMs = $dbDuration / 1e6;
    $strMs = $strDuration / 1e6;

    $dbMsg = "HasOptimizedUuids (DB) for {$iterations} iters: {$dbMs}ms";
    $strMsg = "Str::uuid for {$iterations} iters: {$strMs}ms";
    info($dbMsg);
    info($strMsg);
    fwrite(STDOUT, $dbMsg.PHP_EOL);
    fwrite(STDOUT, $strMsg.PHP_EOL);

    expect($dbMs)->toBeLessThanOrEqual($strMs * 3);
});
