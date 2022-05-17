<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\DB;
use tcCore\DefaultSection;
use tcCore\DefaultSubject;
use tcCore\ExcelDefaultSubjectAndSectionManifest;

class ExcelDefaultSubjectsAndSectionsImportHelper
{

    protected $inConsole = false;
    protected $path;
    protected $manifest;
    protected $rawSubjects;

    public function __construct($inConsole = false)
    {
        $this->inConsole = $inConsole;
    }

    public function setFilePath($path)
    {
        $this->path = storage_path($path);
        return $this;
    }

    public function handleImport()
    {
        DB::beginTransaction();
        try {
            $this->getDataFromFile();
            $this->checkDataIntegrity();
            $this->import();
            DB::commit();
        } catch(\Exception $e){
            DB::rollback();
            logger($e);
            logger($e->getMessage());
            if($this->inConsole){
                $this->output->error($e->getMessage());
            }
        }
    }

    protected function getDataFromFile()
    {
        $this->manifest = new ExcelDefaultSubjectAndSectionManifest($this->path,false);
    }

    protected function checkDataIntegrity()
    {

        $this->manifest->getSectionResources()->each(function($name){
            DefaultSection::updateOrCreate([
               'name' => $name,
            ],[]);
        });

        // throws an error if the data is not valid
        $this->rawSubjects = $this->manifest->getSubjectResources();
    }

    protected function import()
    {
        $bar = null;
        if($this->inConsole){
            $this->info(PHP_EOL.'Going to create or update the default subjects');
            $bar = $this->output->createProgressBar($this->rawSubjects->count());
            $bar->start();
        }
        $this->rawSubjects->each(function($row) use ($bar) {
            DefaultSubject::updateOrCreate(
                ['name' => $row->name],
                (array) $row
            );
            if($this->inConsole) {
                $bar->advance();
            }
        });
        if($this->inConsole) {
            $bar->finish();
            $this->info(PHP_EOL.'A total of '.$this->rawSubjects->count().' default subjects have been created or updated');
        }
    }

}