<?php

namespace App\Helpers;

use App\Services\BackgroundJobRunner;

if (!function_exists('runBackgroundJob')) {
    /**
     * Run a background job.
     *
     * @param string $className
     * @param string $methodName
     * @param array $params
     * @param int $maxRetries
     * @param int $delay
     * @return void
     */
    function runBackgroundJob(string $className, string $methodName, array $params = [], int $maxRetries = 3, int $delay = 0): void
    {
        $jobRunner = new BackgroundJobRunner();
        $jobRunner->runInBackground($className, $methodName, $params, $maxRetries, $delay);
    }
}
