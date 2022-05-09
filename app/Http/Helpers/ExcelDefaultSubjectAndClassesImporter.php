<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\DB;
use tcCore\DefaultSection;
use tcCore\ExcelDefaultSubjectAndSectionManifest;

class ExcelDefaultSubjectAndClassesImporter
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
        $this->path = $path;
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
        }
    }

    protected function getDataFromFile()
    {
        $this->manifest = new ExcelDefaultSubjectAndSectionManifest($this->path);
    }

    protected function checkDataIntegrity()
    {

        $this->manifest->getSectionResources()->each(function($row){
            if(DefaultSection::where('name',$row->name)->count() < 1){
                DefaultSection::create([
                   'name' => $row->name,
                ]);
            }
        });

        // throws an error if the data is not valid
        $this->rawSubjects = $this->manifest->getSubjectResources();
    }

    protected function import()
    {
        $this->rawSubjects->each(function($row){

        });
    }

}