<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake(); // Prevent emails during user creation
    
    // Ensure session driver is set to database for tests
    Config::set('session.driver', 'database');
    Config::set('session.table', 'sessions');
    
    // Enable session middleware for all requests in tests
    $this->withMiddleware();
});

afterEach(function () {
    // Clean up sessions table after each test
    DB::table('sessions')->truncate();
});

it('gets sessions returns error when session driver is not database', function () {
    Config::set('session.driver', 'file'); // Change to non-database driver
    
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->getJson('/api/sessions');
    
    $response->assertStatus(501)
        ->assertJson([
            'success' => false,
            'errors' => [
                'title' => 'SESSION ERROR',
            ]
        ]);
});

it('logout other browser sessions requires password', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->deleteJson('/api/sessions', []);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('logout other browser sessions fails with incorrect password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password')
    ]);
    
    $response = $this->actingAs($user)->deleteJson('/api/sessions', [
        'password' => 'wrong-password'
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('logout other browser sessions does nothing when driver is not database', function () {
    Config::set('session.driver', 'file');
    
    $user = User::factory()->create([
        'password' => Hash::make('correct-password')
    ]);
    
    $response = $this->actingAs($user)->deleteJson('/api/sessions', [
        'password' => 'correct-password'
    ]);
    
    $response->assertStatus(200)
        ->assertJson([
            'success' => true
        ]);
});