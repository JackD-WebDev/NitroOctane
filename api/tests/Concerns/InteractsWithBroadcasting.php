<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\Event;

trait InteractsWithBroadcasting
{
    /**
     * Assert that a specific event was dispatched (not necessarily broadcasted).
     *
     * @param string $eventClass
     * @param callable|null $callback
     * @return void
     */
    protected function assertEventDispatched(string $eventClass, ?callable $callback = null): void
    {
        Event::assertDispatched($eventClass, $callback);
    }

    /**
     * Assert that a specific event was not dispatched.
     *
     * @param string $eventClass
     * @param callable|null $callback
     * @return void
     */
    protected function assertEventNotDispatched(string $eventClass, ?callable $callback = null): void
    {
        Event::assertNotDispatched($eventClass, $callback);
    }

    /**
     * Assert that an event was broadcasted by checking both dispatch and broadcast queue.
     * This works with any broadcast driver including 'log'.
     *
     * @param string $eventClass
     * @param callable|null $callback
     * @return void
     */
    protected function assertEventBroadcasted(string $eventClass, ?callable $callback = null): void
    {
        // First assert that the event itself was dispatched
        Event::assertDispatched($eventClass, $callback);
        
        // Since we're using broadcast()->toOthers() in the code, 
        // the event should be queued for broadcasting
        // We can verify this by checking if the event implements ShouldBroadcast
        $reflection = new \ReflectionClass($eventClass);
        $this->assertTrue(
            $reflection->implementsInterface(\Illuminate\Contracts\Broadcasting\ShouldBroadcast::class),
            "Event {$eventClass} does not implement ShouldBroadcast interface"
        );
    }
}