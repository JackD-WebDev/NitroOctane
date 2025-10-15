<?php

use App\Models\User;

it('returns all users with the correct structure for the index endpoint', function () {
    $user = User::factory()->create();

    User::factory()->count(2)->create();

    $response = $this->actingAs($user)->getJson('/api/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'data' => [
                        'type',
                        'user_id',
                        'attributes' => [
                            'name',
                            'username',
                            'email_verified_at',
                            'preferred_language',
                            'created_at_dates',
                            'updated_at_dates',
                        ],
                    ],
                    'links',
                    'meta',
                ],
            ],
            'links',
            'meta',
            'version',
        ]);

    $data = $response->json('data');
    expect(count($data))->toBe(3);
});
