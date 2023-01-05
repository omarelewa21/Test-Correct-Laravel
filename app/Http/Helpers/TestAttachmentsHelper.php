<?php

namespace tcCore\Http\Helpers;

use Carbon\Carbon;
use tcCore\Test;

class TestAttachmentsHelper extends BaseHelper
{
    protected Test $test;
    protected string $fileName;
    protected $tmpFile;
    protected string $filePath;
    protected \ZipArchive $zip;
    protected $linkAttachments;

    public static function createZipDownload(Test $test)
    {
        $instance = new static;
        $instance->test = $test;

        $instance->createZipFileName();

        $instance->createZipArchiveWithTestAttachments();

        return $instance->createDownloadWithCorrectHeaders();
    }

    protected function createZipFileName(): void
    {
        $this->fileName = sprintf(
            '%s_%s_%s.zip',
            __('test-pdf.test_attachments_zip'),
            $this->test->name,
            Carbon::now()->format('d-m-Y_H-i')
        );
    }

    protected function createDownloadWithCorrectHeaders()
    {
        if (!file_exists($this->filePath)) {
            throw new \Exception(
                sprintf("%s: cannot open <%s>", __METHOD__, $this->filePath)
            );
        }

        return response()
            ->download($this->filePath, $this->fileName)
            ->deleteFileAfterSend();
    }

    protected function createZipArchiveWithTestAttachments(): void
    {
//        $this->tmpFile = tmpfile();
//        $this->filePath = stream_get_meta_data($this->tmpFile)['uri'];

        $attachmentZipFolderPath = storage_path('attachments_zip/');

        if(!file_exists($attachmentZipFolderPath)) {
            if (!mkdir($attachmentZipFolderPath) && !is_dir($attachmentZipFolderPath)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $attachmentZipFolderPath));
            }
        }

        $this->filePath = $attachmentZipFolderPath . $this->fileName;

        $this->zip = new \ZipArchive();

        //create zip file with ZipArchive::OVERWRITE flag to overwrite tmpfile
//        if ($this->zip->open($this->filePath, \ZipArchive::OVERWRITE) !== TRUE) {
        if ($this->zip->open($this->filePath, \ZipArchive::CREATE) !== TRUE) {
            throw new \Exception(
                sprintf("%s: cannot open <%s>", __METHOD__, $this->filePath)
            );
        }

        if (!$this->test->attachments?->count()) {
            throw new \Exception(
                sprintf("%s: cannot create zip, test doesn't have attachments", __METHOD__)
            );
        }

        $this->linkAttachments = collect();

        $this->test->attachments->each(function ($attachment) {
            if ($attachment->link !== null) {
                $this->linkAttachments[] = $attachment;
                return;
            }
            $this->zip->addFile($attachment->getCurrentPath(), $attachment->title);
        });

        if ($this->linkAttachments->isNotEmpty()) {
            $textContent = $this->linkAttachments->reduce(function ($carry, $attachment) {
                $carry .= sprintf("%s\n%s\n%s", $attachment->title, $attachment->link, PHP_EOL);
                return $carry;
            }, '');
            $this->zip->addFromString('links.txt', $textContent);
        }

        $this->zip->close();
    }
}