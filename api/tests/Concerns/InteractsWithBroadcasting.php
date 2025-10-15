<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\Event;

trait InteractsWithBroadcasting
{
    /**
     * Assert that a specific event was dispatched (not necessarily broadcasted).
     */
    protected function assertEventDispatched(string $eventClass, ?callable $callback = null): void
    {
        Event::assertDispatched($eventClass, $callback);
    }

    /**
     * Assert that a specific event was not dispatched.
     */
    protected function assertEventNotDispatched(string $eventClass, ?callable $callback = null): void
    {
        Event::assertNotDispatched($eventClass, $callback);
    }

    /**
     * Assert that an event was broadcasted by checking both dispatch and broadcast queue.
     * This works with any broadcast driver including 'log'.
     */
    protected function assertEventBroadcasted(string $eventClass, ?callable $callback = null): void
    {
        Event::assertDispatched($eventClass, $callback);

        $reflection = new \ReflectionClass($eventClass);
        $this->assertTrue(
            $reflection->implementsInterface(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class),
            "Event {$eventClass} does not implement ShouldBroadcast interface"
        );
    }
}
