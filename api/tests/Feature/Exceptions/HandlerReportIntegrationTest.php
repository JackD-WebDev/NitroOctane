<?php

use Illuminate\Support\Facades\Route;

test('reports exceptions via the framework flow and returns 500', function () {
    Route::get('/__test-handler-report', function () {
        throw new \Exception('integration report test');
    });

    $resp = $this->get('/__test-handler-report');

    $resp->assertStatus(500);
});
