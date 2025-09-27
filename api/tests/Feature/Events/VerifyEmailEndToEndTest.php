<?php

namespace Tests\Feature\Events;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Carbon;

it('verifies a user via the signed verification url', function () {
    Config::set('fortify.features', [\Laravel\Fortify\Features::emailVerification()]);

    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $signed = URL::temporarySignedRoute(
        'verification.verify',
        Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
        [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]
    );

    $parsed = parse_url($signed);
    $path = $parsed['path'] ?? $signed;
    $query = $parsed['query'] ?? '';

    $endpoint = $path . ($query ? '?'.$query : '');

    $this->actingAs($user);

    $response = $this->getJson($endpoint);

    $response->assertStatus(200)
        ->assertJson(['success' => true]);

    $user->refresh();
    expect($user->email_verified_at)->not->toBeNull();
})->uses(TestCase::class);
