<?php

use Tests\TestCase;
use App\Models\User;

uses(TestCase::class);

it('does not include hidden attributes in array/JSON serialization', function () {
    $user = User::factory()->make([
        'password' => 'secret',
        'remember_token' => 'tok',
        'two_factor_secret' => 's',
        'two_factor_recovery_codes' => json_encode(['a', 'b']),
    ]);

    $arr = $user->toArray();

    expect(isset($arr['password']))->toBeFalse();
    expect(isset($arr['remember_token']))->toBeFalse();
    expect(isset($arr['two_factor_secret']))->toBeFalse();
    expect(isset($arr['two_factor_recovery_codes']))->toBeFalse();
});
