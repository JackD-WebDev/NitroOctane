<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {    
    Config::set('session.driver', 'database');
    Config::set('session.table', 'sessions');
    
    $this->withMiddleware();
    $this->enableRouteSession('/api/sessions');
});

afterEach(function () {
    DB::table('sessions')->truncate();
});

it('returns an error when retrieving sessions if the session driver is not database', function () {
    Config::set('session.driver', 'file'); 
    
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

it('requires a password to logout other browser sessions', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->deleteJson('/api/sessions', []);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('fails when attempting to logout other browser sessions with an incorrect password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password')
    ]);
    
    $response = $this->actingAs($user)->deleteJson('/api/sessions', [
        'password' => 'wrong-password'
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('does nothing when attempting to logout other browser sessions if the driver is not database', function () {
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

it('returns user sessions when the session driver is database', function () {
    $user = User::factory()->create();

    // Ensure we have a current session id from the test session
    $currentId = session()->getId();

    $otherId = 'other-session-id';

    DB::table('sessions')->insert([
        [
            'id' => $currentId,
            'user_id' => $user->id,
            'user_agent' => 'TestAgent/1.0',
            'payload' => '',
            'ip_address' => '127.0.0.1',
            'last_activity' => time(),
        ],
        [
            'id' => $otherId,
            'user_id' => $user->id,
            'user_agent' => 'OtherAgent/2.0',
            'payload' => '',
            'ip_address' => '192.168.0.1',
            'last_activity' => time() - 3600,
        ],
    ]);

    $response = $this->actingAs($user)->getJson('/api/sessions');
    $response->assertStatus(200)
        ->assertJson([ 'success' => true ]);

    $data = $response->json('data');
    // Expect two sessions and the expected IPs
    expect(count($data))->toBe(2);
    expect(collect($data)->pluck('ip'))->toContain('127.0.0.1');
    expect(collect($data)->pluck('ip'))->toContain('192.168.0.1');
});

it('returns null lastActive when last_activity is not numeric or zero', function () {
    $user = User::factory()->create();

    $currentId = session()->getId();

    DB::table('sessions')->insert([
        'id' => $currentId,
        'user_id' => $user->id,
        'user_agent' => 'Agent/3.0',
        'payload' => '',
        'ip_address' => '10.0.0.1',
        'last_activity' => 0,
    ]);

    $response = $this->actingAs($user)->getJson('/api/sessions');
    $response->assertStatus(200)->assertJson([ 'success' => true ]);

    $data = $response->json('data');
    $session = collect($data)->first();
    expect($session['lastActive'])->toBeNull();
});

it('deletes other session records when logging out other browser sessions if driver is database', function () {
    $user = User::factory()->create([
        'password' => Hash::make('mypassword'),
    ]);

    $currentId = session()->getId();
    $otherId = 'to-delete-session';

    DB::table('sessions')->insert([
        [
            'id' => $currentId,
            'user_id' => $user->id,
            'user_agent' => 'CurrAgent/1.0',
            'payload' => '',
            'ip_address' => '127.0.0.1',
            'last_activity' => time(),
        ],
        [
            'id' => $otherId,
            'user_id' => $user->id,
            'user_agent' => 'OtherAgent/2.0',
            'payload' => '',
            'ip_address' => '10.0.0.2',
            'last_activity' => time(),
        ],
    ]);

    $response = $this->actingAs($user)->deleteJson('/api/sessions', [
        'password' => 'mypassword'
    ]);

    $response->assertStatus(200)->assertJson([ 'success' => true ]);

    // Other session should be deleted
    $exists = DB::table('sessions')->where('id', $otherId)->exists();
    expect($exists)->toBeFalse();
    // At least one session should still exist for the user
    $remaining = DB::table('sessions')->where('user_id', $user->id)->count();
    expect($remaining)->toBeGreaterThanOrEqual(1);
});