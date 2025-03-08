<?php

namespace craigclement\craftbrokenlinks\jobs;

use Craft;
use craft\queue\BaseJob;

/**
 * A simple test job to confirm that the queue is running.
 */
class TestJob extends BaseJob
{
    public function execute($queue): void
    {
        // Log a simple message when the job runs
        Craft::info("TestJob has run successfully!", __METHOD__);
    }

    protected function defaultDescription(): string
    {
        return "Testing Queue System";
    }
}
