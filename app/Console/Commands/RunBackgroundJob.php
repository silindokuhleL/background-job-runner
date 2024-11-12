<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunBackgroundJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:background-job {class} {method} {params?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a job in the background';

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $className = $this->argument('class');
        $methodName = $this->argument('method');
        $params = $this->argument('params') ? json_decode($this->argument('params'), true) : [];

        Log::channel('background_jobs')->info("Starting the process...");
        if (class_exists($className) && method_exists($className, $methodName)) {
            $job = new $className();
            try {
                call_user_func_array([$job, $methodName], $params);
                Log::channel('background_jobs')->info("Job executed successfully.");

            } catch (\Exception $e) {
                Log::channel('background_jobs_errors')->error("Job $className::$methodName failed. Error: " . $e->getMessage());
            }
        } else {
            $this->error("Invalid class or method.");
            Log::error("Invalid class or method for $className::$methodName");
        }
    }
}
