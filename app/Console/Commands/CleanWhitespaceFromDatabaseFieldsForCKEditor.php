<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;


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
        $this->createBackups();

        $this->clean();

        return Command::SUCCESS;
    }

    private function createBackups()
    {
        foreach ($this->cleaningTablesAndFields as $tableAndField) {
            $table = $tableAndField['table'];
            $field = $tableAndField['field'];
            $backupField = sprintf('%s_backup_%s', $field, time());
            $this->info("creating backup for table $table and field $field");
            DB::statement("ALTER TABLE $table ADD COLUMN $backupField LONGTEXT");
            DB::statement("UPDATE $table SET $backupField = $field");
        }
    }

    private function clean()
    {
        foreach ($this->cleaningTablesAndFields as $tableAndField) {
            $table = $tableAndField['table'];
            $field = $tableAndField['field'];
            $this->info("cleaning table $table and field $field");
            $this->cleanTableAndField($table, $field);
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
