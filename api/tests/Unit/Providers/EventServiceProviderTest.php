<?php

use Tests\TestCase;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Events\Registered;
use App\Providers\EventServiceProvider;
use Illuminate\Auth\Events\OtherDeviceLogout;

uses(TestCase::class);

it('should not discover events by default', function () {
    $prov = new EventServiceProvider(app());

    expect($prov->shouldDiscoverEvents())->toBeFalse();
});

it('registers expected listen mappings', function () {
    $prov = new EventServiceProvider(app());

    $ref = new ReflectionClass($prov);
    $prop = $ref->getProperty('listen');
    $prop->setAccessible(true);

    $listen = $prop->getValue($prov);

    expect(array_key_exists(Registered::class, $listen))->toBeTrue();
    expect(array_key_exists(OtherDeviceLogout::class, $listen))->toBeTrue();
    expect(array_key_exists(Verified::class, $listen))->toBeTrue();
});
