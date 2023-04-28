<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Telescope\Telescope;
use tcCore\SchoolLocation;

class RefreshDatabase extends Command
{
    use CommandsHelperTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:refreshdb {--file=} {--allow-all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


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
     * @return mixed
     */
    public function handle()
    {
        $sqlImports = [
            database_path('seeds/dropAllTablesAndViews.sql'),
            database_path('seeds/testdb.sql'),
            database_path('seeds/attainments.sql'),
        ];

        if ($this->hasOption('file') && $this->option('file') != null) {
            $sqlImports = [
                database_path('seeds/dropAllTablesAndViews.sql'),
                database_path(sprintf('seeds/testing/db_dump_%s.sql', $this->option('file'))),
            ];
        }

        if (!in_array(config('app.env'), ['local', 'testing'])) {
            $this->error('You cannot perform this action on this environment! only with APP_ENV set to local!!');
            return 1;
        }

        // this might be slow, so give us some time
        ini_set('max_execution_time', 180); //3 minutes

        $this->info('start refreshing database...(this can take some time as in several minutes)');
        // only needed when using mysql database, not when sqlite setup is needed
        switch (DB::connection()->getConfig('name')) {
            case 'sqlite':
                // do nothing
                break;
            case 'mysql':
            default:
                $this->handleSqlFiles($sqlImports);
                break;
        }

        $this->addMigrations();

        $this->addRequiredDatabaseData();

        $this->runseeder();

        if ($this->option('allow-all')) {
            $this->grantSchoolLocationAllPermissions();
        }
        $this->addDefaultFeatureSettingsToSchoolLocations();


        $this->info('refresh database complete');
    }

    protected function grantSchoolLocationAllPermissions()
    {
        DB::table('school_locations')->update([
            'allow_cms_drawer'              => 1,
            'allow_new_drawing_question'    => 1,
            'allow_guest_accounts'          => 1,
            'allow_new_player_access'       => 1,
            'allow_new_student_environment' => 1,
            'allow_inbrowser_testing'       => 1,
            'allow_new_test_bank'           => 1,
            'allow_wsc'                     => 1,
            'allow_writing_assignment'      => 1,
            'show_exam_material'            => 1,
            'show_cito_quick_test_start'    => 1,
            'show_national_item_bank'       => 1,
        ]);

        $this->info('granted all school locations all permissions');
    }

    private function addDefaultFeatureSettingsToSchoolLocations()
    {
        SchoolLocation::all()->each->addDefaultSettings();

    }
}
