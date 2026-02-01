<?php

namespace Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExampleJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        // Logic to process the job
        if (function_exists('logger')) {
            logger()->info("Processing ExampleJob with data: " . json_encode($this->data));
        }

        // Simulating work
        sleep(1);

        logger()->info("ExampleJob Finished");
    }
}
