<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Jobs\TestJob;
use App\Jobs\TestJobShouldBeUnique;
use App\Jobs\TestBatchableJobShouldBeUnique;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;

class QueueBugsTest extends TestCase
{
    public function test_dispatch(): void
    {
        Queue::fake();

        dispatch(new TestJob);
        dispatch(new TestJob);
        dispatch(new TestJob);
        dispatch(new TestJob);

        Queue::assertPushed(TestJob::class, 4);
    }

    public function test_dispatch_supports_should_be_unique(): void
    {
        Queue::fake();

        dispatch(new TestJobShouldBeUnique);
        dispatch(new TestJobShouldBeUnique);
        dispatch(new TestJobShouldBeUnique);
        dispatch(new TestJobShouldBeUnique);

        Queue::assertPushed(TestJobShouldBeUnique::class, 1);
    }

    public function test_bus_dispatch_does_not_support_should_be_unique(): void
    {
        Queue::fake();

        Bus::dispatch(new TestJobShouldBeUnique);
        Bus::dispatch(new TestJobShouldBeUnique);
        Bus::dispatch(new TestJobShouldBeUnique);
        Bus::dispatch(new TestJobShouldBeUnique);

        Queue::assertPushed(TestJobShouldBeUnique::class, 4);
    }

    public function test_bus_batch_dispatch_does_not_support_should_be_unique(): void
    {
        Queue::fake();

        Bus::batch([
            new TestBatchableJobShouldBeUnique,
            new TestBatchableJobShouldBeUnique,
        ])->dispatch();

        Queue::assertPushed(TestBatchableJobShouldBeUnique::class, 2);

        Bus::batch([
            new TestBatchableJobShouldBeUnique,
            new TestBatchableJobShouldBeUnique,
        ])->dispatch();

        Queue::assertPushed(TestBatchableJobShouldBeUnique::class, 4);
    }

    public function test_bus_chain_somewhat_supports_should_be_unique(): void
    {
        Queue::fake();

        Bus::chain([
            new TestBatchableJobShouldBeUnique,
            new TestBatchableJobShouldBeUnique,
            new TestBatchableJobShouldBeUnique,
            new TestBatchableJobShouldBeUnique,
        ])->dispatch();

        Queue::assertPushed(TestBatchableJobShouldBeUnique::class, 1);

        Bus::chain([
            new TestBatchableJobShouldBeUnique,
            new TestBatchableJobShouldBeUnique,
            new TestBatchableJobShouldBeUnique,
            new TestBatchableJobShouldBeUnique,
        ])->dispatch();

        Queue::assertPushed(TestBatchableJobShouldBeUnique::class, 2);
    }
}
