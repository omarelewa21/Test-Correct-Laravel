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
    protected $htmlStoragePath;

    /**
     * Create a new job instance.
     */
    public function __construct($path,$htmlStoragePath)
    {
        $this->path = $path;
        $this->htmlStoragePath = $htmlStoragePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if(file_exists($this->htmlStoragePath)){
            PdfController::HtmlToPdfFileFromString(file_get_contents($this->htmlStoragePath),$this->path);
            if(file_exists($this->path)) {
                chmod($this->path, 0777);
            }
        }
    }
}
