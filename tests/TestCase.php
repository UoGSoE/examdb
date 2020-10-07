<?php

namespace Tests;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        TestResponse::macro('data', function ($key) {
            return $this->original->getData()[$key];
        });
        EloquentCollection::macro('assertContains', function ($value) {
            Assert::assertTrue($this->contains($value), 'Failed asserting that the collection contains the specified value.');
        });
        EloquentCollection::macro('assertNotContains', function ($value) {
            Assert::assertFalse($this->contains($value), 'Failed asserting that the collection does not contain the specified value.');
        });
        EloquentCollection::macro('assertEquals', function ($items) {
            Assert::assertEquals(count($this), count($items));
            $this->zip($items)->each(function ($pair) {
                list($a, $b) = $pair;
                Assert::assertTrue($a->is($b));
            });
        });
    }

    /**
     * Asserts that a command is registered with the console kernel schedular.
     * @param string $command The artisan-format command (eg 'myapp:do-a-thing')
     * @return void
     */
    protected function assertCommandIsScheduled(string $command)
    {
        $schedular = app(\Illuminate\Console\Scheduling\Schedule::class);
        $this->assertTrue(collect($schedular->events())->contains(function ($task) use ($command) {
            return preg_match("/ 'artisan' {$command}$/", $task->command) === 1;
        }), "Command {$command} is not registered with the schedular");
    }
}
