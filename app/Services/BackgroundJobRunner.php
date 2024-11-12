<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class BackgroundJobRunner
{
    /**
     * Run a job in the background.
     *
     * @param string $className
     * @param string $methodName
     * @param array $params
     * @param int $maxRetries
     * @param int $delay
     * @return void
     */
    public function runInBackground(string $className, string $methodName, array $params = [], int $maxRetries = 3, int $delay = 0): void
    {
        if (!$this->isValidClass($className) || !$this->isValidMethod($className, $methodName)) {
            Log::channel('background_jobs_errors')->error("Invalid class or method: $className::$methodName");
            return;
        }

        $command = $this->buildCommand($className, $methodName, $params);
        $retryCount = 0;

        while ($retryCount <= $maxRetries) {
            try {
                if ($delay > 0) {
                    sleep($delay);
                }
                $this->executeCommandInBackground($command);
                Log::channel('background_jobs')
                    ->info("Job $className::$methodName started with parameters: " . json_encode($params));
                break;
            } catch (Exception $e) {
                Log::channel('background_jobs_errors')
                    ->error("Job $className::$methodName failed. Attempt: " . ($retryCount + 1) . " Error: " . $e->getMessage());
                $retryCount++;
                if ($retryCount > $maxRetries) {
                    Log::channel('background_jobs_errors')
                        ->error("Job $className::$methodName failed after $maxRetries attempts.");
                }
            }
        }
    }

    /**
     * Build the shell command to run the job in the background.
     *
     * @param string $className
     * @param string $methodName
     * @param array $params
     * @return string
     */
    private function buildCommand(string $className, string $methodName, array $params): string
    {
        $paramsJson = escapeshellarg(json_encode($params));
        return "php artisan run:background-job '$className' '$methodName' $paramsJson > /dev/null 2>&1 &";
    }

    /**
     * Execute the command in the background.
     *
     * @param string $command
     * @return void
     */
    private function executeCommandInBackground(string $command): void
    {
        if (PHP_OS === 'WINNT') {
            exec("start /B $command");
        } else {
            exec($command . " &");
        }
    }

    /**
     * Validate if the given class exists in the approved classes.
     *
     * @param string $className
     * @return bool
     */
    private function isValidClass(string $className): bool
    {
        return class_exists($className);
    }
    /**
     * Check if the method exists in the given class.
     *
     * @param string $className
     * @param string $methodName
     * @return bool
     */
    private function isValidMethod(string $className, string $methodName): bool
    {
        return method_exists($className, $methodName);
    }
}
