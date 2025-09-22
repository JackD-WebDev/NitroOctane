<?php

use Illuminate\Support\Facades\DB;

it('returns healthy when the health table shows healthy', function () {
    DB::table('health')->truncate();
    DB::table('health')->insert(['health' => 1, 'created_at' => now(), 'updated_at' => now()]);

    $response = $this->getJson('/api/health');

    $response->assertStatus(200)
        ->assertJson(["success" => true])
        ->assertJsonStructure(['success', 'message']);
});

it('returns service unavailable when the health table shows unhealthy', function () {
    DB::table('health')->truncate();
    DB::table('health')->insert(['health' => 0, 'created_at' => now(), 'updated_at' => now()]);

    $response = $this->getJson('/api/health');

    $response->assertStatus(503)
        ->assertJson(["success" => false])
        ->assertJsonStructure(['success', 'message']);
});
