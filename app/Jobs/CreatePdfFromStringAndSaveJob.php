<?php

namespace tcCore\Jobs;

use Facades\tcCore\Http\Controllers\PdfController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreatePdfFromStringAndSaveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $html;

    /**
     * Create a new job instance.
     */
    public function __construct($path,$html)
    {
        $this->path = $path;
        $this->html = $html;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        PdfController::HtmlToPdfFileFromString($this->html,$this->path);
    }
}
