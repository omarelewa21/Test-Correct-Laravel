<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


/**
 * The question column in questions table is managed in the admin interface by CKEditor.
 * The old version whould add a lot of whitespace to the column.
 * The domNode clean function can be found in the file domconvertor.js::1292 in the method    _processDataFromDomText( node ) {
 * This command will try clean up the whitespace.
 *
 * expected problems:
 * - a field can contain latex code this is stored in a alt attribute on a img tag this can be found using \n or \r so we exclude latex code from the cleaning
 * - in a pre tag the \n and \r\n have meaning so we exclude pre tags from the cleaning
 * - in a code tag the \n and \r\n have meaning so we exclude code tags from the cleaning
 *
 * properties of the solution:
 * a database field of type longtext can be viewed as html, text or binary.
 *
 * solutions for cleaning up in the context of html are considered savest but could also take a lot of time to build and
 * could lead to regression because cleanup in the solution is not exactly the same as in the ckEditor which is a prerequisite.
 *
 * solution for cleaning up as text is considered the a good solution because it is fast and easy to implement. But it leaves open the the mixup between a char(10) and a char(13) and a char(10)char(13)
 * which is a problem and the ascii repesenation of a char(10) and a char(13) is not the same in all operating systems. Although we choose this apporach we should be aware of this problem. And we have tried to exclude the expected cases (see above).
 *
 * solution for cleaning up as binary is considered a nice solution because it can distinguish between a char(10)
 * and a char(13) and a char(10)char(13) and the ascii repesenation so it could be considerably saver
 * but this approach need extensive knowledge of regexp which we do not have. A other consideration is that the ckEditor cleaner does not clean as binary but as sting so outcomes can still be different;
 *
 */
class CleanWhitespaceFromDatabaseFieldsForCKEditor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ckeditor_fields:clean';

    private $cleaningTablesAndFields = [
        [
            'table' => 'questions',
            'field' => 'question',
        ], [
            'table' => 'open_questions',
            'field' => 'answer',
        ],
    ];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $actions = collect([
            ['label' => 'createBackupsAndClean', 'name' => 'create backup and clean'],
            ['label' => 'restoreBackups', 'name' => 'restore backup'],
        ]);

        $choice = $this->choice(
            'what do you want to do?',
            $this->getChoiceOptions($actions),
            0
        );

        $action = $this->getChoice($actions,$choice);

        if(method_exists($this,$action)) {
            return $this->{$action}() ? Command::SUCCESS : Command::FAILURE;
        }
        $this->error('no such action '.$action);
        return Command::FAILURE;

    }

    protected function getChoiceOptions($collection)
    {
        return $collection->map(function ($env) {
            return $env['name'];
        })->toArray();
    }

    protected function getChoice($collection,$name)
    {
        return ($collection->firstWhere('name',$name))['label'];
    }

    private function restoreBackups()
    {
        $timeValues = $this->getAvailableTimeValuesForRestore();
        if(count($timeValues) === 0) {
            $this->error('No backups to restore, sorry');
            return false;
        }
        else if(count($timeValues) === 1){
            $timeValue = $timeValues[0];
        }
        else {
            $timeValue = $this->choice('Which time value to use', $timeValues);
        }

        // are the fields available
        foreach ($this->cleaningTablesAndFields as $tableAndField) {
            $table = $tableAndField['table'];
            $field = $tableAndField['field'];
            $backupField = $this->createBackupFieldString($field,$timeValue);
            if(!Schema::hasColumn($table,$backupField)){
                $this->error(sprintf('Sorry we can not continue, the table %s does not have the field %s',$table,$backupField));
                return false;
            }
        }

        foreach($this->cleaningTablesAndFields as $tableAndField) {
            $table = $tableAndField['table'];
            $field = $tableAndField['field'];
            $backupField = $this->createBackupFieldString($field,$timeValue);
            $carbonInst = Carbon::createFromFormat('YmdHis',$timeValue);
            $this->info("restoring backup for table $table and field $field from $timeValue");
            DB::statement("UPDATE $table SET $field = $backupField  where $backupField is not null AND $backupField != '' AND updated_at <= '".$carbonInst->format('Y-m-d H:i:s')."'");
        }

        $this->info('restoring backups done, do not forget to remove the backup fields if they are no longer needed');
        return true;
    }

    protected function getAvailableTimeValuesForRestore()
    {
        $table = $this->cleaningTablesAndFields[0]['table'];
        $field = $this->cleaningTablesAndFields[0]['field'];
        $likeField = $this->createBackupFieldString($field,'');
        $columns = Schema::getColumnListing($table);

        return collect($columns)->filter(
            function($attr) use ($likeField) {
                    return Str::contains($attr,$likeField);
            }
        )->map(function($attr) use ($likeField) {
            return Str::replace($likeField,'',$attr);
        })->values()->toArray();
    }

    private function createBackupsAndClean()
    {
        $start =  $this->getStartTime();
        $this->createBackups();
        $this->clean();
        $this->info(sprintf('creating backups and do the cleaning is done in %d ms, please check the result',$this->getDuration($start)));
        return true;
    }

    private function createBackups()
    {
        $timeValue = date('YmdHis');
        foreach ($this->cleaningTablesAndFields as $tableAndField) {
            $start =  $this->getStartTime();
            $table = $tableAndField['table'];
            $field = $tableAndField['field'];
            $backupField = $this->createBackupFieldString($field,$timeValue);
            $this->info("creating backup for table $table and field $field with timevalue $timeValue");
            DB::statement("ALTER TABLE $table ADD COLUMN $backupField TEXT");
            DB::statement("UPDATE $table SET $backupField = $field");
            $this->info(sprintf('done in %d ms',$this->getDuration($start)));
        }

        return true;
    }

    protected function getStartTime()
    {
        return microtime(true);
    }

    protected function getDuration($start)
    {
        return microtime(true) * 1000 - ($start * 1000);
    }

    private function createBackupFieldString($field,$timeValue)
    {
        return sprintf('%s_backup_%s', $field, $timeValue);
    }

    private function clean()
    {
        foreach ($this->cleaningTablesAndFields as $tableAndField) {
            $start = $this->getStartTime();
            $table = $tableAndField['table'];
            $field = $tableAndField['field'];
            $this->info("cleaning table $table and field $field");
            $this->cleanTableAndField($table, $field);
            $this->info(sprintf('cleaning done in %d ms',$this->getDuration($start)));
        }
    }

    private function cleanTableAndField(string $table, string $field)
    {
        DB::statement(
            <<<SQL
                UPDATE $table 
                SET $field = REPLACE(REPLACE($field, '\n', ''), '\r', '') 
                 WHERE (
                     $field NOT LIKE '%<pre>%' 
                     OR $field NOT LIKE '%<code>%'
                     OR $field like '%https://latex.codecogs.com/gif.latex%'
                 )
            SQL
        );
    }
}
