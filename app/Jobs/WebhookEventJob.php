<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Webhook;

class WebhookEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $class_path;

    protected $Webhook;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($class_path, Webhook $Webhook)
    {
        $this->class_path = $class_path;
        $this->Webhook = $Webhook;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->class_path::handle($this->Webhook);
    }
}
