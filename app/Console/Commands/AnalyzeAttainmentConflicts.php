<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use tcCore\Exports\AttainmentConflictCollectionExport;
use tcCore\Exports\AttainmentConflictExport;
use tcCore\Question;
use tcCore\User;

class AnalyzeAttainmentConflicts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analyze:attainment-conflicts {--mode=} {--weight=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Specific command for analyzing attainments that belong to questions that Marja has made';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $mode = $this->option('mode');
        $weight = is_null($this->option('weight'))?false:$this->option('weight');
        switch($mode)
        {
            case 'marja':
                $this->exportExcelForUser(68233,$weight);
                $this->info('finished');
                break;
            case 'docent14':
                $this->exportExcelForUser(40325,$weight);
                $this->info('finished');
                break;
            case 'countAll':
                $count = $this->countAll();
                $this->info($count.' counted');
                break;
            case 'all':
                $this->exportExcelForAll();
                $this->info('finished');
                break;
            default:
                $this->info('unknown mode');
        }


    }

    private function exportExcelForUser($userId,$weight)
    {
        $user = User::find($userId);
        $questions = $user->authors()->get();
        Excel::store(new AttainmentConflictExport($questions,$weight), __DIR__.'/../../logs/attainments_conflicts_export_'.$userId.'_'.$weight.'.xlsx');
    }

    private function exportExcelForAll()
    {
        $collection = $this->getCollectionForAll();
        $questions = [];
        $export = new AttainmentConflictCollectionExport($questions,'superLean');
        $export->setCollection($collection);
        Excel::store($export, __DIR__.'/../../logs/attainments_conflicts_export_all.xlsx');
    }

    private function countAll()
    {
        $collection = $this->getCollectionForAll();
        return $collection->count();
    }

    private function getCollectionForAll()
    {
        $collection = collect([]);
        Question::where('type','not like','%bla%')->chunk(100, function ($questions) use($collection) {
            $chunkCollection = (new AttainmentConflictExport($questions,'superLean'))->collection();
            foreach ($chunkCollection as $entryArray){
                    $collection->push($entryArray);
            }
        });
        return $collection;
    }
}
