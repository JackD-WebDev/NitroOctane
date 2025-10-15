<?php

namespace Tests\Feature\Auth;

it('returns 422 or 404 when attempting 2FA without proper setup', function () {
    $response = $this->postJson('/api/two-factor/challenge', [
        'code' => '123456',
    ]);

    $this->assertTrue(in_array($response->getStatusCode(), [422, 404]));
});

it('returns 401 or 404 for protected 2FA-only endpoints when unauthenticated', function () {
    $response = $this->postJson('/api/two-factor/recovery-codes');
    $this->assertTrue(in_array($response->getStatusCode(), [401, 404]));
});
