<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use tcCore\BaseSubject;
use tcCore\Factories\FactoryUser;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithAllQuestionTypes;
use tcCore\Lib\User\Factory;
use tcCore\Lib\User\Roles;
use tcCore\License;
use tcCore\MaintenanceWhitelistIp;
use tcCore\Period;
use tcCore\SchoolYear;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\TestKind;
use tcCore\TrialPeriod;
use tcCore\User;

class CreateTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createAddresses();
        $this->createAnswers();
        $this->createAnswerParentQuestions();
        $this->createAnswerRatings();
        $this->createAnswersFeedback();
        $this->createAppVersionInfos();
        $this->createAppFeatureSettings();
        $this->createArchivedModels();
        $this->createAttachements();
        $this->createAttainments();
        $this->createCache();
        $this->createCitoExportRows();
        $this->createCompletionQuestionAnswerLinks();
        $this->createCompletionQuestionAnswers();
        $this->createCompletionQuestions();
        $this->createContacts();
        $this->createDatabaseQuestions();
        $this->createDefaultSections();
        $this->createDefaultSubjects();
        $this->createDemoTeacherRegistrations();
        $this->createDeployments();
        $this->createEanCodes();
        $this->createDrawingQuestions();
        $this->createEducationLevels();
        $this->createDiscussingParentQuestions();
        $this->createEckidUser();
        $this->createEduIxRegistrations();
        $this->createEmailConfirmations();
        $this->createFailedJobs();
        $this->createFailedLogins();
        $this->createFeatureSettings();
        $this->createFileManagementStatusLogs();
        $this->createFileManagementStatuses();
        $this->createFileManagements();
        $this->createGeneralTermsLogs();
        $this->createGradingScales();
        $this->createGroupQuestionQuestions();
        $this->createGroupQuestion();
        $this->createInfoRole();
        $this->createInfos();
        $this->createInfoscreenQuestions();
        $this->createInvigilators();
        $this->createJobs();
        $this->createLicenses();
        $this->createLicenseLogs();
        $this->createLoginLogs();
        $this->createLogs();
        $this->createMaintenanceWhitelistIps();
        $this->createMatrixQuestions();
        $this->createMentors();
        $this->createMessageReceiver();
        $this->createMessages();
        $this->createManagers();
        $this->createMatchingQuestionAnswerLinks();
        $this->createMatchingQuestionAnswers();
        $this->createMatchingQuestions();
        $this->createMatrixQuestionAnswerSubQuestions();
        $this->createMatrixQuestionAnswers();
        $this->createMatrixQuestionSubQuestions();
        $this->createMultipleChoiceQuestionAnswerLinks();
        $this->createMultipleChoiceQuestionAnswers();
        $this->createMultipleChoiceQuestions();
        $this->onboarding_wizard_reports();
        $this->createOnboardingWizardSteps();
        $this->createOnboardingWizardUserStates();
        $this->createOnboardingWizardUserSteps();
        $this->onboarding_wizards();
        $this->createOpenQuestions();
        $this->createPValueAttainments();
        $this->createPValueUsers();
        $this->createPValues();
        $this->password_resets();
        $this->createPeriods();
        $this->createQuestionAttachments();
        $this->createQuestionAttainments();
        $this->createQuestionAuthors();
        $this->createQuestions();
        $this->createRankingQuestionAnswerLinks();
        $this->createRankingQuestionAnswers();
        $this->createRankingQuestions();
        $this->createRatings();
        $this->createRoles();
        $this->createSalesOrganizations();
        $this->createSamlMessages();
        $this->createSchoolAddresses();
        $this->createSchoolClassImportLogs();
        $this->createSchoolClasses();
        $this->createSchoolContacts();
        $this->createSchoolLocationAddresses();
        $this->createSchoolLocationContacts();
        $this->createSchoolLocationEducationLevels();
        $this->createSchoolLocationIps();
        $this->createSchoolLocationReports();
        $this->createSchoolLocationSchoolYears();
        $this->createSchoolLocationSections();
        $this->createSchoolLocationSharedSections();
        $this->createSchoolLocationUser();
        $this->createSchoolLocations();
        $this->createSchoolYears();
        $this->createSchools();
        $this->createSearchFilters();
        $this->createSections();
        $this->createShortcodeClicks();
        $this->createShortcodes();
        $this->somtodaycount();
        $this->createStudentParents();
        $this->createStudents();
        $this->createBaseSubjects();
        $this->createSubjects();
        $this->createSupportTakeOverLogs();
        $this->createTagRelations();
        $this->createTags();
        $this->createTeacherImportLogs();
        $this->createTeachers();
        $this->temporary_login();
        $this->createTestAuthors();
        $this->createTestKinds();
        $this->createTestParticipants();
        $this->createTestQuestions();
        $this->createTestRatingParticipants();
        $this->createTestTakeCodes();
        $this->createTestTakeEventTypes();
        $this->createTestTakeEvents();
        $this->createTestTakeStatusLogs();
        $this->createTestTakeStatuses();
        $this->createTestTake();
        $this->createTests();
        $this->createText2speech();
        $this->createText2speechLog();
        $this->createUmbrellaOrganizationAddresses();
        $this->createUmbrellaOrganizationContacts();
        $this->createUserRoles();
        $this->createUsers();
        $this->trial_periods();
        $this->createUserInfosDontShows();
        $this->createUwlrSoapEntries();
        $this->createUwlrSoapResults();
//        $this->createAnswerRatings();
        $this->createAverageRating();
        $this->createUserFeatureSettings();
        $this->createUserSystemSettings();
        $this->createMailsSend();
        $this->createRttiExportLogs();
        $this->createVersions();
        $this->createWordLists();
        $this->createWords();
        $this->createWordListWord();

        (new \Database\Seeders\SqLiteSeeder())->run();
        //        Artisan::call('db:seed', ['--class' => 'SqLiteSeeder',]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('password_resets');
    }


//CREATE TABLE `addresses` (
//`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NOT NULL default '0000-00-00 00:00:00',
//`updated_at` timestamp NOT NULL default '0000-00-00 00:00:00',
//`deleted_at` timestamp NULL default NULL,
//`name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
//`address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
//`postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
//`city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
//`country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
//`uuid` binary(16) default NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `addresses_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;

    private function createAddresses()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('name', 45);
            $table->string('address', 60);
            $table->string('postal', 7);
            $table->string('city', 60);
            $table->string('country', 60);
            $table->efficientUuid('uuid')->index()->unique()->nullable();
        });
    }


//
//# Dump of table answer_parent_questions
//# ------------------------------------------------------------
//
//CREATE TABLE `answer_parent_questions` (
//`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NOT NULL default '0000-00-00 00:00:00',
//`updated_at` timestamp NOT NULL default '0000-00-00 00:00:00',
//`deleted_at` timestamp NULL default NULL,
//`answer_id` int(10) unsigned NOT NULL,
//`group_question_id` int(10) unsigned NOT NULL,
//`level` int(10) unsigned NOT NULL,
//PRIMARY KEY (`id`),
//KEY `fk_answer_parent_questions_answers1_idx` (`answer_id`),
//KEY `fk_answer_parent_questions_group_questions1_idx` (`group_question_id`),
//CONSTRAINT `fk_answer_parent_questions_answers1` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
//CONSTRAINT `fk_answer_parent_questions_group_questions1` FOREIGN KEY (`group_question_id`) REFERENCES `group_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
    private function createAnswerParentQuestions()
    {
        Schema::create('answer_parent_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('answer_id')->unsigned()->index('fk_answer_parent_questions_answers1_idx');
            $table->integer('group_question_id')->unsigned()->index('fk_answer_parent_questions_group_questions1_idx');
            $table->integer('level')->unsigned();
//            $table->foreign('answer_id',
//                'fk_answer_parent_questions_answers1')->references('id')->on('answers')->onUpdate('CASCADE')->onDelete('CASCADE');
//            $table->foreign('group_question_id',
//                'fk_answer_parent_questions_group_questions1')->references('id')->on('group_questions')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


//# Dump of table answer_ratings
//# ------------------------------------------------------------
//
//CREATE TABLE `answer_ratings` (
//`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NOT NULL default '0000-00-00 00:00:00',
//`updated_at` timestamp NOT NULL default '0000-00-00 00:00:00',
//`deleted_at` timestamp NULL default NULL,
//`answer_id` int(10) unsigned NOT NULL,
//`user_id` int(10) unsigned default NULL,
//`test_take_id` int(10) unsigned NOT NULL,
//`type` enum('SYSTEM', 'STUDENT', 'TEACHER') COLLATE utf8_unicode_ci NOT NULL,
//`rating` decimal(11, 1) unsigned default NULL,
//`advise` text COLLATE utf8_unicode_ci,
//PRIMARY KEY (`id`),
//KEY `fk_answer_ratings_answers1_idx` (`answer_id`),
//KEY `fk_answer_ratings_users1_idx` (`user_id`),
//KEY `fk_answer_ratings_test_takes1_idx` (`test_take_id`),
//CONSTRAINT `fk_answer_ratings_answers1` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
//CONSTRAINT `fk_answer_ratings_test_takes1` FOREIGN KEY (`test_take_id`) REFERENCES `test_takes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
//CONSTRAINT `fk_answer_ratings_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;

    private function createAnswerRatings()
    {
        Schema::create('answer_ratings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('answer_id')->unsigned()->index('fk_answer_ratings_answers1_idx');
            $table->integer('user_id')->unsigned()->nullable()->index('fk_answer_ratings_users1_idx');
            $table->integer('test_rating_id')->unsigned()->nullable()->index('fk_answer_ratings_test_ratings1_idx');
            $table->integer('test_take_id');
            $table->enum('type', ['SYSTEM', 'STUDENT', 'TEACHER']);
            $table->decimal('rating', 11, 1)->unsigned()->nullable();
            $table->text('advice')->nullable();
            $table->json('json')->nullable();
        });
    }

//
//# Dump of table answers
//# ------------------------------------------------------------
//
//CREATE TABLE `answers` (
//`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NOT NULL default '0000-00-00 00:00:00',
//`updated_at` timestamp NOT NULL default '0000-00-00 00:00:00',
//`deleted_at` timestamp NULL default NULL,
//`test_participant_id` int(10) unsigned NOT NULL,
//`question_id` int(10) unsigned NOT NULL,
//`json` longtext COLLATE utf8_unicode_ci,
//`note` longblob,
//`order` int(10) unsigned NOT NULL,
//`time` int(10) unsigned NOT NULL default '0',
//`done` tinyint(1) NOT NULL default '0',
//`final_rating` decimal(11, 1) unsigned default NULL,
//`ignore_for_rating` tinyint(1) NOT NULL,
//`uuid` binary(16) default NULL,
//`closed` tinyint(1) NOT NULL default '0',
//`closed_group` tinyint(1) NOT NULL default '0',
//PRIMARY KEY (`id`),
//UNIQUE KEY `unique_test_participants_has_questions` (`test_participant_id`, `question_id`),
//UNIQUE KEY `answers_uuid_unique` (`uuid`),
//KEY `fk_test_participants_has_questions_test_participants1_idx` (`test_participant_id`),
//KEY `fk_test_participants_has_questions_questions1_idx` (`question_id`),
//CONSTRAINT `fk_test_participants_has_questions_questions1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
//CONSTRAINT `fk_test_participants_has_questions_test_participants1` FOREIGN KEY (`test_participant_id`) REFERENCES `test_participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;

    private function createAnswers()
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('test_participant_id')->unsigned()->index(
                'fk_test_participants_has_questions_test_participants1_idx'
            );
            $table->integer('question_id')->unsigned()->index('fk_test_participants_has_questions_questions1_idx');
            $table->longText('json')->nullable();
            $table->binary('note')->nullable();
            $table->integer('order')->unsigned();
            $table->integer('time')->unsigned()->default(0);
            $table->boolean('done')->default(0);
            $table->unique(['test_participant_id', 'question_id'], 'unique_test_participants_has_questions');
            $table->decimal('final_rating', 11, 1)->unsigned()->nullable();
            $table->boolean('ignore_for_rating')->default(0);
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->boolean('closed')->boolean()->default(false);
            $table->boolean('closed_group')->default(false);
            $table->text('commented_answer')->nullable();
        });
    }

//
//# Dump of table answers_feedback
//# ------------------------------------------------------------
//
//CREATE TABLE `answers_feedback` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`answer_id` int(11) NOT NULL,
//`user_id` int(11) NOT NULL,
//`message` text COLLATE utf8_unicode_ci NOT NULL,
//`uuid` binary(16) default NULL,
//`deleted_at` timestamp NULL default NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `answers_feedback_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
    private function createAnswersFeedback()
    {
        Schema::create('answers_feedback', function (Blueprint $table) {
            $table->id();
            $table->integer('answer_id')->references('id')->on('answers');
            $table->integer('user_id')->references('id')->on('users');
            $table->string('message', 240);
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->integer('order')->nullable();
            $table->string('thread_id')->nullable();
            $table->string('comment_id')->nullable();
            $table->string('comment_color')->nullable();
            $table->string('comment_emoji')->nullable();
        });
    }
//
//# Dump of table app_version_infos
//# ------------------------------------------------------------
//
//CREATE TABLE `app_version_infos` (
//`id` char(36) COLLATE utf8_unicode_ci NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`user_id` int(11) NOT NULL,
//`version` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`os` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`headers` text COLLATE utf8_unicode_ci,
//`version_check_result` varchar(255) COLLATE utf8_unicode_ci default NULL,
//PRIMARY KEY (`id`),
//KEY `app_version_infos_user_id_index` (`user_id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createAppVersionInfos()
    {
        Schema::create('app_version_infos', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('user_id')->index();
            $table->string('version')->nullable();
            $table->string('os')->nullable();
            $table->text('headers')->nullable();
            $table->string('version_check_result')->nullable();
            $table->string('user_os')->nullable();
            $table->string('user_os_version')->nullable();
            $table->string('platform')->nullable();
            $table->string('platform_version')->nullable();
        });
    }
//
//
//# Dump of table archived_models
//# ------------------------------------------------------------
//
//CREATE TABLE `archived_models` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`archivable_model_id` int(11) NOT NULL,
//`archivable_model_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`user_id` int(11) NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createArchivedModels()
    {
        Schema::create('archived_models', function (Blueprint $table) {
            $table->id();
            $table->integer('archivable_model_id')->index();
            $table->string('archivable_model_type');
            $table->integer('user_id')->index();
            $table->timestamps();
            $table->softDeletes();;
            $table->index(['archivable_model_id', 'archivable_model_type', 'user_id'],
                'model_id_and_type_user_id_index');
            $table->index(['archivable_model_type', 'user_id'], 'archivable_model_type_user_id_index');
        });
    }
//
//# Dump of table cache
//# ------------------------------------------------------------
//
//CREATE TABLE `cache` (
//`key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`value` mediumblob NOT NULL,
//`expiration` int(11) NOT NULL,
//UNIQUE KEY `cache_key_unique` (`key`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createCache()
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->unique();
            $table->binary('value');
            $table->integer('expiration');
        });
    }
//
//# Dump of table cito_export_rows
//# ------------------------------------------------------------
//
//CREATE TABLE `cito_export_rows` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`brin` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`user_id` bigint(20) default NULL,
//`vak` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`leerdoel` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`answered_at` datetime default NULL,
//`item_1` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`item_2` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`item_3` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`item_4` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`item_5` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`item_6` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`item_7` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`item_8` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`item_9` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`item_10` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`item_11` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`item_12` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`item_13` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`item_14` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`item_15` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`item_16` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`answer_1` text COLLATE utf8_unicode_ci,
//`answer_2` text COLLATE utf8_unicode_ci,
//`answer_3` text COLLATE utf8_unicode_ci,
//`answer_4` text COLLATE utf8_unicode_ci,
//`answer_5` text COLLATE utf8_unicode_ci,
//`answer_6` text COLLATE utf8_unicode_ci,
//`answer_7` text COLLATE utf8_unicode_ci,
//`answer_8` text COLLATE utf8_unicode_ci,
//`answer_9` text COLLATE utf8_unicode_ci,
//`answer_10` text COLLATE utf8_unicode_ci,
//`answer_11` text COLLATE utf8_unicode_ci,
//`answer_12` text COLLATE utf8_unicode_ci,
//`answer_13` text COLLATE utf8_unicode_ci,
//`answer_14` text COLLATE utf8_unicode_ci,
//`answer_15` text COLLATE utf8_unicode_ci,
//`answer_16` text COLLATE utf8_unicode_ci,
//`score_1` int(11) default NULL,
//`score_2` int(11) default NULL,
//`score_3` int(11) default NULL,
//`score_4` int(11) default NULL,
//`score_5` int(11) default NULL,
//`score_6` int(11) default NULL,
//`score_7` int(11) default NULL,
//`score_8` int(11) default NULL,
//`score_9` int(11) default NULL,
//`score_10` int(11) default NULL,
//`score_11` int(11) default NULL,
//`score_12` int(11) default NULL,
//`score_13` int(11) default NULL,
//`score_14` int(11) default NULL,
//`score_15` int(11) default NULL,
//`score_16` int(11) default NULL,
//`question_type` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`test_take_id` int(11) default NULL,
//`test_participant_id` int(11) default NULL,
//`question_id` int(11) default NULL,
//`answer_id` int(11) default NULL,
//`json` text COLLATE utf8_unicode_ci,
//`number` int(11) default NULL,
//`export` tinyint(1) NOT NULL default '0',
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createCitoExportRows()
    {
        Schema::create('cito_export_rows', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('brin')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->string('vak')->nullable();
            $table->string('leerdoel')->nullable();
            $table->datetime('answered_at')->nullable();
            $table->string('item_1')->nullable();
            $table->string('item_2')->nullable();
            $table->string('item_3')->nullablee();
            $table->string('item_4')->nullable();
            $table->string('item_5')->nullable();
            $table->string('item_6')->nullable();
            $table->string('item_7')->nullable();
            $table->string('item_8')->nullable();
            $table->string('item_9')->nullable();
            $table->string('item_10')->nullable();
            $table->string('item_11')->nullable();
            $table->string('item_12')->nullable();
            $table->string('item_13')->nullable();
            $table->string('item_14')->nullable();
            $table->string('item_15')->nullable();
            $table->string('item_16')->nullable();

            $table->text('answer_1')->nullable();
            $table->text('answer_2')->nullable();
            $table->text('answer_3')->nullable();
            $table->text('answer_4')->nullable();
            $table->text('answer_5')->nullable();
            $table->text('answer_6')->nullable();
            $table->text('answer_7')->nullable();
            $table->text('answer_8')->nullable();
            $table->text('answer_9')->nullable();
            $table->text('answer_10')->nullable();
            $table->text('answer_11')->nullable();
            $table->text('answer_12')->nullable();
            $table->text('answer_13')->nullable();
            $table->text('answer_14')->nullable();
            $table->text('answer_15')->nullable();
            $table->text('answer_16')->nullable();

            $table->integer('score_1')->nullable();
            $table->integer('score_2')->nullable();
            $table->integer('score_3')->nullable();
            $table->integer('score_4')->nullable();
            $table->integer('score_5')->nullable();
            $table->integer('score_6')->nullable();
            $table->integer('score_7')->nullable();
            $table->integer('score_8')->nullable();
            $table->integer('score_9')->nullable();
            $table->integer('score_10')->nullable();
            $table->integer('score_11')->nullable();
            $table->integer('score_12')->nullable();
            $table->integer('score_13')->nullable();
            $table->integer('score_14')->nullable();
            $table->integer('score_15')->nullable();
            $table->integer('score_16')->nullable();
            $table->string('question_type')->nullable();
            $table->integer('test_take_id')->nullable();
            $table->integer('test_participant_id')->nullable();
            $table->integer('question_id')->nullable();
            $table->integer('answer_id')->nullable();
            $table->text('json')->nullable();
            $table->integer('number')->nullable();
            $table->boolean('export')->default(false);
        });
    }

    /**
     * CREATE TABLE `completion_question_answer_links` (
     * `completion_question_id` int(10) unsigned NOT NULL,
     * `completion_question_answer_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `order` int(11) NOT NULL DEFAULT '0',
     * PRIMARY KEY (`completion_question_id`,`completion_question_answer_id`),
     * KEY `fk_completion_question_answer_links_completion_questions1_idx` (`completion_question_id`),
     * KEY `fk_completion_question_answer_links_completion_question_ans_idx` (`completion_question_answer_id`),
     * CONSTRAINT `fk_completion_question_answer_links_completion_question_answe1` FOREIGN KEY (`completion_question_answer_id`) REFERENCES `completion_question_answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_completion_question_answer_links_completion_questions1` FOREIGN KEY (`completion_question_id`) REFERENCES `completion_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createCompletionQuestionAnswerLinks()
    {
        Schema::create('completion_question_answer_links', function (Blueprint $table) {
            $table->integer('completion_question_id')->unsigned();
            $table->integer('completion_question_answer_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('order')->default('0');
            $table->primary(['completion_question_id', 'completion_question_answer_id'])->name('c_q_id_c_q_a_id_index');
//            $table->foreign('completion_question_answer_id')->references('id')->on('completion_question_answers');
//            $table->foreign('completion_question_id')->references('id')->on('completion_questions');
        });
    }

    /**
     * CREATE TABLE `completion_question_answers` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `tag` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `answer` text COLLATE utf8_unicode_ci,
     * `correct` tinyint(1) NOT NULL,
     * PRIMARY KEY (`id`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=210628 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createCompletionQuestionAnswers()
    {
        Schema::create('completion_question_answers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('tag', 45)->nullable();
            $table->text('answer');
            $table->tinyInteger('correct');
        });
    }

    /**
     * CREATE TABLE `completion_questions` (
     * `id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `subtype` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
     * `rating_method` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * `auto_check_answer` tinyint(1) NOT NULL DEFAULT '0',
     * `auto_check_answer_case_sensitive` tinyint(1) NOT NULL DEFAULT '1',
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `completion_questions_uuid_unique` (`uuid`),
     * KEY `fk_completion_questions_questions1_idx` (`id`),
     * CONSTRAINT `fk_completion_questions_questions1` FOREIGN KEY (`id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createCompletionQuestions()
    {
        Schema::create('completion_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('subtype', 45);
            $table->string('rating_method', 45)->nullable();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->tinyInteger('auto_check_answer')->default('0');
            $table->tinyInteger('auto_check_answer_case_sensitive')->default('1');
//            $table->foreign('id')->references('id')->on('questions');
        });
    }

    /**
     * CREATE TABLE `contacts` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
     * `address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
     * `city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `phone` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
     * `mobile` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
     * `email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
     * `note` text COLLATE utf8_unicode_ci,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `contacts_uuid_unique` (`uuid`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createContacts()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('name', 45);
            $table->string('address', 60);
            $table->string('postal', 7);
            $table->string('city', 60);
            $table->string('country', 60);
            $table->string('phone', 15);
            $table->string('mobile', 15);
            $table->string('email', 150);
            $table->text('note');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
        });
    }

//
//# Dump of table database_questions
//# ------------------------------------------------------------
//
//CREATE TABLE `database_questions` (
//`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createDatabaseQuestions()
    {
        Schema::create('database_questions', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
        });
    }
//
//# Dump of table default_sections
//# ------------------------------------------------------------
//
//CREATE TABLE `default_sections` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`name` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`demo` tinyint(1) default '0',
//`uuid` binary(16) default NULL,
//`level` varchar(255) COLLATE utf8_unicode_ci NOT NULL default 'VO',
//`deleted_at` timestamp NULL default NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `default_sections_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createDefaultSections()
    {
        Schema::create('default_sections', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('name')->nullable();
            $table->boolean('demo')->default(0)->nullable();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->string('level')->default('VO');
        });
    }
//
//# Dump of table default_subjects
//# ------------------------------------------------------------
//
//CREATE TABLE `default_subjects` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`uuid` binary(16) default NULL,
//`name` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`abbreviation` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`base_subject_id` bigint(20) default NULL,
//`default_section_id` bigint(20) NOT NULL,
//`education_levels` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`demo` tinyint(1) NOT NULL default '0',
//`deleted_at` timestamp NULL default NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `default_subjects_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
    private function createDefaultSubjects()
    {
        Schema::create('default_subjects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->string('name')->nullable();
            $table->string('abbreviation')->nullable();
            $table->bigInteger('base_subject_id')->nullable();
            $table->bigInteger('default_section_id');
            $table->string('education_levels')->nullable();
            $table->boolean('demo')->default(false);
        });
    }
//# Dump of table demo_teacher_registrations
//# ------------------------------------------------------------
//
//CREATE TABLE `demo_teacher_registrations` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`school_location` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`website_url` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`postcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`gender` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`gender_different` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`name_first` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`name_suffix` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`subjects` text COLLATE utf8_unicode_ci,
//`mobile` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`remarks` text COLLATE utf8_unicode_ci,
//`how_did_you_hear_about_test_correct` text COLLATE utf8_unicode_ci,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`user_id` int(11) NOT NULL,
//`abbreviation` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`house_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`email` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`registration_email_confirmed` int(11) default NULL,
//`invitee` int(11) default NULL,
//`level` varchar(15) COLLATE utf8_unicode_ci NOT NULL default 'VO',
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createDemoTeacherRegistrations()
    {
        Schema::create('demo_teacher_registrations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('school_location');
            $table->string('website_url')->nullable();
            $table->string('address');
            $table->string('postcode');
            $table->string('city');
            $table->string('gender');
            $table->string('gender_different')->nullable();
            $table->string('name_first');
            $table->string('name_suffix')->nullable();
            $table->string('name');
            $table->string('username');
            $table->text('subjects')->nullable();
            $table->string('mobile')->nullable();
            $table->text('remarks')->nullable();
            $table->text('how_did_you_hear_about_test_correct')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();;
            $table->string('abbreviation')->nullable();
            $table->string('house_number');
            $table->string('email')->nullable();
            $table->integer('registration_email_confirmed')->nullable();
            $table->integer('invitee')->nullable();
            $table->string('level', 15)->default('VO');
        });
    }
//
//# Dump of table deployments
//# ------------------------------------------------------------
//
//CREATE TABLE `deployments` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`uuid` binary(16) NOT NULL,
//`content` text COLLATE utf8_unicode_ci NOT NULL,
//`notification` text COLLATE utf8_unicode_ci NOT NULL,
//`deployment_day` date NOT NULL,
//`status` varchar(255) COLLATE utf8_unicode_ci NOT NULL default 'PLANNED',
//PRIMARY KEY (`id`),
//UNIQUE KEY `deployments_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createDeployments()
    {
        Schema::create('deployments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->text('content');
            $table->text('notification');
            $table->date('deployment_day');
            $table->string('status')->default('PLANNED'); // options: PLANNED, NOTIFY, ACTIVE, DONE,
        });
    }
//
//
//# Dump of table ean_codes
//# ------------------------------------------------------------
//
//CREATE TABLE `ean_codes` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`ean` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`description` text COLLATE utf8_unicode_ci,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createEanCodes()
    {
        Schema::create('ean_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ean');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();;
        });
    }

    /**
     * CREATE TABLE `drawing_questions` (
     * `id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `answer` longblob,
     * `answer_svg` longblob,
     * `grid` int(10) unsigned DEFAULT NULL,
     * `grid_svg` char(4) DEFAULT '0.00',
     * `zoom_group` json DEFAULT NULL,
     * `bg_name` varchar(255) DEFAULT NULL,
     * `bg_size` int(10) unsigned DEFAULT NULL,
     * `bg_mime_type` varchar(255) DEFAULT NULL,
     * `bg_extension` varchar(10) DEFAULT NULL,
     * `question_svg` longblob,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `drawing_questions_uuid_unique` (`uuid`),
     * KEY `fk_drawing_questions_questions1_idx` (`id`),
     * CONSTRAINT `fk_drawing_questions_questions1` FOREIGN KEY (`id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
     */

    private function createDrawingQuestions()
    {
        Schema::create('drawing_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->binary('answer');
            $table->binary('answer_svg');
            $table->integer('grid')->unsigned()->nullable();
            $table->char('grid_svg', 4)->default('0.00');
            $table->json('zoom_group')->nullable();
            $table->string('bg_name')->nullable();
            $table->integer('bg_size')->unsigned()->nullable();
            $table->string('bg_mime_type')->nullable();
            $table->string('bg_extension')->nullable();
            $table->binary('question_svg');;
            $table->efficientUuid('uuid')->index()->unique()->nullable();
//            $table->foreign('id')->references('id')->on('questions');
        });
    }

    /**
     * CREATE TABLE `education_levels` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `max_years` int(10) unsigned NOT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `education_levels_uuid_unique` (`uuid`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createEducationLevels()
    {
        Schema::create('education_levels', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('attainment_education_level_id')->nullable();
            $table->integer('min_attainment_year')->nullable();
            $table->string('name', 45)->nullable();
            $table->integer('max_years')->unsigned();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
        });
    }


    /**
     * CREATE TABLE `discussing_parent_questions` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `test_take_id` int(10) unsigned NOT NULL,
     * `group_question_id` int(10) unsigned NOT NULL,
     * `level` int(10) unsigned NOT NULL,
     * PRIMARY KEY (`id`),
     * KEY `fk_discussing_parent_questions_test_takes1_idx` (`test_take_id`),
     * KEY `fk_discussing_parent_questions_group_questions1_idx` (`group_question_id`),
     * CONSTRAINT `fk_discussing_parent_questions_group_questions1` FOREIGN KEY (`group_question_id`) REFERENCES `group_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_discussing_parent_questions_test_takes1` FOREIGN KEY (`test_take_id`) REFERENCES `test_takes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=3016 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */

    private function createDiscussingParentQuestions()
    {
        Schema::create('discussing_parent_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('test_take_id')->unsigned();
            $table->integer('group_question_id')->unsigned();
            $table->integer('level')->unsigned();
//            $table->foreign('group_question_id')->references('id')->on('group_questions');
//            $table->foreign('test_take_id')->references('id')->on('test_takes');
        });
    }
//
//
//# Dump of table eckid_user
//# ------------------------------------------------------------
//
//CREATE TABLE `eckid_user` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`user_id` bigint(20) NOT NULL,
//`eckid` text COLLATE utf8_unicode_ci NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`eckid_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//PRIMARY KEY (`id`),
//KEY `eckid_user_eckid_hash_index` (`eckid_hash`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createEckidUser()
    {
        Schema::create('eckid_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->text('eckid');
            $table->timestamps();
            $table->softDeletes();;
            $table->string('eckid_hash')->index();
        });
    }
//
//
//# Dump of table edu_ix_registrations
//# ------------------------------------------------------------
//
//CREATE TABLE `edu_ix_registrations` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`digi_delivery_id` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`json` text COLLATE utf8_unicode_ci,
//`user_id` bigint(20) default NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createEduIxRegistrations()
    {
        Schema::create('edu_ix_registrations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('digi_delivery_id');
            $table->text('json')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();;
        });
    }
//
//
//# Dump of table email_confirmations
//# ------------------------------------------------------------
//
//CREATE TABLE `email_confirmations` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`user_id` bigint(20) NOT NULL,
//`uuid` blob NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createEmailConfirmations()
    {
        Schema::create('email_confirmations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->binary('uuid');
            $table->timestamps();
            $table->softDeletes();;
        });
    }

    /**
     * CREATE TABLE `failed_jobs` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `connection` text COLLATE utf8_unicode_ci NOT NULL,
     * `queue` text COLLATE utf8_unicode_ci NOT NULL,
     * `payload` longtext COLLATE utf8_unicode_ci NOT NULL,
     * `exception` longtext COLLATE utf8_unicode_ci NOT NULL,
     * `failed_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * PRIMARY KEY (`id`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */

    private function createFailedJobs()
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
            $table->string('uuid')->after('id')->nullable()->unique();
        });
    }
//
//
//# Dump of table failed_logins
//# ------------------------------------------------------------
//
//CREATE TABLE `failed_logins` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`ip` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`solved` tinyint(1) NOT NULL default '0',
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createFailedLogins()
    {
        Schema::create('failed_logins', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('username');
            $table->string('ip')->nullable();
            $table->boolean('solved')->default(false);
        });
    }
//
//
//# Dump of table feature_settings
//# ------------------------------------------------------------
//
//CREATE TABLE `feature_settings` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`settingable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`settingable_id` bigint(20) unsigned NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//PRIMARY KEY (`id`),
//KEY `feature_settings_settingable_type_settingable_id_index` (`settingable_type`, `settingable_id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
    private function createFeatureSettings()
    {
        Schema::create('feature_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('value');
            $table->morphs('settingable'); //feature_settingable is too long
            $table->timestamps();
            $table->softDeletes();;
        });
    }
//
//# Dump of table file_management_status_logs
//# ------------------------------------------------------------
//
//CREATE TABLE `file_management_status_logs` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`file_management_id` char(36) COLLATE utf8_unicode_ci NOT NULL default '',
//`file_management_status_id` int(11) NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createFileManagementStatusLogs()
    {
        Schema::create('file_management_status_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('file_management_id', 36);
            $table->integer('file_management_status_id');
            $table->timestamps();
            $table->softDeletes();;
        });
    }
//
//
//# Dump of table file_management_statuses
//# ------------------------------------------------------------
//
//CREATE TABLE `file_management_statuses` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`displayorder` int(11) NOT NULL,
//`partof` int(11) NOT NULL,
//`colorcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createFileManagementStatuses()
    {
        Schema::create('file_management_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('name');
            $table->integer('displayorder');
            $table->integer('partof');
            $table->string('colorcode');
        });

        \DB::table('file_management_statuses')->insert([
            [
                'id'           => 1,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
                'name'         => 'Nieuw',
                'displayorder' => 1,
                'colorcode'    => 'colorcode-41',
                'partof'       => 1,
            ],
            [
                'id'           => 2,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
                'name'         => 'In behandeling',
                'displayorder' => 2,
                'colorcode'    => 'colorcode-42',
                'partof'       => 2,
            ],
            [
                'id'           => 3,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
                'name'         => 'Behandeling gepauzeerd',
                'displayorder' => 3,
                'colorcode'    => 'colorcode-43',
                'partof'       => 2,
            ],
            [
                'id'           => 4,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
                'name'         => 'gereed voor eerste controle',
                'displayorder' => 4,
                'colorcode'    => 'colorcode-44',
                'partof'       => 2,
            ],
            [
                'id'           => 5,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
                'name'         => 'gereed voor tweede controle',
                'displayorder' => 5,
                'colorcode'    => 'colorcode-44',
                'partof'       => 2,
            ],
            [
                'id'           => 6,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
                'name'         => 'bijna klaar',
                'displayorder' => 6,
                'colorcode'    => 'colorcode-45',
                'partof'       => 2,
            ],
            [
                'id'           => 7,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
                'name'         => 'goedgekeurd',
                'displayorder' => 7,
                'colorcode'    => 'colorcode-46',
                'partof'       => 7,
            ],
            [
                'id'           => 8,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
                'name'         => 'onvolledig, items ontbreken',
                'displayorder' => 8,
                'colorcode'    => 'colorcode-47',
                'partof'       => 8,
            ],
            [
                'id'           => 9,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
                'name'         => 'Antwoordmodel ontbreekt',
                'displayorder' => 9,
                'colorcode'    => 'colorcode-47',
                'partof'       => 9,
            ],
            [
                'id'           => 10,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
                'name'         => 'Meerdere toetsen aanwezig',
                'displayorder' => 10,
                'colorcode'    => 'colorcode-47',
                'partof'       => 10,
            ],
            [
                'id'           => 11,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
                'name'         => 'Bugs',
                'displayorder' => 11,
                'colorcode'    => 'colorcode-47',
                'partof'       => 11,
            ],
            [
                'id'           => 12,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
                'name'         => 'Afgerond',
                'displayorder' => 8,
                'partof'       => 12,
                'colorcode'    => 'colorcode-46'
            ],
            [
                'id'           => 13,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
                'name'         => 'Geannuleerd',
                'displayorder' => 13,
                'partof'       => 13,
                'colorcode'    => 'colorcode-47'
            ],
            [
                'id'           => 14,
                'created_at'   => \Carbon\Carbon::now(),
                'updated_at'   => \Carbon\Carbon::now(),
                'name'         => 'Aangeleverd',
                'displayorder' => 0,
                'partof'       => 14,
                'colorcode'    => 'colorcode-2'
            ],
        ]);
    }
//
//
//# Dump of table file_managements
//# ------------------------------------------------------------
//
//CREATE TABLE `file_managements` (
//`id` char(36) COLLATE utf8_unicode_ci NOT NULL,
//`school_location_id` int(11) NOT NULL,
//`user_id` int(11) NOT NULL,
//`origname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`typedetails` text COLLATE utf8_unicode_ci,
//`file_management_status_id` int(11) NOT NULL default '1',
//`handledby` int(11) NOT NULL,
//`test_builder_code` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`notes` text COLLATE utf8_unicode_ci,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`planned_at` datetime default NULL,
//`parent_id` char(36) COLLATE utf8_unicode_ci default NULL,
//`archived` tinyint(1) NOT NULL default '0',
//`uuid` binary(16) default NULL,
//`form_id` char(36) COLLATE utf8_unicode_ci default NULL,
//`class` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`subject` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`subject_id` int(11) default NULL,
//`education_level_year` int(11) NOT NULL default '0',
//`education_level_id` int(11) NOT NULL default '0',
//`test_kind_id` int(11) NOT NULL default '0',
//`test_name` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`orig_filenames` text COLLATE utf8_unicode_ci,
//PRIMARY KEY (`id`),
//UNIQUE KEY `file_managements_uuid_unique` (`uuid`),
//KEY `file_managements_created_at_index` (`created_at`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createFileManagements()
    {
        Schema::create('file_managements', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->integer('school_location_id');
            $table->integer('user_id');
            $table->string('origname');
            $table->string('name');
            $table->string('type');
            $table->text('typedetails')->nullable();
            $table->string('status')->default('new');
            $table->integer('handledby')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('file_management_status_id')->default(1);
            $table->char('parent_id', 36)->nullable();
            $table->boolean('archived')->default(false);
            $table->index('created_at');
            $table->char('form_id', 36)->nullable();
            $table->string('class')->nullable();
            $table->string('subject')->nullable();
            $table->integer('education_level_year')->default(0);
            $table->integer('education_level_id')->default(0);
            $table->integer('test_kind_id')->default(0);
            $table->string('test_name')->nullable();
            $table->text('orig_filenames')->nullable();
            $table->string('test_builder_code')->nullable();
            $table->dateTime('planned_at')->nullable();
            $table->integer('subject_id')->nullable();
            $table->boolean('contains_publisher_content')->nullable()->default(false);
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->integer('test_id')->nullable();
        });
    }
//
//
//# Dump of table general_terms_logs
//# ------------------------------------------------------------
//
//CREATE TABLE `general_terms_logs` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`user_id` bigint(20) NOT NULL,
//`accepted_at` datetime default NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createGeneralTermsLogs()
    {
        Schema::create('general_terms_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();
            $table->bigInteger('user_id');
            $table->dateTime('accepted_at')->nullable();
        });
    }

    /**
     * CREATE TABLE `grading_scales` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
     * `system_name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `grading_scales_uuid_unique` (`uuid`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */

    private function createGradingScales()
    {
        Schema::create('grading_scales', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('name', 45);
            $table->string('system_name', 45);
            $table->efficientUuid('uuid')->index()->unique()->nullable();
        });
    }

    /**
     * CREATE TABLE `group_question_questions` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `group_question_id` int(10) unsigned NOT NULL,
     * `question_id` int(10) unsigned NOT NULL,
     * `order` int(10) unsigned NOT NULL,
     * `maintain_position` tinyint(1) NOT NULL,
     * `discuss` tinyint(1) NOT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `group_question_questions_uuid_unique` (`uuid`),
     * KEY `fk_group_question_questions_group_questions1_idx` (`group_question_id`),
     * KEY `fk_group_question_questions_questions1_idx` (`question_id`),
     * CONSTRAINT `fk_group_question_questions_group_questions1` FOREIGN KEY (`group_question_id`) REFERENCES `group_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_group_question_questions_questions1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=50168 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createGroupQuestionQuestions()
    {
        Schema::create('group_question_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('group_question_id')->unsigned();
            $table->integer('question_id')->unsigned();
            $table->integer('order')->unsigned();
            $table->tinyInteger('maintain_position');
            $table->tinyInteger('discuss');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
//            $table->foreign('group_question_id')->references('id')->on('group_questions');
//            $table->foreign('question_id')->references('id')->on('questions');
        });
    }


    /**
     * CREATE TABLE `group_questions` (
     * `id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `shuffle` tinyint(1) NOT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * `groupquestion_type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `number_of_subquestions` int(11) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `group_questions_uuid_unique` (`uuid`),
     * KEY `fk_group_questions_questions1_idx` (`id`),
     * CONSTRAINT `fk_group_questions_questions1` FOREIGN KEY (`id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    public function createGroupQuestion()
    {
        Schema::create('group_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('name')->nullable();
            $table->tinyInteger('shuffle');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->string('groupquestion_type', 50)->nullable();
            $table->integer('number_of_subquestions')->nullable();
//            $table->foreign('id')->references('id')->on('questions');
        });
    }


//
//
//# Dump of table info_role
//# ------------------------------------------------------------
//
//CREATE TABLE `info_role` (
//`info_id` bigint(20) NOT NULL,
//`role_id` bigint(20) NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//PRIMARY KEY (`info_id`, `role_id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
    private function createInfoRole()
    {
        Schema::create('info_role', function (Blueprint $table) {
            $table->bigInteger('info_id');
            $table->bigInteger('role_id');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['info_id', 'role_id'], 'info_role_key');
        });
    }
//
//# Dump of table infos
//# ------------------------------------------------------------
//
//CREATE TABLE `infos` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`uuid` binary(16) NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`title_nl` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`title_en` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`content_nl` text COLLATE utf8_unicode_ci,
//`content_en` text COLLATE utf8_unicode_ci,
//`show_from` datetime NOT NULL,
//`show_until` datetime NOT NULL,
//`status` varchar(255) COLLATE utf8_unicode_ci NOT NULL default 'INACTIVE',
//`created_by` int(11) NOT NULL,
//`for_all` tinyint(1) NOT NULL default '1',
//PRIMARY KEY (`id`),
//UNIQUE KEY `infos_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
//
    private function createInfos()
    {
        Schema::create('infos', function (Blueprint $table) {
            $table->id();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('title_nl')->nullable();
            $table->string('title_en')->nullable();
            $table->text('content_nl')->nullable();
            $table->text('content_en')->nullable();
            $table->datetime('show_from');
            $table->datetime('show_until');
            $table->string('status')->default('INACTIVE');
            $table->integer('created_by');
            $table->boolean('for_all')->default(true);
            $table->string('type')->default(\tcCore\Info::BASE_TYPE);
        });
    }
//# Dump of table infoscreen_questions
//# ------------------------------------------------------------
//
//CREATE TABLE `infoscreen_questions` (
//`id` int(10) unsigned NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`subtype` varchar(45) COLLATE utf8_unicode_ci default NULL,
//`answer` text COLLATE utf8_unicode_ci,
//`uuid` binary(16) default NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `infoscreen_questions_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createInfoscreenQuestions()
    {
        Schema::create('infoscreen_questions', function (Blueprint $table) {
            $table->integer('id')->primary()->unsigned()->index('fk_infoscreen_questions_questions1_idx');
            $table->timestamps();
            $table->softDeletes();
            $table->string('subtype', 45)->nullable();
            $table->text('answer', 400)->nullable();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
        });
    }

    /**
     * CREATE TABLE `invigilators` (
     * `test_take_id` int(10) unsigned NOT NULL,
     * `user_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`test_take_id`,`user_id`),
     * UNIQUE KEY `invigilators_uuid_unique` (`uuid`),
     * KEY `fk_test_takes_has_users_users2_idx` (`user_id`),
     * KEY `fk_test_takes_has_users_test_takes2_idx` (`test_take_id`),
     * CONSTRAINT `fk_test_takes_has_users_test_takes2` FOREIGN KEY (`test_take_id`) REFERENCES `test_takes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_test_takes_has_users_user1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */

    private function createInvigilators()
    {
        Schema::create('invigilators', function (Blueprint $table) {
            $table->integer('test_take_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->primary(['test_take_id', 'user_id']);
//            $table->foreign('test_take_id')->references('id')->on('test_takes');
//            $table->foreign('user_id')->references('id')->on('users');
        });
    }
//
//
//# Dump of table jobs
//# ------------------------------------------------------------
//
//CREATE TABLE `jobs` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`queue` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`payload` longtext COLLATE utf8_unicode_ci NOT NULL,
//`attempts` tinyint(3) unsigned NOT NULL,
//`reserved_at` int(10) unsigned default NULL,
//`available_at` int(10) unsigned NOT NULL,
//`created_at` int(10) unsigned NOT NULL,
//PRIMARY KEY (`id`),
//KEY `jobs_queue_reserved_at_index` (`queue`, `reserved_at`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createJobs()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }
//
//CREATE TABLE `licenses` (
//`id` int unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
//`updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
//`deleted_at` timestamp NULL DEFAULT NULL,
//`school_location_id` int unsigned NOT NULL,
//`start` date NOT NULL,
//`end` date DEFAULT NULL,
//`amount` int DEFAULT NULL,
//`uuid` binary(16) DEFAULT NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `licenses_uuid_unique` (`uuid`),
//KEY `fk_licenses_school_locations1_idx` (`school_location_id`),
//CONSTRAINT `fk_licenses_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
//) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
    private function createLicenses()
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('school_location_id')->unsigned()->index('fk_licenses_school_locations1_idx');
            $table->date('start');
            $table->date('end')->nullable();
            $table->integer('amount')->nullable();
//            $table->foreign('school_location_id',
//                'fk_licenses_school_locations1')->references('id')->on('school_locations')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
//
//# Dump of table license_logs
//# ------------------------------------------------------------
//
//CREATE TABLE `license_logs` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`license_id` int(11) NOT NULL,
//`amount` int(11) NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createLicenseLogs()
    {
        Schema::create('license_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('license_id');
            $table->integer('amount');
            $table->integer('amount_change');
            $table->timestamps();
            $table->softDeletes();;
        });

        License::all()->each(function (License $l) {
            \tcCore\LicenseLog::create([
                'license_id'    => $l->getKey(),
                'amount'        => $l->amount,
                'amount_change' => $l->amount,
                'created_at'    => $l->created_at,
                'updated_at'    => $l->created_at
            ]);
        });
    }
//
//
//# Dump of table login_logs
//# ------------------------------------------------------------
//
//CREATE TABLE `login_logs` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`user_id` bigint(20) NOT NULL,
//PRIMARY KEY (`id`),
//KEY `login_logs_created_at_index` (`created_at`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createLoginLogs()
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();;
            $table->index('created_at');
            $table->bigInteger('user_id');
        });
    }

    /**
     * CREATE TABLE `logs` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `uri` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
     * `uri_full` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
     * `method` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
     * `request` text COLLATE utf8_unicode_ci NOT NULL,
     * `response` longtext COLLATE utf8_unicode_ci,
     * `headers` text COLLATE utf8_unicode_ci NOT NULL,
     * `code` int(11) NOT NULL DEFAULT '-1',
     * `ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
     * `duration` double(8,2) NOT NULL,
     * `user_id` varchar(36) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `user_agent` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `success` tinyint(1) NOT NULL DEFAULT '0',
     * PRIMARY KEY (`id`),
     * KEY `logs_user_id_index` (`user_id`),
     * KEY `logs_updated_at_index` (`updated_at`)
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createLogs()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->default('0000-00-00 00:00:00');
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
            $table->string('uri');
            $table->string('uri_full');
            $table->string('method');
            $table->text('request');
            $table->longText('response');;
            $table->text('headers');
            $table->integer('code')->default('-1');
            $table->string('ip');
            $table->double('duration', 8, 2);
            $table->string('user_id', 36)->nullable();
            $table->string('user_agent')->nullable();
            $table->tinyInteger('success')->default('0');
        });
    }

//
//
//# Dump of table maintenance_whitelist_ips
//# ------------------------------------------------------------
//
//CREATE TABLE `maintenance_whitelist_ips` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`uuid` binary(16) NOT NULL,
//`ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `maintenance_whitelist_ips_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createMaintenanceWhitelistIps()
    {
        Schema::create('maintenance_whitelist_ips', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->string('ip');
            $table->string('name');
        });

        $ipList = [
            '95.97.95.106'                            => 'Sobit kantoor',
            '84.87.252.175'                           => 'Martin thuis',
            '83.85.48.248'                            => 'Carlo thuis',
            '2001:1c00:2508:7000:dc4a:597f:4da0:6758' => 'Carlo thuis',
            '77.60.34.179'                            => 'TLC kantoor',
            '77.167.20.237'                           => 'Jonathan thuis',
            '136.144.207.195'                         => 'Devportal (Grafana)'
        ];
        MaintenanceWhitelistIp::withoutEvents(function () use ($ipList) {
            foreach ($ipList as $ip => $name) {
                $model = MaintenanceWhitelistIp::make([
                    'ip'   => $ip,
                    'name' => $name,
                ]);
                $model->uuid = Ramsey\Uuid\Uuid::uuid4();
                $model->save();
            }
        });
    }
//
//
//# Dump of table matrix_questions
//# ------------------------------------------------------------
//
//CREATE TABLE `matrix_questions` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`subtype` varchar(45) COLLATE utf8_unicode_ci NOT NULL default 'SingleChoice',
//`shuffle` tinyint(1) NOT NULL default '0',
//`uuid` binary(16) default NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `matrix_questions_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createMatrixQuestions()
    {
        Schema::create('matrix_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('subtype', 45)->default('SingleChoice');
            $table->boolean('shuffle')->default(false);
            $table->efficientUuid('uuid')->index()->unique();
        });
    }

    /**
     * CREATE TABLE `mentors` (
     * `school_class_id` int(10) unsigned NOT NULL,
     * `user_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`school_class_id`,`user_id`),
     * UNIQUE KEY `mentors_uuid_unique` (`uuid`),
     * KEY `fk_mentors_school_classes1_idx` (`school_class_id`),
     * KEY `fk_mentors_users1_idx` (`user_id`),
     * CONSTRAINT `fk_mentors_school_classes1` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_mentors_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createMentors()
    {
        Schema::create('mentors', function (Blueprint $table) {
            $table->integer('school_class_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->primary(['school_class_id', 'user_id']);
//            $table->foreign('school_class_id')->references('id')->on('school_classes');
//            $table->foreign('user_id')->references('id')->on('users');
        });
    }


    /**
     * CREATE TABLE `message_receivers` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `message_id` int(10) unsigned NOT NULL,
     * `user_id` int(10) unsigned NOT NULL,
     * `type` enum('TO','CC','BCC') COLLATE utf8_unicode_ci NOT NULL,
     * `read` tinyint(1) NOT NULL,
     * PRIMARY KEY (`id`),
     * KEY `fk_message_receivers_messages1_idx` (`message_id`),
     * KEY `fk_message_receivers_users1_idx` (`user_id`),
     * CONSTRAINT `fk_message_receivers_messages1` FOREIGN KEY (`message_id`) REFERENCES `messages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createMessageReceiver()
    {
        Schema::create('message_receivers', function (Blueprint $table) {
            $table->id()->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('message_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->enum('type', ['TO', 'CC', 'BCC']);
            $table->tinyInteger('read');
//            $table->foreign('message_id')->references('id')->on('messages');
        });
    }


    /**
     * CREATE TABLE `messages` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `user_id` int(10) unsigned NOT NULL,
     * `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
     * `message` text COLLATE utf8_unicode_ci,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `messages_uuid_unique` (`uuid`),
     * KEY `fk_messages_users1_idx` (`user_id`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=138 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createMessages()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('user_id')->unsigned();
            $table->string('subject');
            $table->text('message');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
        });
    }

    /**
     * CREATE TABLE `managers` (
     * `school_class_id` int(10) unsigned NOT NULL,
     * `user_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`school_class_id`,`user_id`),
     * UNIQUE KEY `managers_uuid_unique` (`uuid`),
     * KEY `fk_managers_school_classes1_idx` (`school_class_id`),
     * KEY `fk_managers_users1_idx` (`user_id`),
     * CONSTRAINT `fk_managers_school_classes1` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_managers_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createManagers()
    {
        Schema::create('managers', function (Blueprint $table) {
            $table->integer('school_class_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->primary(['school_class_id', 'user_id']);
//            $table->foreign('school_class_id')->references('id')->on('school_classes');
//            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * CREATE TABLE `matching_question_answer_links` (
     * `matching_question_id` int(10) unsigned NOT NULL,
     * `matching_question_answer_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `order` int(10) unsigned NOT NULL,
     * PRIMARY KEY (`matching_question_id`,`matching_question_answer_id`),
     * KEY `fk_matching_question_answer_links_matching_questions1_idx` (`matching_question_id`),
     * KEY `fk_matching_question_answer_links_matching_question_answers_idx` (`matching_question_answer_id`),
     * CONSTRAINT `fk_matching_question_answer_links_matching_question_answers1` FOREIGN KEY (`matching_question_answer_id`) REFERENCES `matching_question_answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_matching_question_answer_links_matching_questions1` FOREIGN KEY (`matching_question_id`) REFERENCES `matching_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     *
     */
    private function createMatchingQuestionAnswerLinks()
    {
        Schema::create('matching_question_answer_links', function (Blueprint $table) {
            $table->integer('matching_question_id')->unsigned();
            $table->integer('matching_question_answer_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('order')->unsigned();
            $table->primary(['matching_question_id', 'matching_question_answer_id'])->name(
                'ma_qu_id_ma_qu_an_id_pIndex'
            );
//            $table->foreign('matching_question_answer_id')->references('id')->on('matching_question_answers');
//            $table->foreign('matching_question_id')->references('id')->on('matching_questions');
        });
    }

    /**
     * CREATE TABLE `matching_question_answers` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `correct_answer_id` int(10) unsigned DEFAULT NULL,
     * `answer` text COLLATE utf8_unicode_ci,
     * `type` enum('LEFT','RIGHT') COLLATE utf8_unicode_ci DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * KEY `fk_matching_question_answers_matching_question_answers1_idx` (`correct_answer_id`),
     * CONSTRAINT `fk_matching_question_answers_matching_question_answers1` FOREIGN KEY (`correct_answer_id`) REFERENCES `matching_question_answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=31636 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createMatchingQuestionAnswers()
    {
        Schema::create('matching_question_answers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('correct_answer_id')->unsigned()->nullable();
            $table->text('answer');
            $table->enum('type', ['LEFT', 'RIGHT'])->nullable();
//            $table->foreign('correct_answer_id')->references('id')->on('matching_question_answers');
        });
    }

    /**
     * CREATE TABLE `matching_questions` (
     * `id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `subtype` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `matching_questions_uuid_unique` (`uuid`),
     * KEY `fk_matching_questions_questions1_idx` (`id`),
     * CONSTRAINT `fk_matching_questions_questions1` FOREIGN KEY (`id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createMatchingQuestions()
    {
        Schema::create('matching_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('subtype', 45)->default('');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
//            $table->foreign('id')->references('id')->on('questions');
        });
    }

    /**
     * CREATE TABLE `matrix_question_answer_sub_questions` (
     * `matrix_question_sub_question_id` bigint(20) unsigned NOT NULL,
     * `matrix_question_answer_id` bigint(20) unsigned NOT NULL,
     * `created_at` timestamp NULL DEFAULT NULL,
     * `updated_at` timestamp NULL DEFAULT NULL,
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * PRIMARY KEY (`matrix_question_sub_question_id`,`matrix_question_answer_id`),
     * KEY `fk_matrix_question_answer` (`matrix_question_answer_id`),
     * KEY `mqsqid-idx` (`matrix_question_sub_question_id`),
     * CONSTRAINT `fk_matrix_question_answer` FOREIGN KEY (`matrix_question_answer_id`) REFERENCES `matrix_question_answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_matrix_question_sub` FOREIGN KEY (`matrix_question_sub_question_id`) REFERENCES `matrix_question_sub_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createMatrixQuestionAnswerSubQuestions()
    {
        Schema::create('matrix_question_answer_sub_questions', function (Blueprint $table) {
            $table->bigInteger('matrix_question_sub_question_id')->unsigned();
            $table->bigInteger('matrix_question_answer_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->primary(['matrix_question_sub_question_id', 'matrix_question_answer_id'])->name(
                'prim_matrix_question'
            );
//            $table->foreign('matrix_question_answer_id')->references('id')->on('matrix_question_answers');
//            $table->foreign('matrix_question_sub_question_id')->references('id')->on('matrix_question_sub_questions');
        });
    }

    /**
     * CREATE TABLE `matrix_question_answers` (
     * `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NULL DEFAULT NULL,
     * `updated_at` timestamp NULL DEFAULT NULL,
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `matrix_question_id` bigint(20) unsigned NOT NULL,
     * `answer` text COLLATE utf8_unicode_ci,
     * `order` int(11) NOT NULL,
     * PRIMARY KEY (`id`),
     * KEY `matrix_question_answers_matrix_question_id_foreign` (`matrix_question_id`),
     * CONSTRAINT `matrix_question_answers_matrix_question_id_foreign` FOREIGN KEY (`matrix_question_id`) REFERENCES `matrix_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=12355 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createMatrixQuestionAnswers()
    {
        Schema::create('matrix_question_answers', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->bigInteger('matrix_question_id')->unsigned();
            $table->text('answer');
            $table->integer('order');
//            $table->foreign('matrix_question_id')->references('id')->on('matrix_questions');
        });
    }

    /**
     * CREATE TABLE `matrix_question_sub_questions` (
     * `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NULL DEFAULT NULL,
     * `updated_at` timestamp NULL DEFAULT NULL,
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `matrix_question_id` bigint(20) unsigned NOT NULL,
     * `sub_question` text COLLATE utf8_unicode_ci NOT NULL,
     * `order` int(11) NOT NULL,
     * `score` int(11) NOT NULL,
     * PRIMARY KEY (`id`),
     * KEY `matrix_question_sub_questions_matrix_question_id_foreign` (`matrix_question_id`),
     * CONSTRAINT `matrix_question_sub_questions_matrix_question_id_foreign` FOREIGN KEY (`matrix_question_id`) REFERENCES `matrix_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=17581 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createMatrixQuestionSubQuestions()
    {
        Schema::create('matrix_question_sub_questions', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->bigInteger('matrix_question_id')->unsigned();
            $table->text('sub_question');
            $table->integer('order');
            $table->integer('score');
//            $table->foreign('matrix_question_id')->references('id')->on('matrix_questions');
        });
    }
//
//
//# Dump of table migrations
//# ------------------------------------------------------------
//
//CREATE TABLE `migrations` (
//`migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`batch` int(11) NOT NULL,
//PRIMARY KEY (`migration`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;

    /**
     * CREATE TABLE `multiple_choice_question_answer_links` (
     * `multiple_choice_question_id` int(10) unsigned NOT NULL,
     * `multiple_choice_question_answer_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `order` int(10) unsigned NOT NULL,
     * PRIMARY KEY (`multiple_choice_question_id`,`multiple_choice_question_answer_id`),
     * KEY `fk_multiple_choice_question_answer_links_multiple_choice_qu_idx1` (`multiple_choice_question_id`),
     * CONSTRAINT `fk_multiple_choice_question_answer_links_multiple_choice_ques2` FOREIGN KEY (`multiple_choice_question_id`) REFERENCES `multiple_choice_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createMultipleChoiceQuestionAnswerLinks()
    {
        Schema::create('multiple_choice_question_answer_links', function (Blueprint $table) {
            $table->integer('multiple_choice_question_id')->unsigned();
            $table->integer('multiple_choice_question_answer_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('order')->unsigned()->nullable();
            $table->primary(['multiple_choice_question_id', 'multiple_choice_question_answer_id'])->name('primMCQAL');
//            $table->foreign('multiple_choice_question_id')->references('id')->on('multiple_choice_questions');
        });
    }

    /**
     * CREATE TABLE `multiple_choice_question_answers` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `answer` text COLLATE utf8_unicode_ci,
     * `score` int(11) DEFAULT NULL,
     * PRIMARY KEY (`id`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=123612 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createMultipleChoiceQuestionAnswers()
    {
        Schema::create('multiple_choice_question_answers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->text('answer');
            $table->float('score')->nullable();
        });
    }

    /**
     * CREATE TABLE `multiple_choice_questions` (
     * `id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `subtype` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
     * `selectable_answers` int(10) unsigned NOT NULL DEFAULT '1',
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `multiple_choice_questions_uuid_unique` (`uuid`),
     * KEY `fk_multiple_choice_questions_questions1_idx` (`id`),
     * CONSTRAINT `fk_multiple_choice_questions_questions1` FOREIGN KEY (`id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createMultipleChoiceQuestions()
    {
        Schema::create('multiple_choice_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('subtype', 45)->default('');
            $table->integer('selectable_answers')->unsigned()->default('1');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
//            $table->foreign('id')->references('id')->on('questions');
        });
    }

//
//# Dump of table onboarding_wizard_reports
//# ------------------------------------------------------------
//
//CREATE TABLE `onboarding_wizard_reports` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`user_id` int(11) default NULL,
//`user_email` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`user_name_first` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`user_name_suffix` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`user_name` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`user_created_at` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`user_last_login` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`school_location_name` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`school_location_customer_code` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`test_items_created_amount` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`tests_created_amount` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`first_test_planned_date` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`last_test_planned_date` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`first_test_created_date` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`last_test_created_date` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`first_test_taken_date` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`last_test_taken_date` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`tests_taken_amount` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`first_test_discussed_date` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`last_test_discussed_date` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`tests_discussed_amount` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`first_test_checked_date` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`last_test_checked_date` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`tests_checked_amount` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`first_test_rated_date` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`last_test_rated_date` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`tests_rated_amount` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`finished_demo_tour` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`finished_demo_steps_percentage` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`finished_demo_substeps_percentage` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`current_demo_tour_step` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`current_demo_tour_step_since_date` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`current_demo_tour_step_since_hours` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`average_time_finished_demo_tour_steps_hours` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`user_sections` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`user_login_amount` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`last_updated_from_TC` datetime NOT NULL,
//`invited_by` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`invited_users_amount` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`invited_users` text COLLATE utf8_unicode_ci,
//`account_verified` datetime default NULL,
//`nr_uploaded_test_files_7` int(11) default NULL,
//`nr_uploaded_test_files_30` int(11) default NULL,
//`nr_uploaded_test_files_60` int(11) default NULL,
//`nr_uploaded_test_files_90` int(11) default NULL,
//`nr_uploaded_test_files_365` int(11) NOT NULL default '0',
//`nr_uploaded_test_files_total` int(11) default NULL,
//`nr_added_question_items_7` int(11) default NULL,
//`nr_added_question_items_30` int(11) default NULL,
//`nr_added_question_items_60` int(11) default NULL,
//`nr_added_question_items_90` int(11) default NULL,
//`nr_added_question_items_365` int(11) NOT NULL default '0',
//`nr_added_question_items_total` int(11) default NULL,
//`nr_uploaded_classes_7` int(11) NOT NULL default '0',
//`nr_uploaded_classes_30` int(11) NOT NULL default '0',
//`nr_uploaded_classes_60` int(11) NOT NULL default '0',
//`nr_uploaded_classes_90` int(11) NOT NULL default '0',
//`nr_uploaded_classes_365` int(11) NOT NULL default '0',
//`nr_uploaded_classes_total` int(11) NOT NULL default '0',
//`nr_tests_taken_7` int(11) default NULL,
//`nr_tests_taken_30` int(11) default NULL,
//`nr_tests_taken_60` int(11) default NULL,
//`nr_tests_taken_90` int(11) default NULL,
//`nr_tests_taken_365` int(11) NOT NULL default '0',
//`nr_test_taken_total` int(11) default NULL,
//`nr_tests_rated_7` int(11) default NULL,
//`nr_tests_rated_30` int(11) default NULL,
//`nr_tests_rated_60` int(11) default NULL,
//`nr_tests_rated_90` int(11) default NULL,
//`nr_tests_rated_365` int(11) NOT NULL default '0',
//`nr_tests_rated_total` int(11) default NULL,
//`nr_colearning_sessions_7` int(11) default NULL,
//`nr_colearning_sessions_30` int(11) default NULL,
//`nr_colearning_sessions_60` int(11) default NULL,
//`nr_colearning_sessions_90` int(11) default NULL,
//`nr_colearning_sessions_365` int(11) NOT NULL default '0',
//`nr_colearning_sessions_total` int(11) default NULL,
//`accepted_general_terms` datetime default NULL,
//`trial_period_end` datetime default NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function onboarding_wizard_reports()
    {
        Schema::create('onboarding_wizard_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_name_first')->nullable();
            $table->string('user_name_suffix')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_created_at')->nullable();
            $table->string('user_last_login')->nullable();
            $table->string('school_location_name')->nullable();
            $table->string('school_location_customer_code')->nullable();
            $table->string('test_items_created_amount')->nullable();
            $table->string('tests_created_amount')->nullable();
            $table->string('first_test_planned_date')->nullable();
            $table->string('last_test_planned_date')->nullable();
            $table->string('first_test_created_date')->nullable();
            $table->string('last_test_created_date')->nullable();
            $table->string('first_test_taken_date')->nullable();
            $table->string('last_test_taken_date')->nullable();
            $table->string('tests_taken_amount')->nullable();
            $table->string('first_test_discussed_date')->nullable();
            $table->string('last_test_discussed_date')->nullable();
            $table->string('tests_discussed_amount')->nullable();
            $table->string('first_test_checked_date')->nullable();
            $table->string('last_test_checked_date')->nullable();
            $table->string('tests_checked_amount')->nullable();
            $table->string('first_test_rated_date')->nullable();
            $table->string('last_test_rated_date')->nullable();
            $table->string('tests_rated_amount')->nullable();
            $table->string('finished_demo_tour')->nullable();
            $table->string('finished_demo_steps_percentage')->nullable();
            $table->string('finished_demo_substeps_percentage')->nullable();
            $table->string('current_demo_tour_step')->nullable();
            $table->string('current_demo_tour_step_since_date')->nullable();
            $table->string('current_demo_tour_step_since_hours')->nullable();
            $table->string('average_time_finished_demo_tour_steps_hours')->nullable();
            $table->string('user_sections')->nullable();
            $table->string('user_login_amount')->nullable();
            $table->timestamps();
            $table->softDeletes();;
            $table->dateTime('trial_period_end')->nullable();
            $table->dateTime('last_updated_from_TC');
            $table->text('invited_users')->nullable()->change();
            $table->dateTime('accepted_general_terms')->nullable();
            $table->string('invited_by')->nullable();
            $table->string('invited_users_amount')->nullable();
            $table->text('invited_users')->nullable();
            $table->dateTime('account_verified')->nullable();
            $table->integer('nr_uploaded_test_files_7');
            $table->integer('nr_uploaded_test_files_30');
            $table->integer('nr_uploaded_test_files_60');
            $table->integer('nr_uploaded_test_files_90');
            $table->integer('nr_uploaded_test_files_total');
            $table->integer('nr_added_question_items_7');
            $table->integer('nr_added_question_items_30');
            $table->integer('nr_added_question_items_60');
            $table->integer('nr_added_question_items_90');
            $table->integer('nr_added_question_items_total');
            $table->integer('nr_tests_taken_7');
            $table->integer('nr_tests_taken_30');
            $table->integer('nr_tests_taken_60');
            $table->integer('nr_tests_taken_90');
            $table->integer('nr_test_taken_total');
            $table->integer('nr_tests_rated_7');
            $table->integer('nr_tests_rated_30');
            $table->integer('nr_tests_rated_60');
            $table->integer('nr_tests_rated_90');
            $table->integer('nr_tests_rated_total');
            $table->integer('nr_colearning_sessions_7');
            $table->integer('nr_colearning_sessions_30');
            $table->integer('nr_colearning_sessions_60');
            $table->integer('nr_colearning_sessions_90');
            $table->integer('nr_colearning_sessions_total');
            $table->integer('nr_uploaded_test_files_365')->default(0);
            $table->integer('nr_added_question_items_365')->default(0);
            $table->integer('nr_uploaded_classes_total')->default(0);
            $table->integer('nr_uploaded_classes_365')->default(0);
            $table->integer('nr_uploaded_classes_90')->default(0);
            $table->integer('nr_uploaded_classes_60')->default(0);
            $table->integer('nr_uploaded_classes_30')->default(0);
            $table->integer('nr_uploaded_classes_7')->default(0);
            $table->integer('nr_tests_taken_365')->default(0);
            $table->integer('nr_tests_rated_365')->default(0);
            $table->integer('nr_colearning_sessions_365')->default(0);
        });
    }
//
//
//# Dump of table onboarding_wizard_steps
//# ------------------------------------------------------------
//
//CREATE TABLE `onboarding_wizard_steps` (
//`id` char(36) COLLATE utf8_unicode_ci NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`onboarding_wizard_id` char(36) COLLATE utf8_unicode_ci NOT NULL,
//`parent_id` char(255) COLLATE utf8_unicode_ci default NULL,
//`title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`action` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`action_content` text COLLATE utf8_unicode_ci,
//`knowledge_base_action` text COLLATE utf8_unicode_ci,
//`confetti_max_count` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`confetti_time_out` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`displayorder` int(11) NOT NULL default '1',
//`uuid` binary(16) default NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `onboarding_wizard_steps_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createOnboardingWizardSteps()
    {
        Schema::create('onboarding_wizard_steps', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->timestamps();
            $table->softDeletes();
            $table->char('onboarding_wizard_id', 36);
            $table->char('parent_id')->nullable();
            $table->string('title');
            $table->string('action')->nullable();
            $table->text('action_content')->nullable();
            $table->text('knowledge_base_action')->nullable();
            $table->string('confetti_max_count')->nullable();
            $table->string('confetti_time_out')->nullable();
            $table->integer('displayorder')->default(1);
            $table->efficientUuid('uuid')->index()->unique()->nullable();
        });
    }
//
//
//# Dump of table onboarding_wizard_user_states
//# ------------------------------------------------------------
//
//CREATE TABLE `onboarding_wizard_user_states` (
//`id` char(255) COLLATE utf8_unicode_ci NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`user_id` bigint(20) NOT NULL,
//`onboarding_wizard_id` char(36) COLLATE utf8_unicode_ci NOT NULL,
//`show` tinyint(1) NOT NULL default '1',
//`active_step` int(11) NOT NULL default '0',
//PRIMARY KEY (`id`),
//KEY `onboarding_wizard_user_states_user_id_index` (`user_id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createOnboardingWizardUserStates()
    {
        Schema::create('onboarding_wizard_user_states', function (Blueprint $table) {
            $table->char('id')->primary();
            $table->timestamps();
            $table->softDeletes();
            $table->biginteger('user_id')->index();
            $table->char('onboarding_wizard_id', 36);
            $table->boolean('show')->default(true);
            $table->integer('active_step')->default(0);
        });
    }
//
//
//# Dump of table onboarding_wizard_user_steps
//# ------------------------------------------------------------
//
//CREATE TABLE `onboarding_wizard_user_steps` (
//`id` char(36) COLLATE utf8_unicode_ci NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`onboarding_wizard_step_id` char(36) COLLATE utf8_unicode_ci NOT NULL,
//`user_id` bigint(20) NOT NULL,
//PRIMARY KEY (`id`),
//KEY `onboarding_wizard_user_steps_user_id_index` (`user_id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createOnboardingWizardUserSteps()
    {
        Schema::create('onboarding_wizard_user_steps', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->timestamps();
            $table->softDeletes();
            $table->char('onboarding_wizard_step_id', 36);
            $table->biginteger('user_id')->index();
        });
    }


//
//
//# Dump of table onboarding_wizards
//# ------------------------------------------------------------
//
//CREATE TABLE `onboarding_wizards` (
//`id` char(36) COLLATE utf8_unicode_ci NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`role_id` int(11) NOT NULL,
//`active` tinyint(1) NOT NULL,
//`uuid` binary(16) default NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `onboarding_wizards_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function onboarding_wizards()
    {
        Schema::create('onboarding_wizards', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->timestamps();
            $table->softDeletes();
            $table->string('title');
            $table->integer('role_id');
            $table->boolean('active');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
        });
    }

    /**
     * CREATE TABLE `open_questions` (
     * `id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `subtype` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
     * `answer` text COLLATE utf8_unicode_ci,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `open_questions_uuid_unique` (`uuid`),
     * KEY `fk_open_questions_questions1_idx` (`id`),
     * CONSTRAINT `fk_open_questions_questions1` FOREIGN KEY (`id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createOpenQuestions()
    {
        Schema::create('open_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('subtype', 45)->nullable();
            $table->text('answer');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->boolean('spell_check_available')->nullable();
            $table->boolean('text_formatting')->nullable();
            $table->boolean('mathml_functions')->nullable();
            $table->boolean('restrict_word_amount')->nullable();
            $table->integer('max_words')->nullable();
//            $table->foreign('id')->references('id')->on('questions');
        });
    }

    /**
     * CREATE TABLE `p_value_attainments` (
     * `p_value_id` int(10) unsigned NOT NULL,
     * `attainment_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * PRIMARY KEY (`p_value_id`,`attainment_id`),
     * KEY `fk_p_value_attainments_p_values1_idx` (`p_value_id`),
     * KEY `fk_p_value_attainments_attainments1_idx` (`attainment_id`),
     * CONSTRAINT `fk_p_value_attainments_attainments1` FOREIGN KEY (`attainment_id`) REFERENCES `attainments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_p_value_attainments_p_values1` FOREIGN KEY (`p_value_id`) REFERENCES `p_values` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createPValueAttainments()
    {
        Schema::create('p_value_attainments', function (Blueprint $table) {
            $table->integer('p_value_id')->unsigned();
            $table->integer('attainment_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->primary(['p_value_id', 'attainment_id'])->name('prim_pva');
//            $table->foreign('attainment_id')->references('id')->on('attainments');
//            $table->foreign('p_value_id')->references('id')->on('p_values');
        });
    }

    /**
     * CREATE TABLE `p_value_users` (
     * `p_value_id` int(10) unsigned NOT NULL,
     * `user_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * PRIMARY KEY (`p_value_id`,`user_id`),
     * KEY `fk_p_value_users_p_values1_idx` (`p_value_id`),
     * KEY `fk_p_value_users_users1_idx` (`user_id`),
     * CONSTRAINT `fk_p_value_users_p_values1` FOREIGN KEY (`p_value_id`) REFERENCES `p_values` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_p_value_users_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createPValueUsers()
    {
        Schema::create('p_value_users', function (Blueprint $table) {
            $table->integer('p_value_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->primary(['p_value_id', 'user_id']);
//            $table->foreign('p_value_id')->references('id')->on('p_values');
//            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * CREATE TABLE `p_values` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `score` decimal(11,1) unsigned NOT NULL,
     * `max_score` decimal(11,1) unsigned NOT NULL,
     * `answer_id` int(10) unsigned NOT NULL,
     * `test_participant_id` int(10) unsigned NOT NULL,
     * `question_id` int(10) unsigned NOT NULL,
     * `period_id` int(10) unsigned NOT NULL,
     * `school_class_id` int(10) unsigned NOT NULL,
     * `education_level_id` int(10) unsigned NOT NULL,
     * `education_level_year` int(10) unsigned NOT NULL,
     * `subject_id` int(10) unsigned NOT NULL,
     * PRIMARY KEY (`id`),
     * KEY `fk_p_values_answers1_idx` (`answer_id`),
     * KEY `fk_p_values_test_participants1_idx` (`test_participant_id`),
     * KEY `fk_p_values_questions1_idx` (`question_id`),
     * KEY `fk_p_values_periods1_idx` (`period_id`),
     * KEY `fk_p_values_school_classes1_idx` (`school_class_id`),
     * KEY `fk_p_values_education_levels1_idx` (`education_level_id`),
     * KEY `fk_p_values_subjects1_idx` (`subject_id`),
     * CONSTRAINT `fk_p_values_answers1` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_p_values_education_levels1` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_p_values_periods1` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_p_values_questions1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_p_values_school_classes1` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_p_values_subjects1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_p_values_test_participants1` FOREIGN KEY (`test_participant_id`) REFERENCES `test_participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=112395 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createPValues()
    {
        Schema::create('p_values', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->decimal('score', 11, 1);
            $table->decimal('max_score', 11, 1);
            $table->integer('answer_id')->unsigned();
            $table->integer('test_participant_id')->unsigned();
            $table->integer('question_id')->unsigned();
            $table->integer('period_id')->unsigned();
            $table->integer('school_class_id')->unsigned();
            $table->integer('education_level_id')->unsigned();
            $table->integer('education_level_year')->unsigned();
            $table->integer('subject_id')->unsigned();
//            $table->foreign('answer_id')->references('id')->on('answers');
//            $table->foreign('education_level_id')->references('id')->on('education_levels');
//            $table->foreign('period_id')->references('id')->on('periods');
//            $table->foreign('question_id')->references('id')->on('questions');
//            $table->foreign('school_class_id')->references('id')->on('school_classes');
//            $table->foreign('subject_id')->references('id')->on('subjects');
//            $table->foreign('test_participant_id')->references('id')->on('test_participants');
        });
    }

//
//
//# Dump of table password_resets
//# ------------------------------------------------------------
//
//CREATE TABLE `password_resets` (
//`email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`created_at` timestamp NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//PRIMARY KEY (`email`, `token`),
//KEY `password_resets_email_index` (`email`),
//KEY `password_resets_token_index` (`token`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
//
//# Dump of table question_groups
//# ------------------------------------------------------------
    private function password_resets()
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token')->index();
            $table->timestamp('created_at');
        });
    }

    /**
     * CREATE TABLE `periods` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     * `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `school_year_id` int(10) unsigned NOT NULL,
     * `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `start_date` date DEFAULT NULL,
     * `end_date` date DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `periods_uuid_unique` (`uuid`),
     * KEY `fk_periods_school_years1_idx` (`school_year_id`),
     * CONSTRAINT `fk_periods_school_years1` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=520 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createPeriods()
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('school_year_id')->unsigned();
            $table->string('name', 45)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
//            $table->foreign('school_year_id')->references('id')->on('school_years');
        });
    }


    /**
     * CREATE TABLE `question_attachments` (
     * `question_id` int(10) unsigned NOT NULL,
     * `attachment_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * PRIMARY KEY (`question_id`,`attachment_id`),
     * KEY `fk_question_attachments_questions1_idx` (`question_id`),
     * KEY `fk_question_attachments_attachments1_idx` (`attachment_id`),
     * CONSTRAINT `fk_question_attachments_attachments1` FOREIGN KEY (`attachment_id`) REFERENCES `attachments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_question_attachments_questions1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createQuestionAttachments()
    {
        Schema::create('question_attachments', function (Blueprint $table) {
            $table->integer('question_id')->unsigned();
            $table->integer('attachment_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
            $table->text('options')->nullable();
            $table->primary(['question_id', 'attachment_id'])->name('prim_q_a');
//            $table->foreign('attachment_id')->references('id')->on('attachments');
//            $table->foreign('question_id')->references('id')->on('questions');
        });
    }

    /**
     * CREATE TABLE `question_attainments` (
     * `attainment_id` int(10) unsigned NOT NULL,
     * `question_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * PRIMARY KEY (`attainment_id`,`question_id`),
     * KEY `fk_question_attainments_attainments1_idx` (`attainment_id`),
     * KEY `fk_question_attainments_questions1_idx` (`question_id`),
     * CONSTRAINT `fk_question_attainments_attainments1` FOREIGN KEY (`attainment_id`) REFERENCES `attainments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_question_attainments_questions1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createQuestionAttainments()
    {
        Schema::create('question_attainments', function (Blueprint $table) {
            $table->integer('attainment_id')->unsigned();
            $table->integer('question_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->primary(['attainment_id', 'question_id'])->name('prim_qAttain');
//            $table->foreign('attainment_id')->references('id')->on('attainments');
//            $table->foreign('question_id')->references('id')->on('questions');
        });
    }

    /**
     * CREATE TABLE `question_authors` (
     * `user_id` int(10) unsigned NOT NULL,
     * `question_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * PRIMARY KEY (`user_id`,`question_id`),
     * KEY `fk_question_authors_users1_idx` (`user_id`),
     * KEY `fk_question_authors_questions1_idx` (`question_id`),
     * CONSTRAINT `fk_question_authors_questions1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_question_authors_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createQuestionAuthors()
    {
        Schema::create('question_authors', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('question_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->primary(['user_id', 'question_id']);
//            $table->foreign('question_id')->references('id')->on('questions');
//            $table->foreign('user_id')->references('id')->on('users');
        });
    }


    /**
     * CREATE TABLE `questions` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     * `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `subject_id` int(10) unsigned NOT NULL,
     * `education_level_id` int(10) unsigned NOT NULL,
     * `type` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `question` longtext COLLATE utf8_unicode_ci,
     * `education_level_year` int(10) unsigned NOT NULL,
     * `score` int(10) unsigned DEFAULT NULL,
     * `decimal_score` tinyint(1) DEFAULT NULL,
     * `note_type` enum('NONE','TEXT','DRAWING') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'NONE',
     * `rtti` enum('R','T1','T2','I') COLLATE utf8_unicode_ci DEFAULT NULL,
     * `bloom` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `miller` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `add_to_database` tinyint(1) NOT NULL,
     * `is_subquestion` tinyint(1) NOT NULL,
     * `derived_question_id` int(10) unsigned DEFAULT NULL,
     * `is_open_source_content` tinyint(1) DEFAULT NULL,
     * `metadata` text COLLATE utf8_unicode_ci,
     * `external_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `styling` longtext COLLATE utf8_unicode_ci,
     * `uuid` binary(16) DEFAULT NULL,
     * `closeable` tinyint(1) NOT NULL DEFAULT '0',
     * `html_specialchars_encoded` tinyint(1) NOT NULL DEFAULT '1',
     * `all_or_nothing` tinyint(1) NOT NULL DEFAULT '0',
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `questions_uuid_unique` (`uuid`),
     * KEY `fk_questions_subjects1_idx` (`subject_id`),
     * KEY `fk_questions_education_levels1_idx` (`education_level_id`),
     * KEY `fk_questions_questions1_idx` (`derived_question_id`),
     * KEY `bloom` (`bloom`),
     * KEY `miller` (`miller`),
     * KEY `questions_scope_index` (`scope`),
     * KEY `questions_created_at_index` (`created_at`),
     * CONSTRAINT `fk_questions_education_levels1` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_questions_questions1` FOREIGN KEY (`derived_question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_questions_subjects1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=109588 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createQuestions()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('subject_id')->unsigned();
            $table->integer('education_level_id')->unsigned();
            $table->string('type', 45)->nullable();
            $table->longText('question');
            $table->integer('education_level_year')->unsigned();
            $table->float('score')->nullable();
            $table->integer('owner_id')->unsigned()->nullable();
            $table->tinyInteger('decimal_score')->nullable();
            $table->enum('note_type', ['NONE', 'TEXT', 'DRAWING'])->default('NONE');
            $table->enum('rtti', ['R', 'T1', 'T2', 'I'])->nullable();
            $table->string('bloom')->nullable();
            $table->string('miller')->nullable();
            $table->tinyInteger('add_to_database');
            $table->tinyInteger('add_to_database_disabled')->default(0);
            $table->tinyInteger('is_subquestion')->default(0);
            $table->integer('derived_question_id')->unsigned()->nullable();
            $table->tinyInteger('is_open_source_content')->nullable();
            $table->text('metadata')->nullable();
            $table->string('external_id')->nullable();
            $table->string('scope')->nullable();
            $table->longText('styling')->nullable();
            $table->boolean('fix_order')->default(false);
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->tinyInteger('closeable')->default('0');
            $table->tinyInteger('html_specialchars_encoded')->default('1');
            $table->tinyInteger('all_or_nothing')->default('0');
            $table->boolean('draft')->default(false);
//            $table->foreign('education_level_id')->references('id')->on('education_levels');
//            $table->foreign('derived_question_id')->references('id')->on('questions');
//            $table->foreign('subject_id')->references('id')->on('subjects');
        });
    }


    /**
     * CREATE TABLE `ranking_question_answer_links` (
     * `ranking_question_id` int(10) unsigned NOT NULL,
     * `ranking_question_answer_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `order` int(10) unsigned NOT NULL,
     * `correct_order` int(10) unsigned NOT NULL,
     * PRIMARY KEY (`ranking_question_id`,`ranking_question_answer_id`),
     * KEY `fk_ranking_question_answer_links_ranking_questions1_idx` (`ranking_question_id`),
     * KEY `fk_ranking_question_answer_links_ranking_question_answers1_idx` (`ranking_question_answer_id`),
     * CONSTRAINT `fk_ranking_question_answer_links_ranking_question_answers1` FOREIGN KEY (`ranking_question_answer_id`) REFERENCES `ranking_question_answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_ranking_question_answer_links_ranking_questions1` FOREIGN KEY (`ranking_question_id`) REFERENCES `ranking_questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createRankingQuestionAnswerLinks()
    {
        Schema::create('ranking_question_answer_links', function (Blueprint $table) {
            $table->integer('ranking_question_id')->unsigned();
            $table->integer('ranking_question_answer_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('order')->unsigned();
            $table->integer('correct_order')->unsigned()->nullable();
            $table->primary(['ranking_question_id', 'ranking_question_answer_id'])->name('prim_ra_qu_ra_qu_id');
//            $table->foreign('ranking_question_answer_id')->references('id')->on('ranking_question_answers');
//            $table->foreign('ranking_question_id')->references('id')->on('ranking_questions');
        });
    }

    /**
     * CREATE TABLE `ranking_question_answers` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `answer` text COLLATE utf8_unicode_ci,
     * PRIMARY KEY (`id`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=7854 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createRankingQuestionAnswers()
    {
        Schema::create('ranking_question_answers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->text('answer');
        });
    }

    /**
     * CREATE TABLE `ranking_questions` (
     * `id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `random_order` tinyint(1) DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `ranking_questions_uuid_unique` (`uuid`),
     * KEY `fk_ranking_questions_questions1_idx` (`id`),
     * CONSTRAINT `fk_ranking_questions_questions1` FOREIGN KEY (`id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createRankingQuestions()
    {
        Schema::create('ranking_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->tinyInteger('random_order')->nullable();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
//            $table->foreign('id')->references('id')->on('questions');
        });
    }

    /**
     * CREATE TABLE `ratings` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `rating` decimal(8,4) unsigned NOT NULL,
     * `score` decimal(11,1) unsigned NOT NULL,
     * `max_score` decimal(11,1) unsigned NOT NULL,
     * `weight` int(10) unsigned NOT NULL,
     * `test_participant_id` int(10) unsigned NOT NULL,
     * `user_id` int(10) unsigned NOT NULL,
     * `period_id` int(10) unsigned NOT NULL,
     * `school_class_id` int(10) unsigned NOT NULL,
     * `education_level_id` int(10) unsigned NOT NULL,
     * `education_level_year` int(10) unsigned NOT NULL,
     * `subject_id` int(10) unsigned NOT NULL,
     * PRIMARY KEY (`id`),
     * KEY `fk_ratings_test_participants1_idx` (`test_participant_id`),
     * KEY `fk_ratings_users1_idx` (`user_id`),
     * KEY `fk_ratings_periods1_idx` (`period_id`),
     * KEY `fk_ratings_school_classes1_idx` (`school_class_id`),
     * KEY `fk_ratings_education_levels1_idx` (`education_level_id`),
     * KEY `fk_ratings_subjects1_idx` (`subject_id`),
     * CONSTRAINT `fk_ratings_education_levels1` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_ratings_periods1` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_ratings_school_classes1` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_ratings_subjects1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_ratings_test_participants1` FOREIGN KEY (`test_participant_id`) REFERENCES `test_participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_ratings_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=5987 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createRatings()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->decimal('rating', 8, 4);
            $table->decimal('score', 11, 1);
            $table->decimal('max_score', 11, 1);
            $table->integer('weight')->unsigned();
            $table->integer('test_participant_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('period_id')->unsigned();
            $table->integer('school_class_id')->unsigned();
            $table->integer('education_level_id')->unsigned();
            $table->integer('education_level_year')->unsigned();
            $table->integer('subject_id')->unsigned();
//            $table->foreign('education_level_id')->references('id')->on('education_levels');
//            $table->foreign('period_id')->references('id')->on('periods');
//            $table->foreign('school_class_id')->references('id')->on('school_classes');
//            $table->foreign('subject_id')->references('id')->on('subjects');
//            $table->foreign('test_participant_id')->references('id')->on('test_participants');
//            $table->foreign('user_id')->references('id')->on('users');
        });
    }


    /**
     * CREATE TABLE `roles` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
     * PRIMARY KEY (`id`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */

    private function createRoles()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('name', 45);
        });
    }

    /**
     * CREATE TABLE `sales_organizations` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `sales_organizations_uuid_unique` (`uuid`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createSalesOrganizations()
    {
        Schema::create('sales_organizations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('name', 45);
            $table->efficientUuid('uuid')->index()->unique()->nullable();
        });
    }
//
//
//# Dump of table saml_messages
//# ------------------------------------------------------------
//
//CREATE TABLE `saml_messages` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`uuid` binary(16) default NULL,
//`email` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`message_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`eck_id` text COLLATE utf8_unicode_ci NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`data` text COLLATE utf8_unicode_ci,
//PRIMARY KEY (`id`),
//UNIQUE KEY `saml_messages_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createSamlMessages()
    {
        Schema::create('saml_messages', function (Blueprint $table) {
            $table->id();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->string('email')->nullable();
            $table->string('message_id');
            $table->text('eck_id');
            $table->timestamps();
            $table->softDeletes();;
            $table->text('data')->nullable();
        });
    }

    /**
     * CREATE TABLE `school_addresses` (
     * `address_id` int(10) unsigned NOT NULL,
     * `school_id` int(10) unsigned NOT NULL,
     * `type` enum('MAIN','INVOICE','OTHER') COLLATE utf8_unicode_ci NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * PRIMARY KEY (`address_id`,`school_id`,`type`),
     * KEY `fk_school_addresses_addresses1_idx` (`address_id`),
     * KEY `fk_school_addresses_schools1_idx` (`school_id`),
     * CONSTRAINT `fk_school_addresses_addresses1` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_school_addresses_schools1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createSchoolAddresses()
    {
        Schema::create('school_addresses', function (Blueprint $table) {
            $table->integer('address_id')->unsigned();
            $table->integer('school_id')->unsigned();
            $table->enum('type', ['MAIN', 'INVOICE', 'OTHER']);
            $table->timestamps();
            $table->softDeletes();;
            $table->primary(['address_id', 'school_id', 'type']);
//            $table->foreign('address_id')->references('id')->on('addresses');
//            $table->foreign('school_id')->references('id')->on('schools');
        });
    }

//
//# Dump of table school_class_import_logs
//# ------------------------------------------------------------
//
//CREATE TABLE `school_class_import_logs` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`checked_by_teacher` datetime default NULL,
//`checked_by_teacher_id` int(11) default NULL,
//`checked_by_admin` datetime default NULL,
//`class_id` int(11) NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`finalized` datetime default NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createSchoolClassImportLogs()
    {
        Schema::create('school_class_import_logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('checked_by_teacher')->nullable();
            $table->dateTime('checked_by_admin')->nullable();
            $table->integer('class_id');
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('checked_by_teacher_id')->nullable();
        });
    }

    /**
     * CREATE TABLE `school_classes` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     * `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `school_location_id` int(10) unsigned NOT NULL,
     * `education_level_id` int(10) unsigned NOT NULL,
     * `school_year_id` int(10) unsigned NOT NULL,
     * `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
     * `education_level_year` int(10) unsigned DEFAULT NULL,
     * `is_main_school_class` tinyint(1) DEFAULT NULL,
     * `do_not_overwrite_from_interface` tinyint(1) DEFAULT '1',
     * `demo` tinyint(1) NOT NULL DEFAULT '0',
     * `uuid` binary(16) DEFAULT NULL,
     * `visible` tinyint(1) NOT NULL DEFAULT '1',
     * `created_by` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `guest_class` tinyint(1) NOT NULL DEFAULT '0',
     * `test_take_id` int(11) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `school_classes_uuid_unique` (`uuid`),
     * KEY `fk_classes_school_locations1_idx` (`school_location_id`),
     * KEY `fk_classes_education_levels1_idx` (`education_level_id`),
     * KEY `fk_classes_school_years1_idx` (`school_year_id`),
     * CONSTRAINT `fk_classes_education_levels1` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_classes_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_classes_school_years1` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=12167 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createSchoolClasses()
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('school_location_id')->unsigned();
            $table->integer('education_level_id')->unsigned();
            $table->integer('school_year_id')->unsigned();
            $table->string('name', 45);
            $table->integer('education_level_year')->unsigned()->nullable();
            $table->tinyInteger('is_main_school_class')->nullable();
            $table->tinyInteger('do_not_overwrite_from_interface')->default('1');
            $table->tinyInteger('demo')->default('0');
            $table->tinyInteger('visible')->default('1');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->string('created_by', 15)->nullable();
            $table->tinyInteger('guest_class')->default('0');
            $table->integer('test_take_id')->nullable();
//            $table->foreign('education_level_id')->references('id')->on('education_levels');
//            $table->foreign('school_location_id')->references('id')->on('school_locations');
//            $table->foreign('school_year_id')->references('id')->on('school_years');
        });
    }

    /**
     * CREATE TABLE `school_contacts` (
     * `school_id` int(10) unsigned NOT NULL,
     * `contact_id` int(10) unsigned NOT NULL,
     * `type` enum('FINANCE','TECHNICAL','IMPLEMENTATION','OTHER') COLLATE utf8_unicode_ci NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * PRIMARY KEY (`school_id`,`contact_id`,`type`),
     * KEY `fk_school_contacts_schools1_idx` (`school_id`),
     * KEY `fk_school_contacts_contacts1_idx` (`contact_id`),
     * CONSTRAINT `fk_school_contacts_contacts1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_school_contacts_schools1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createSchoolContacts()
    {
        Schema::create('school_contacts', function (Blueprint $table) {
            $table->integer('school_id')->unsigned();
            $table->integer('contact_id')->unsigned();
            $table->enum('type', ['FINANCE', 'TECHNICAL', 'IMPLEMENTATION', 'OTHER']);
            $table->timestamps();
            $table->softDeletes();;
            $table->primary(['school_id', 'contact_id', 'type']);
//            $table->foreign('contact_id')->references('id')->on('contacts');
//            $table->foreign('school_id')->references('id')->on('schools');
        });
    }

    /**
     * CREATE TABLE `school_location_addresses` (
     * `address_id` int(10) unsigned NOT NULL,
     * `school_location_id` int(10) unsigned NOT NULL,
     * `type` enum('MAIN','INVOICE','VISIT','OTHER') COLLATE utf8_unicode_ci NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`address_id`,`school_location_id`,`type`),
     * UNIQUE KEY `school_location_addresses_uuid_unique` (`uuid`),
     * KEY `fk_school_location_addresses_addresses1_idx` (`address_id`),
     * KEY `fk_school_location_addresses_school_locations1_idx` (`school_location_id`),
     * CONSTRAINT `fk_school_location_addresses_addresses1` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_school_location_addresses_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createSchoolLocationAddresses()
    {
        Schema::create('school_location_addresses', function (Blueprint $table) {
            $table->integer('address_id')->unsigned();
            $table->integer('school_location_id')->unsigned();
            $table->enum('type', ['MAIN', 'INVOICE', 'VISIT', 'OTHER']);
            $table->timestamps();
            $table->softDeletes();;
            $table->primary(['address_id', 'school_location_id', 'type'])->name('prim_school_location_add');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
//            $table->foreign('address_id')->references('id')->on('addresses');
//            $table->foreign('school_location_id')->references('id')->on('school_locations');
        });
    }

    /**
     * CREATE TABLE `school_location_contacts` (
     * `school_location_id` int(10) unsigned NOT NULL,
     * `contact_id` int(10) unsigned NOT NULL,
     * `type` enum('FINANCE','TECHNICAL','IMPLEMENTATION','OTHER') COLLATE utf8_unicode_ci NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     * `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`school_location_id`,`contact_id`,`type`),
     * UNIQUE KEY `school_location_contacts_uuid_unique` (`uuid`),
     * KEY `fk_school_location_contacts_school_locations1_idx` (`school_location_id`),
     * KEY `fk_school_location_contacts_contacts1_idx` (`contact_id`),
     * CONSTRAINT `fk_school_location_contacts_contacts1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_school_location_contacts_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createSchoolLocationContacts()
    {
        Schema::create('school_location_contacts', function (Blueprint $table) {
            $table->integer('school_location_id')->unsigned();
            $table->integer('contact_id')->unsigned();
            $table->enum('type', ['FINANCE', 'TECHNICAL', 'IMPLEMENTATION', 'OTHER']);
            $table->timestamps();
            $table->softDeletes();;
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->primary(['school_location_id', 'contact_id', 'type'])->name('prim_school_location_contacts');
//            $table->foreign('contact_id')->references('id')->on('contacts');
//            $table->foreign('school_location_id')->references('id')->on('school_locations');
        });
    }

    /**
     * CREATE TABLE `school_location_education_levels` (
     * `school_location_id` int(10) unsigned NOT NULL,
     * `education_level_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * PRIMARY KEY (`school_location_id`,`education_level_id`),
     * KEY `fk_school_location_education_levels_school_locations1_idx` (`school_location_id`),
     * KEY `fk_school_location_education_levels_education_levels1_idx` (`education_level_id`),
     * CONSTRAINT `fk_school_location_education_levels_education_levels1` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_school_location_education_levels_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createSchoolLocationEducationLevels()
    {
        Schema::create('school_location_education_levels', function (Blueprint $table) {
            $table->integer('school_location_id')->unsigned();
            $table->integer('education_level_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->primary(['school_location_id', 'education_level_id'])->name(
                'prim_school_location_education_levels'
            );
//            $table->foreign('education_level_id')->references('id')->on('education_levels');
//            $table->foreign('school_location_id')->references('id')->on('school_locations');
        });
    }

    /**
     * CREATE TABLE `school_location_ips` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     * `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `school_location_id` int(10) unsigned NOT NULL,
     * `ip` varbinary(16) NOT NULL,
     * `netmask` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `school_location_ips_uuid_unique` (`uuid`),
     * KEY `fk_school_location_ips_school_locations1_idx` (`school_location_id`),
     * CONSTRAINT `fk_school_location_ips_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createSchoolLocationIps()
    {
        Schema::create('school_location_ips', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('school_location_id')->unsigned();
            $table->binary('ip');
            $table->string('netmask', 50);
            $table->efficientUuid('uuid')->index()->unique()->nullable();
//            $table->foreign('school_location_id')->references('id')->on('school_locations');
        });
    }

//
//
//# Dump of table school_location_reports
//# ------------------------------------------------------------
//
//CREATE TABLE `school_location_reports` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`school_location_id` int(11) default NULL,
//`company_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL default '',
//`school_location_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`nr_licenses` int(11) default NULL,
//`nr_activated_licenses_30` int(11) NOT NULL,
//`nr_activated_licenses_60` int(11) NOT NULL,
//`nr_activated_licenses_90` int(11) NOT NULL default '0',
//`nr_activated_licenses_total` int(11) default NULL,
//`nr_uploaded_class_files_30` int(11) NOT NULL,
//`nr_uploaded_class_files_60` int(11) NOT NULL,
//`nr_uploaded_class_files_90` int(11) NOT NULL,
//`nr_uploaded_class_files_365` int(11) NOT NULL,
//`nr_uploaded_class_files_total` int(11) NOT NULL,
//`nr_uploaded_test_files_30` int(11) NOT NULL,
//`nr_uploaded_test_files_60` int(11) NOT NULL,
//`nr_uploaded_test_files_90` int(11) NOT NULL,
//`nr_uploaded_test_files_365` int(11) NOT NULL,
//`nr_uploaded_test_files_total` int(11) NOT NULL,
//`nr_added_question_items_7` int(11) default NULL,
//`nr_added_question_items_30` int(11) default NULL,
//`nr_added_question_items_60` int(11) default NULL,
//`nr_added_question_items_90` int(11) default NULL,
//`nr_added_question_items_365` int(11) NOT NULL default '0',
//`nr_added_question_items_total` int(11) default NULL,
//`nr_tests_taken_7` int(11) default NULL,
//`nr_tests_taken_30` int(11) default NULL,
//`nr_tests_taken_60` int(11) default NULL,
//`nr_tests_taken_90` int(11) default NULL,
//`nr_tests_taken_365` int(11) NOT NULL default '0',
//`nr_tests_taken_total` int(11) default NULL,
//`nr_tests_rated_7` int(11) default NULL,
//`nr_tests_rated_30` int(11) default NULL,
//`nr_tests_rated_60` int(11) default NULL,
//`nr_tests_rated_90` int(11) default NULL,
//`nr_tests_rated_365` int(11) NOT NULL default '0',
//`nr_tests_rated_total` int(11) default NULL,
//`nr_colearning_sessions_7` int(11) default NULL,
//`nr_colearning_sessions_30` int(11) default NULL,
//`nr_colearning_sessions_60` int(11) default NULL,
//`nr_colearning_sessions_90` int(11) default NULL,
//`nr_colearning_sessions_365` int(11) NOT NULL default '0',
//`nr_colearning_sessions_total` int(11) default NULL,
//`nr_unique_students_taken_test_total` int(11) NOT NULL default '0',
//`nr_unique_students_taken_test_7` int(11) NOT NULL default '0',
//`nr_unique_students_taken_test_30` int(11) NOT NULL default '0',
//`nr_unique_students_taken_test_60` int(11) NOT NULL default '0',
//`nr_unique_students_taken_test_90` int(11) NOT NULL default '0',
//`nr_unique_students_taken_test_365` int(11) NOT NULL default '0',
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`nr_participants_taken_test_7` int(11) NOT NULL,
//`nr_participants_taken_test_30` int(11) NOT NULL,
//`nr_participants_taken_test_60` int(11) NOT NULL,
//`nr_participants_taken_test_90` int(11) NOT NULL,
//`nr_participants_taken_test_365` int(11) NOT NULL,
//`nr_participants_taken_test_total` int(11) NOT NULL,
//`lvs_type` text COLLATE utf8_unicode_ci,
//`lvs_active` text COLLATE utf8_unicode_ci,
//`lvs_active_no_mail_allowed` text COLLATE utf8_unicode_ci,
//`sso_type` text COLLATE utf8_unicode_ci,
//`sso_active` text COLLATE utf8_unicode_ci,
//`allow_inbrowser_testing` text COLLATE utf8_unicode_ci,
//`intense` text COLLATE utf8_unicode_ci,
//`klantcode_schoollocatie` text COLLATE utf8_unicode_ci,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createSchoolLocationReports()
    {
        Schema::create('school_location_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('school_location_id')->nullable();
            $table->integer('nr_licenses')->nullable();
            $table->integer('total_activated_licenses')->nullable();
            $table->integer('nr_added_question_items_7')->nullable();
            $table->integer('nr_added_question_items_30')->nullable();
            $table->integer('nr_added_question_items_60')->nullable();
            $table->integer('nr_added_question_items_90')->nullable();
            $table->integer('total_added_question_items_files')->nullable();
            $table->integer('nr_tests_taken_7')->nullable();
            $table->integer('nr_tests_taken_30')->nullable();
            $table->integer('nr_tests_taken_60')->nullable();
            $table->integer('nr_tests_taken_90')->nullable();
            $table->integer('total_tests_taken')->nullable();
            $table->integer('nr_tests_rated_7')->nullable();
            $table->integer('nr_tests_rated_30')->nullable();
            $table->integer('nr_tests_rated_60')->nullable();
            $table->integer('nr_tests_rated_90')->nullable();
            $table->integer('total_tests_rated')->nullable();
            $table->integer('nr_colearning_sessions_7')->nullable();
            $table->integer('nr_colearning_sessions_30')->nullable();
            $table->integer('nr_colearning_sessions_60')->nullable();
            $table->integer('nr_colearning_sessions_90')->nullable();
            $table->integer('total_colearning_sessions')->nullable();
            $table->integer('in_browser_tests_allowed')->nullable();
            $table->integer('nr_active_teachers')->nullable();
            $table->timestamps();
            $table->softDeletes();;
            $table->text('lvs_type')->nullable();
            $table->text('lvs_active')->nullable();
            $table->text('lvs_active_no_mail_allowed')->nullable();
            $table->text('sso_type')->nullable();
            $table->text('sso_active')->nullable();
            $table->text('allow_inbrowser_testing')->nullable();
            $table->text('intense')->nullable();
            $table->text('klantcode_schoollocatie')->nullable();
            $table->string('school_location_name');
            $table->integer('nr_activated_licenses_90')->default(0);
            $table->integer('nr_added_question_items_365')->default(0);
            $table->integer('nr_tests_taken_365')->default(0);
            $table->integer('nr_tests_rated_365')->default(0);
            $table->integer('nr_colearning_sessions_365')->default(0);
            $table->integer('nr_unique_students_taken_test_365')->default(0);
            $table->integer('nr_unique_students_taken_test_90')->default(0);
            $table->integer('nr_unique_students_taken_test_60')->default(0);
            $table->integer('nr_unique_students_taken_test_30')->default(0);
            $table->integer('nr_unique_students_taken_test_7')->default(0);
            $table->integer('total_unique_students_taken_test')->default(0);
            $table->integer('nr_activated_licenses_60');
            $table->integer('nr_activated_licenses_30');
            $table->integer('total_uploaded_test_files');
            $table->integer('nr_uploaded_test_files_365');
            $table->integer('nr_uploaded_test_files_90');
            $table->integer('nr_uploaded_test_files_60');
            $table->integer('nr_uploaded_test_files_30');
            $table->integer('total_uploaded_class_files');
            $table->integer('nr_uploaded_class_files_365');
            $table->integer('nr_uploaded_class_files_90');
            $table->integer('nr_uploaded_class_files_60');
            $table->integer('nr_uploaded_class_files_30');
            $table->integer('nr_participants_taken_test_7');
            $table->integer('nr_participants_taken_test_30');
            $table->integer('nr_participants_taken_test_60');
            $table->integer('nr_participants_taken_test_90');
            $table->integer('nr_participants_taken_test_365');
            $table->integer('total_participants_taken_test');
        });
    }

    /**
     * CREATE TABLE `school_location_school_years` (
     * `school_location_id` int(10) unsigned NOT NULL,
     * `school_year_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`school_location_id`,`school_year_id`),
     * UNIQUE KEY `school_location_school_years_uuid_unique` (`uuid`),
     * KEY `fk_school_location_school_years_school_locations1_idx` (`school_location_id`),
     * KEY `fk_school_location_school_years_school_years1_idx` (`school_year_id`),
     * CONSTRAINT `fk_school_location_school_years_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_school_location_school_years_sections1` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createSchoolLocationSchoolYears()
    {
        Schema::create('school_location_school_years', function (Blueprint $table) {
            $table->integer('school_location_id')->unsigned();
            $table->integer('school_year_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->primary(['school_location_id', 'school_year_id'])->name('prim_school_location_school_years');
//            $table->foreign('school_location_id')->references('id')->on('school_locations');
//            $table->foreign('school_year_id')->references('id')->on('school_years');
        });
    }

    /**
     * CREATE TABLE `school_location_sections` (
     * `school_location_id` int(10) unsigned NOT NULL,
     * `section_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `demo` tinyint(1) NOT NULL DEFAULT '0',
     * PRIMARY KEY (`school_location_id`,`section_id`),
     * KEY `fk_school_location_sections_school_locations1_idx` (`school_location_id`),
     * KEY `fk_school_location_sections_sections1_idx` (`section_id`),
     * CONSTRAINT `fk_school_location_sections_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_school_location_sections_sections1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createSchoolLocationSections()
    {
        Schema::create('school_location_sections', function (Blueprint $table) {
            $table->integer('school_location_id')->unsigned();
            $table->integer('section_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->tinyInteger('demo')->default('0');
            $table->primary(['school_location_id', 'section_id']);
//            $table->foreign('school_location_id')->references('id')->on('school_locations');
//            $table->foreign('section_id')->references('id')->on('sections');
        });
    }
//
//# Dump of table school_location_shared_sections
//# ------------------------------------------------------------
//
//CREATE TABLE `school_location_shared_sections` (
//`school_location_id` bigint(20) NOT NULL,
//`section_id` bigint(20) NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//PRIMARY KEY (`school_location_id`, `section_id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createSchoolLocationSharedSections()
    {
        Schema::create('school_location_shared_sections', function (Blueprint $table) {
            $table->biginteger('school_location_id');
            $table->biginteger('section_id');
            $table->timestamps();
            $table->softDeletes();;
            $table->primary(['school_location_id', 'section_id'], 'school_location_school_shared_section_key');
        });
    }

//
//
//# Dump of table school_location_user
//# ------------------------------------------------------------
//
//CREATE TABLE `school_location_user` (
//`school_location_id` bigint(20) NOT NULL,
//`user_id` bigint(20) NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`external_id` varchar(45) COLLATE utf8_unicode_ci default NULL,
//PRIMARY KEY (`school_location_id`, `user_id`),
//KEY `school_location_user_school_location_id_user_id_index` (`school_location_id`, `user_id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createSchoolLocationUser()
    {
        Schema::create('school_location_user', function (Blueprint $table) {
            $table->bigInteger('school_location_id');
            $table->bigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('external_id')->nullable();
            $table->primary(['school_location_id', 'user_id']);
        });
    }

    /**
     * CREATE TABLE `school_locations` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     * `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `user_id` int(10) unsigned NOT NULL,
     * `number_of_teachers` int(10) unsigned NOT NULL,
     * `number_of_students` int(10) unsigned NOT NULL,
     * `school_id` int(10) unsigned DEFAULT NULL,
     * `grading_scale_id` int(10) unsigned NOT NULL,
     * `customer_code` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
     * `main_address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `main_postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
     * `main_city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `main_country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `invoice_address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `invoice_postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
     * `invoice_city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `invoice_country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `visit_address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `visit_postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
     * `visit_city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `visit_country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `count_active_licenses` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_active_teachers` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_expired_licenses` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_licenses` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_questions` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_students` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_teachers` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_tests` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_tests_taken` int(10) unsigned NOT NULL DEFAULT '0',
     * `activated` tinyint(1) NOT NULL DEFAULT '0',
     * `is_rtti_school_location` tinyint(1) DEFAULT '0',
     * `external_main_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `external_sub_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `is_open_source_content_creator` tinyint(1) DEFAULT '0',
     * `is_allowed_to_view_open_source_content` tinyint(1) DEFAULT '0',
     * `count_text2speech` int(11) NOT NULL DEFAULT '0',
     * `edu_ix_organisation_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * `allow_inbrowser_testing` tinyint(4) NOT NULL DEFAULT '1',
     * `allow_new_player_access` tinyint(1) NOT NULL DEFAULT '2',
     * `intense` tinyint(1) NOT NULL DEFAULT '0',
     * `school_language` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'nl',
     * `lvs_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `lvs_active` tinyint(1) NOT NULL DEFAULT '0',
     * `sso` tinyint(1) NOT NULL DEFAULT '0',
     * `sso_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `sso_active` tinyint(1) NOT NULL DEFAULT '0',
     * `lvs_authorization_key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `lvs_client_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `lvs_client_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `lvs_active_no_mail_allowed` tinyint(1) NOT NULL DEFAULT '0',
     * `accepted_mail_domain` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `no_mail_request_detected` datetime DEFAULT NULL,
     * `allow_guest_accounts` tinyint(1) NOT NULL DEFAULT '1',
     * `company_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
     * `allow_new_student_environment` tinyint(1) NOT NULL DEFAULT '1',
     * `allow_new_question_editor` tinyint(1) NOT NULL DEFAULT '0',
     * `allow_new_drawing_question` tinyint(1) NOT NULL DEFAULT '0',
     * `keep_out_of_school_location_report` tinyint(1) NOT NULL DEFAULT '0',
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `school_locations_uuid_unique` (`uuid`),
     * KEY `fk_school_location_school_idx` (`school_id`),
     * KEY `fk_school_locations_users1_idx` (`user_id`),
     * KEY `fk_school_locations_grading_scales1_idx` (`grading_scale_id`),
     * CONSTRAINT `fk_school_location_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_school_locations_grading_scales1` FOREIGN KEY (`grading_scale_id`) REFERENCES `grading_scales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_school_locations_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=216 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createSchoolLocations()
    {
        Schema::create('school_locations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('user_id')->unsigned();
            $table->integer('number_of_teachers')->unsigned();
            $table->integer('number_of_students')->unsigned();
            $table->integer('school_id')->unsigned()->nullable();
            $table->integer('grading_scale_id')->unsigned();
            $table->string('customer_code', 60)->nullable();
            $table->string('name', 100);
            $table->string('main_address', 60);
            $table->string('main_postal', 7);
            $table->string('main_city', 60);
            $table->string('main_country', 60);
            $table->string('invoice_address', 60);
            $table->string('invoice_postal', 7);
            $table->string('invoice_city', 60);
            $table->string('invoice_country', 60);
            $table->string('visit_address', 60);
            $table->string('visit_postal', 7);
            $table->string('visit_city', 60);
            $table->string('visit_country', 60);
            $table->integer('count_active_licenses')->unsigned()->default('0');
            $table->integer('count_active_teachers')->unsigned()->default('0');
            $table->integer('count_expired_licenses')->unsigned()->default('0');
            $table->integer('count_licenses')->unsigned()->default('0');
            $table->integer('count_questions')->unsigned()->default('0');
            $table->integer('count_students')->unsigned()->default('0');
            $table->integer('count_teachers')->unsigned()->default('0');
            $table->integer('count_tests')->unsigned()->default('0');
            $table->integer('count_tests_taken')->unsigned()->default('0');
            $table->tinyInteger('activated')->default('0');
            $table->tinyInteger('is_rtti_school_location')->default('0');
            $table->string('external_main_code', 50)->nullable();
            $table->string('external_sub_code', 50)->nullable();
            $table->tinyInteger('is_open_source_content_creator')->default('0');
            $table->tinyInteger('is_allowed_to_view_open_source_content')->default('0');
            $table->integer('count_text2speech')->default('0');
            $table->string('edu_ix_organisation_id')->nullable();
            $table->tinyInteger('allow_inbrowser_testing')->default('1');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->tinyInteger('allow_new_player_access')->default('2');
            $table->tinyInteger('intense')->default('0');
            $table->string('school_language')->default('nl');
            $table->string('lvs_type')->nullable();
            $table->tinyInteger('lvs_active')->default('0');
            $table->tinyInteger('sso')->default('0');
            $table->string('sso_type')->nullable();
            $table->tinyInteger('sso_active')->default('0');
            $table->string('lvs_authorization_key')->nullable();
            $table->string('lvs_client_name')->nullable();
            $table->string('lvs_client_code')->nullable();
            $table->tinyInteger('lvs_active_no_mail_allowed')->default('0');
            $table->string('accepted_mail_domain')->nullable();
            $table->datetime('no_mail_request_detected')->nullable();
            $table->tinyInteger('allow_guest_accounts')->default('1');
            $table->string('company_id')->default('');
            $table->tinyInteger('allow_new_student_environment')->default('1');
            $table->tinyInteger('allow_new_question_editor')->default('0');
            $table->tinyInteger('allow_new_drawing_question')->default('0');
            $table->boolean('allow_new_test_bank')->default(true);
            $table->boolean('allow_wsc')->default(true);
            $table->tinyInteger('keep_out_of_school_location_report')->default('0');
            $table->boolean('show_national_item_bank')->default(false);
            $table->boolean('allow_writing_assignment')->default(0);
            $table->enum('license_type', ['TRIAL', 'CLIENT'])->default('TRIAL');
            $table->boolean('auto_uwlr_import')->default(0);
            $table->timestamp('auto_uwlr_last_import')->nullable();
            $table->string('auto_uwlr_import_status')->nullable();
//            $table->foreign('school_id')->references('id')->on('schools');
//            $table->foreign('grading_scale_id')->references('id')->on('grading_scales');
//            $table->foreign('user_id')->references('id')->on('users');
        });
    }
//
//
//# Dump of table school_years
//# ------------------------------------------------------------
//
//CREATE TABLE `school_years` (
//`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//`updated_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
//`deleted_at` timestamp NULL default NULL,
//`year` int(4) unsigned default NULL,
//`uuid` binary(16) default NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `school_years_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createSchoolYears()
    {
        Schema::create('school_years', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->smallInteger('year', false, true)->unsigned()->nullable();
        });
    }

    /**
     * CREATE TABLE `schools` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `user_id` int(10) unsigned NOT NULL,
     * `umbrella_organization_id` int(10) unsigned DEFAULT NULL,
     * `customer_code` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `main_address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `main_postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
     * `main_city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `main_country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `invoice_address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `invoice_postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
     * `invoice_city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `invoice_country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `count_active_licenses` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_active_teachers` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_expired_licenses` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_licenses` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_questions` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_students` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_teachers` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_tests` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_tests_taken` int(10) unsigned NOT NULL DEFAULT '0',
     * `external_main_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `count_text2speech` int(11) NOT NULL DEFAULT '0',
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `schools_uuid_unique` (`uuid`),
     * KEY `fk_school_umbrella_organizations1_idx` (`umbrella_organization_id`),
     * KEY `fk_schools_users1_idx` (`user_id`),
     * CONSTRAINT `fk_school_umbrella_organizations1` FOREIGN KEY (`umbrella_organization_id`) REFERENCES `umbrella_organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_schools_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createSchools()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('user_id')->unsigned();
            $table->integer('umbrella_organization_id')->unsigned()->nullable();
            $table->string('customer_code', 60)->nullable();
            $table->string('name', 60);
            $table->string('main_address', 60);
            $table->string('main_postal', 7);
            $table->string('main_city', 60);
            $table->string('main_country', 60);
            $table->string('invoice_address', 60);
            $table->string('invoice_postal', 7)->nullable();
            $table->string('invoice_city', 60)->nullable();
            $table->string('invoice_country', 60)->nullable();
            $table->integer('count_active_licenses')->unsigned()->default('0');
            $table->integer('count_active_teachers')->unsigned()->default('0');
            $table->integer('count_expired_licenses')->unsigned()->default('0');
            $table->integer('count_licenses')->unsigned()->default('0');
            $table->integer('count_questions')->unsigned()->default('0');
            $table->integer('count_students')->unsigned()->default('0');
            $table->integer('count_teachers')->unsigned()->default('0');
            $table->integer('count_tests')->unsigned()->default('0');
            $table->integer('count_tests_taken')->unsigned()->default('0');
            $table->string('external_main_code', 50)->nullable();
            $table->integer('count_text2speech')->default('0');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
//            $table->foreign('umbrella_organization_id')->references('id')->on('umbrella_organizations');
//            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * CREATE TABLE `search_filters` (
     * `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
     * `user_id` int(10) unsigned NOT NULL,
     * `filters` text COLLATE utf8_unicode_ci,
     * `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
     * `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * `active` tinyint(1) NOT NULL DEFAULT '0',
     * `created_at` timestamp NULL DEFAULT NULL,
     * `updated_at` timestamp NULL DEFAULT NULL,
     * `cached_filter` tinyint(1) NOT NULL DEFAULT '0',
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `search_filters_uuid_unique` (`uuid`),
     * KEY `search_filters_user_id_foreign` (`user_id`),
     * CONSTRAINT `search_filters_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=311 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createSearchFilters()
    {
        Schema::create('search_filters', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->text('filters');
            $table->string('key');
            $table->string('name');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->tinyInteger('active')->default('0');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->tinyInteger('cached_filter')->default('0');
//            $table->foreign('user_id')->references('id')->on('users');
        });
    }
//
//
//# Dump of table sections
//# ------------------------------------------------------------
//
//CREATE TABLE `sections` (
//`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//`updated_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
//`deleted_at` timestamp NULL default NULL,
//`name` varchar(45) COLLATE utf8_unicode_ci default NULL,
//`demo` tinyint(1) NOT NULL default '0',
//`uuid` binary(16) default NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `sections_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
    private function createSections()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('name', 45)->nullable();
            $table->boolean('demo')->default(false);
            $table->efficientUuid('uuid')->index()->unique()->nullable();
        });
    }

//
//# Dump of table shortcode_clicks
//# ------------------------------------------------------------
//
//CREATE TABLE `shortcode_clicks` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`shortcode_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`uuid` binary(16) NOT NULL,
//`user_id` int(11) default NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `shortcode_clicks_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createShortcodeClicks()
    {
        Schema::create('shortcode_clicks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('shortcode_id');
            $table->string('ip');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->integer('user_id')->nullable(); // user_id of the user who is created through this process
        });
    }

//
//
//# Dump of table shortcodes
//# ------------------------------------------------------------
//
//CREATE TABLE `shortcodes` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`user_id` int(11) NOT NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createShortcodes()
    {
        Schema::create('shortcodes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('code');
            $table->integer('user_id');
        });
    }

//
//
//# Dump of table somtodaycount
//# ------------------------------------------------------------
//
//CREATE TABLE `somtodaycount` (
//`id` int(10) unsigned NOT NULL,
//`count` int(3) default NULL,
//`counted` int(3) default NULL,
//`class_name` varchar(100) COLLATE utf8_unicode_ci default NULL,
//`class_id` int(8) default NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function somtodaycount()
    {
        Schema::create('somtodaycount', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('count')->nullable();
            $table->smallInteger('counted')->nullable();
            $table->string('class_name')->nullable();
            $table->integer('class_id')->nullable();
        });
    }

    /**
     * CREATE TABLE `student_parents` (
     * `parent_id` int(10) unsigned NOT NULL,
     * `user_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * PRIMARY KEY (`parent_id`,`user_id`),
     * KEY `fk_student_parents_parents1_idx` (`parent_id`),
     * KEY `fk_student_parents_users1_idx` (`user_id`),
     * CONSTRAINT `fk_student_parents_parents1` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_student_parents_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createStudentParents()
    {
        Schema::create('student_parents', function (Blueprint $table) {
            $table->integer('parent_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->primary(['parent_id', 'user_id']);
//            $table->foreign('parent_id')->references('id')->on('users');
//            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * CREATE TABLE `students` (
     * `user_id` int(10) unsigned NOT NULL,
     * `class_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     * `updated_at` timestamp NULL DEFAULT NULL,
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `studentscol` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`user_id`,`class_id`),
     * UNIQUE KEY `students_uuid_unique` (`uuid`),
     * KEY `fk_users_has_school_classes_users1_idx` (`user_id`),
     * KEY `fk_users_has_school_classes_school_classes1_idx` (`class_id`),
     * CONSTRAINT `fk_users_has_school_classes_school_classes1` FOREIGN KEY (`class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_users_has_school_classes_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createStudents()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('class_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('studentscol', 45)->nullable();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->primary(['user_id', 'class_id']);
//            $table->foreign('class_id')->references('id')->on('school_classes');
//            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * CREATE TABLE `base_subjects` (
     * `id` int unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `name` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * `show_in_onboarding` tinyint(1) NOT NULL DEFAULT '0',
     * `level` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT 'VO',
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `base_subjects_uuid_unique` (`uuid`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
     */
    private function createBaseSubjects()
    {
        Schema::create('base_subjects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('name', 45)->nullable();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->tinyInteger('show_in_onboarding')->default('0');
            $table->string('level')->default('VO');
        });
    }


    /**
     * CREATE TABLE `subjects` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     * `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `section_id` int(10) unsigned NOT NULL,
     * `base_subject_id` int(10) unsigned DEFAULT NULL,
     * `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `abbreviation` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `demo` tinyint(1) NOT NULL DEFAULT '0',
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `subjects_uuid_unique` (`uuid`),
     * KEY `fk_subjects_sections1_idx` (`section_id`),
     * KEY `fk_subjects_base_subject1_idx` (`base_subject_id`),
     * CONSTRAINT `fk_subjects_base_subject1` FOREIGN KEY (`base_subject_id`) REFERENCES `base_subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_subjects_sections1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=894 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createSubjects()
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('section_id')->unsigned();
            $table->integer('base_subject_id')->unsigned()->nullable();
            $table->string('name', 45)->nullable();
            $table->string('abbreviation', 10)->nullable();
            $table->tinyInteger('demo')->default('0');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
//            $table->foreign('base_subject_id')->references('id')->on('base_subjects');
//            $table->foreign('section_id')->references('id')->on('sections');
        });
    }
//
//
//# Dump of table support_take_over_logs
//# ------------------------------------------------------------
//
//CREATE TABLE `support_take_over_logs` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`support_user_id` int(11) NOT NULL,
//`user_id` int(11) NOT NULL,
//`ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`uuid` binary(16) NOT NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `support_take_over_logs_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createSupportTakeOverLogs()
    {
        Schema::create('support_take_over_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('support_user_id');
            $table->integer('user_id');
            $table->string('ip');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
        });
    }

    /**
     * CREATE TABLE `tag_relations` (
     * `tag_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `tag_relation_id` int(10) unsigned NOT NULL,
     * `tag_relation_type` varchar(45) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
     * PRIMARY KEY (`tag_relation_id`,`tag_relation_type`,`tag_id`),
     * KEY `fk_tag_relations_tags1_idx` (`tag_id`),
     * CONSTRAINT `fk_tag_relations_tags1` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createTagRelations()
    {
        Schema::create('tag_relations', function (Blueprint $table) {
            $table->integer('tag_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('tag_relation_id')->unsigned();
            $table->string('tag_relation_type')->default('');
            $table->primary(['tag_relation_id', 'tag_relation_type', 'tag_id']);
//            $table->foreign('tag_id')->references('id')->on('tags');
        });
    }


    /**
     * CREATE TABLE `tags` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `tags_uuid_unique` (`uuid`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=476 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createTags()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('name', 45)->nullable();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
        });
    }
//
//
//# Dump of table teacher_import_logs
//# ------------------------------------------------------------
//
//CREATE TABLE `teacher_import_logs` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`checked_by_teacher` datetime default NULL,
//`teacher_id` int(11) NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createTeacherImportLogs()
    {
        Schema::create('teacher_import_logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('checked_by_teacher')->nullable();
            $table->integer('teacher_id');
            $table->timestamps();
            $table->softDeletes();;
        });
    }

    /**
     * CREATE TABLE `teachers` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `user_id` int(10) unsigned NOT NULL,
     * `class_id` int(10) unsigned NOT NULL,
     * `subject_id` int(10) unsigned NOT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `unique_user_has_school_classes_with_subject` (`user_id`,`class_id`,`subject_id`),
     * UNIQUE KEY `teachers_uuid_unique` (`uuid`),
     * KEY `fk_users_has_school_classes_users2_idx` (`user_id`),
     * KEY `fk_users_has_school_classes_school_classes2_idx` (`class_id`),
     * KEY `fk_teachers_subjects1_idx` (`subject_id`),
     * CONSTRAINT `fk_teachers_subjects1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_users_has_school_classes_school_classes2` FOREIGN KEY (`class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_users_has_school_classes_users2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=25897 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createTeachers()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('user_id')->unsigned();
            $table->integer('class_id')->unsigned();
            $table->integer('subject_id')->unsigned();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
//            $table->foreign('subject_id')->references('id')->on('subjects');
//            $table->foreign('class_id')->references('id')->on('school_classes');
//            $table->foreign('user_id')->references('id')->on('users');
        });
    }
//
//
//# Dump of table telescope_entries
//# ------------------------------------------------------------
//
//CREATE TABLE `telescope_entries` (
//`sequence` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`uuid` char(36) COLLATE utf8_unicode_ci NOT NULL,
//`batch_id` char(36) COLLATE utf8_unicode_ci NOT NULL,
//`family_hash` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`should_display_on_index` tinyint(1) NOT NULL default '1',
//`type` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
//`content` longtext COLLATE utf8_unicode_ci NOT NULL,
//`created_at` datetime default NULL,
//PRIMARY KEY (`sequence`),
//UNIQUE KEY `telescope_entries_uuid_unique` (`uuid`),
//KEY `telescope_entries_batch_id_index` (`batch_id`),
//KEY `telescope_entries_type_should_display_on_index_index` (`type`, `should_display_on_index`),
//KEY `telescope_entries_family_hash_index` (`family_hash`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
//
//# Dump of table telescope_entries_tags
//# ------------------------------------------------------------
//
//CREATE TABLE `telescope_entries_tags` (
//`entry_uuid` char(36) COLLATE utf8_unicode_ci NOT NULL,
//`tag` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//KEY `telescope_entries_tags_entry_uuid_tag_index` (`entry_uuid`, `tag`),
//KEY `telescope_entries_tags_tag_index` (`tag`),
//CONSTRAINT `telescope_entries_tags_entry_uuid_foreign` FOREIGN KEY (`entry_uuid`) REFERENCES `telescope_entries` (`uuid`) ON DELETE CASCADE
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
//
//# Dump of table telescope_monitoring
//# ------------------------------------------------------------
//
//CREATE TABLE `telescope_monitoring` (
//`tag` varchar(255) COLLATE utf8_unicode_ci NOT NULL
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
//
//# Dump of table temporary_login
//# ------------------------------------------------------------
//
//CREATE TABLE `temporary_login` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`user_id` bigint(20) NOT NULL,
//`uuid` blob NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`options` json default NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
    private function temporary_login()
    {
        Schema::create('temporary_login', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->binary('uuid');
            $table->timestamps();
            $table->softDeletes();;
            $table->json('options')->nullable();
        });
    }

//
//# Dump of table test_authors
//# ------------------------------------------------------------
//
//CREATE TABLE `test_authors` (
//`test_id` int(11) NOT NULL,
//`user_id` int(11) NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//PRIMARY KEY (`test_id`, `user_id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
    private function createTestAuthors()
    {
        Schema::create('test_authors', function (Blueprint $table) {
            $table->integer('test_id');
            $table->integer('user_id');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['test_id', 'user_id']);
        });
        /** @TODO add test
         * \tcCore\Test::withTrashed()->get()->each(function (\tcCore\Test $test) {
         * \tcCore\TestAuthor::create([
         * 'user_id' => $test->author_id,
         * 'test_id' => $test->getKey()
         * ]);
         * });
         */
    }

    /**
     * CREATE TABLE `test_kinds` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `name` varchar(45) NOT NULL,
     * `has_weight` tinyint(1) NOT NULL,
     * PRIMARY KEY (`id`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
     */
    private function createTestKinds()
    {
        Schema::create('test_kinds', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('name', 45);
            $table->tinyInteger('has_weight');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
        });

        DB::table('test_kinds')->insert([
            ['id' => 1, 'name' => 'Oefentoets', 'has_weight' => 0,],
            ['id' => 2, 'name' => 'Formatief', 'has_weight' => 0,],
            ['id' => 3, 'name' => 'Summatief', 'has_weight' => 1,],
            ['id' => 4, 'name' => 'Opdracht', 'has_weight' => 0,],
        ]);

        TestKind::all()->each(function (TestKind $testKind) {
            $testKind->uuid = (new tcCore\TestKind)->resolveUuid();
            $testKind->save();
        });
    }

    /**
     * CREATE TABLE `test_participants` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `heartbeat_at` timestamp NULL DEFAULT NULL,
     * `test_take_id` int(10) unsigned NOT NULL,
     * `user_id` int(10) unsigned NOT NULL,
     * `test_take_status_id` int(10) unsigned NOT NULL,
     * `school_class_id` int(10) unsigned NOT NULL,
     * `answer_id` int(10) unsigned DEFAULT NULL,
     * `invigilator_note` text COLLATE utf8_unicode_ci,
     * `rating` decimal(4,2) unsigned DEFAULT NULL,
     * `retake_rating` decimal(4,2) unsigned DEFAULT NULL,
     * `ip_address` varbinary(16) DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * `allow_inbrowser_testing` tinyint(1) NOT NULL DEFAULT '0',
     * `started_in_new_player` tinyint(1) NOT NULL DEFAULT '0',
     * `answers_provisioned` tinyint(1) NOT NULL DEFAULT '0',
     * `available_for_guests` tinyint(1) NOT NULL DEFAULT '0',
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `unique_test_takes_has_users` (`test_take_id`,`user_id`),
     * UNIQUE KEY `test_participants_uuid_unique` (`uuid`),
     * KEY `fk_test_takes_has_users_test_takes1_idx` (`test_take_id`),
     * KEY `fk_test_takes_has_users_users1_idx` (`user_id`),
     * KEY `fk_test_participants_test_take_statuses1_idx` (`test_take_status_id`),
     * KEY `fk_test_participants_school_classes1_idx` (`school_class_id`),
     * KEY `fk_test_participants_answers1_idx` (`answer_id`),
     * CONSTRAINT `fk_test_participants_answers1` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_test_participants_school_classes1` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_test_participants_test_take_statuses1` FOREIGN KEY (`test_take_status_id`) REFERENCES `test_take_statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_test_takes_has_users_test_takes1` FOREIGN KEY (`test_take_id`) REFERENCES `test_takes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_test_takes_has_users_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=265280 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createTestParticipants()
    {
        Schema::create('test_participants', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->timestamp('heartbeat_at')->nullable();
            $table->integer('test_take_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('test_take_status_id')->unsigned();
            $table->integer('school_class_id')->unsigned();
            $table->integer('answer_id')->unsigned()->nullable();
            $table->text('invigilator_note')->nullable();
            $table->decimal('rating', 4, 2)->nullable();
            $table->decimal('retake_rating', 4, 2)->nullable();
            $table->binary('ip_address')->nullable();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->tinyInteger('allow_inbrowser_testing')->default('0');
            $table->tinyInteger('started_in_new_player')->default('0');
            $table->tinyInteger('answers_provisioned')->default('0');
            $table->tinyInteger('available_for_guests')->default('0');
            $table->integer('discussing_answer_rating_id')->unsigned()->nullable();
//            $table->foreign('answer_id')->references('id')->on('answers');
//            $table->foreign('school_class_id')->references('id')->on('school_classes');
//            $table->foreign('test_take_status_id')->references('id')->on('test_take_statuses');
//            $table->foreign('test_take_id')->references('id')->on('test_takes');
//            $table->foreign('user_id')->references('id')->on('users');

        });
    }

    /**
     * CREATE TABLE `test_questions` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `test_id` int(10) unsigned NOT NULL,
     * `question_id` int(10) unsigned NOT NULL,
     * `order` int(10) unsigned NOT NULL,
     * `maintain_position` tinyint(1) NOT NULL,
     * `discuss` tinyint(1) NOT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `test_questions_uuid_unique` (`uuid`),
     * KEY `fk_test_questions_tests1_idx` (`test_id`),
     * KEY `fk_test_questions_questions1_idx` (`question_id`),
     * CONSTRAINT `fk_test_questions_questions1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_test_questions_tests1` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=195230 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createTestQuestions()
    {
        Schema::create('test_questions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('test_id')->unsigned();
            $table->integer('question_id')->unsigned();
            $table->integer('order')->unsigned();
            $table->tinyInteger('maintain_position');
            $table->tinyInteger('discuss');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
//            $table->foreign('question_id')->references('id')->on('questions');
//            $table->foreign('test_id')->references('id')->on('tests');
        });
    }


//
//# Dump of table test_rating_participants
//# ------------------------------------------------------------
//
//CREATE TABLE `test_rating_participants` (
//`test_participant_id` int(10) unsigned NOT NULL,
//`test_rating_id` int(10) unsigned NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createTestRatingParticipants()
    {
        Schema::create('test_rating_participants', function (Blueprint $table) {
            $table->integer('test_participant_id')->unsigned()->index(
                'fk_test_participants_has_test_ratings_test_participants1_idx'
            );
            $table->integer('test_rating_id')->unsigned()->index(
                'fk_test_participants_has_test_ratings_test_ratings1_idx'
            );
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['test_participant_id', 'test_rating_id'], 'primary_key_for_test_rating');
//            $table->foreign('test_participant_id',
//                'fk_test_participants_has_test_ratings_test_participants1')->references('id')->on('test_participants')->onUpdate('CASCADE')->onDelete('CASCADE');
//            $table->foreign('test_rating_id',
//                'fk_test_participants_has_test_ratings_test_ratings1')->references('id')->on('test_ratings')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

//
//
//# Dump of table test_take_codes
//# ------------------------------------------------------------
//
//CREATE TABLE `test_take_codes` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`test_take_id` int(11) NOT NULL,
//`code` int(11) NOT NULL,
//`prefix` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
//`rating_visible_expiration` datetime default NULL,
//`uuid` binary(16) default NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `test_take_codes_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
//
    private function createTestTakeCodes()
    {
        Schema::create('test_take_codes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('test_take_id');
            $table->integer('code');
            $table->string('prefix', 10);
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->dateTime('rating_visible_expiration')->nullable();
        });
    }

    /**
     * CREATE TABLE `test_take_event_types` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
     * `requires_confirming` tinyint(1) NOT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * `reason` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `test_take_event_types_uuid_unique` (`uuid`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createTestTakeEventTypes()
    {
        Schema::create('test_take_event_types', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('name', 45);
            $table->tinyInteger('requires_confirming');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->boolean('show_alarm_to_student');
            $table->string('reason', 50);
        });
    }

    /**
     * CREATE TABLE `test_take_events` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `test_take_id` int(10) unsigned NOT NULL,
     * `test_participant_id` int(10) unsigned DEFAULT NULL,
     * `test_take_event_type_id` int(10) unsigned NOT NULL,
     * `confirmed` tinyint(1) NOT NULL DEFAULT '0',
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `test_take_events_uuid_unique` (`uuid`),
     * KEY `fk_test_take_events_test_take_event_types1_idx` (`test_take_event_type_id`),
     * KEY `fk_test_take_events_test_takes1_idx` (`test_take_id`),
     * KEY `fk_test_take_events_test_participants1_idx` (`test_participant_id`),
     * CONSTRAINT `fk_test_take_events_test_participants1` FOREIGN KEY (`test_participant_id`) REFERENCES `test_participants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_test_take_events_test_take_event_types1` FOREIGN KEY (`test_take_event_type_id`) REFERENCES `test_take_event_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_test_take_events_test_takes1` FOREIGN KEY (`test_take_id`) REFERENCES `test_takes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=158246 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createTestTakeEvents()
    {
        Schema::create('test_take_events', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('test_take_id')->unsigned();
            $table->integer('test_participant_id')->unsigned()->nullable();
            $table->integer('test_take_event_type_id')->unsigned();
            $table->tinyInteger('confirmed')->default('0');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->json('metadata')->nullable();
//            $table->foreign('test_participant_id')->references('id')->on('test_participants');
//            $table->foreign('test_take_event_type_id')->references('id')->on('test_take_event_types');
//            $table->foreign('test_take_id')->references('id')->on('test_takes');
        });
    }
//# Dump of table test_take_status_logs
//# ------------------------------------------------------------
//
//CREATE TABLE `test_take_status_logs` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`test_take_id` int(11) NOT NULL,
//`test_take_status_id` int(11) NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//PRIMARY KEY (`id`),
//KEY `test_take_status_logs_created_at_index` (`created_at`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
    private function createTestTakeStatusLogs()
    {
        Schema::create('test_take_status_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('test_take_id');
            $table->integer('test_take_status_id');
            $table->timestamps();
            $table->softDeletes();;
            $table->index('created_at');
        });
    }

    /**
     * CREATE TABLE `test_take_statuses` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `is_individual_status` tinyint(1) DEFAULT NULL,
     * PRIMARY KEY (`id`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createTestTakeStatuses()
    {
        Schema::create('test_take_statuses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->string('name', 45)->nullable();
            $table->tinyInteger('is_individual_status')->nullable();
        });
    }

    /**
     * CREATE TABLE `test_takes` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `user_id` int(10) unsigned NOT NULL,
     * `test_id` int(10) unsigned NOT NULL,
     * `test_take_status_id` int(10) unsigned NOT NULL,
     * `period_id` int(10) unsigned NOT NULL,
     * `is_discussed` tinyint(1) NOT NULL DEFAULT '0',
     * `discussed_user_id` int(10) unsigned DEFAULT NULL,
     * `discussing_question_id` int(10) unsigned DEFAULT NULL,
     * `retake` tinyint(1) DEFAULT NULL,
     * `retake_test_take_id` int(10) unsigned DEFAULT NULL,
     * `time_start` datetime DEFAULT NULL,
     * `time_end` datetime DEFAULT NULL,
     * `location` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `weight` int(10) unsigned DEFAULT NULL,
     * `note` text COLLATE utf8_unicode_ci,
     * `invigilator_note` text COLLATE utf8_unicode_ci,
     * `discussion_type` enum('ALL','OPEN_ONLY') COLLATE utf8_unicode_ci DEFAULT NULL,
     * `show_results` datetime DEFAULT NULL,
     * `ppp` decimal(6,4) unsigned DEFAULT NULL,
     * `epp` decimal(6,4) unsigned DEFAULT NULL,
     * `wanted_average` decimal(6,4) unsigned DEFAULT NULL,
     * `n_term` decimal(6,4) DEFAULT NULL,
     * `pass_mark` decimal(8,4) unsigned DEFAULT NULL,
     * `is_rtti_test_take` tinyint(1) DEFAULT '0',
     * `exported_to_rtti` datetime DEFAULT NULL,
     * `demo` tinyint(1) NOT NULL DEFAULT '0',
     * `uuid` binary(16) DEFAULT NULL,
     * `school_location_id` int(10) unsigned NOT NULL DEFAULT '0',
     * `allow_inbrowser_testing` tinyint(1) NOT NULL DEFAULT '0',
     * `guest_accounts` tinyint(1) NOT NULL DEFAULT '0',
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `test_takes_uuid_unique` (`uuid`),
     * KEY `fk_test_takes_tests1_idx` (`test_id`),
     * KEY `fk_test_takes_test_take_statuses1_idx` (`test_take_status_id`),
     * KEY `fk_test_takes_periods1_idx` (`period_id`),
     * KEY `fk_test_takes_users1_idx` (`user_id`),
     * KEY `fk_test_takes_test_takes1_idx` (`retake_test_take_id`),
     * KEY `fk_test_takes_questions1_idx` (`discussing_question_id`),
     * KEY `fk_test_takes_users2_idx` (`discussed_user_id`),
     * KEY `test_takes_demo_index` (`demo`),
     * KEY `test_takes_school_location_id_index` (`school_location_id`),
     * KEY `test_takes_time_start_index` (`time_start`),
     * CONSTRAINT `fk_test_takes_periods1` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_test_takes_questions1` FOREIGN KEY (`discussing_question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_test_takes_test_take_statuses1` FOREIGN KEY (`test_take_status_id`) REFERENCES `test_take_statuses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_test_takes_test_takes1` FOREIGN KEY (`retake_test_take_id`) REFERENCES `test_takes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_test_takes_tests1` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_test_takes_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_test_takes_users2` FOREIGN KEY (`discussed_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=36548 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */

    private function createTestTake()
    {
        Schema::create('test_takes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('user_id')->unsigned();
            $table->integer('test_id')->unsigned();
            $table->integer('test_take_status_id')->unsigned();
            $table->integer('period_id')->unsigned();
            $table->tinyInteger('is_discussed')->default('0');
            $table->integer('discussed_user_id')->unsigned()->nullable();
            $table->integer('discussing_question_id')->unsigned()->nullable();
            $table->tinyInteger('retake')->nullable();
            $table->integer('retake_test_take_id')->unsigned()->nullable();
            $table->datetime('time_start')->nullable();
            $table->datetime('time_end')->nullable();
            $table->string('location', 45)->nullable();
            $table->integer('weight')->unsigned()->nullable();
            $table->text('note')->nullable();
            $table->text('invigilator_note')->nullable();
            $table->enum('discussion_type', ['ALL', 'OPEN_ONLY'])->nullable();
            $table->datetime('show_results')->nullable();
            $table->decimal('ppp', 6, 4)->nullable();
            $table->decimal('epp', 6, 4)->nullable();
            $table->decimal('wanted_average', 6, 4)->nullable();
            $table->decimal('n_term', 6, 4)->nullable();
            $table->decimal('pass_mark', 8, 4)->nullable();
            $table->tinyInteger('is_rtti_test_take')->default('0');
            $table->datetime('exported_to_rtti')->nullable();
            $table->tinyInteger('demo')->default('0');
            $table->integer('school_location_id')->unsigned()->default('0');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->tinyInteger('allow_inbrowser_testing')->default('0');
            $table->tinyInteger('guest_accounts')->default('0');
            $table->integer('scheduled_by')->unsigned()->nullable();
            $table->boolean('returned_to_taken')->default(false);
            $table->tinyText('assessment_type')->nullable();
            $table->integer('assessing_question_id')->nullable();
            $table->integer('assessing_answer_index')->nullable();
            $table->dateTime('assessed_at')->nullable();
            $table->integer('max_assessed_answer_index')->nullable();
            $table->boolean('allow_wsc')->default(false);
            $table->boolean('show_grades')->default(1);
            $table->boolean('show_correction_model')->default(true)->nullable();
            $table->boolean('enable_spellcheck_colearning')->default(false);
            $table->boolean('enable_comments_colearning')->default(false);
            $table->boolean('review_active')->default(false);
            $table->dateTime('results_published')->nullable();

//            $table->foreign('period_id')->references('id')->on('periods');
//            $table->foreign('discussing_question_id')->references('id')->on('questions');
//            $table->foreign('test_take_status_id')->references('id')->on('test_take_statuses');
//            $table->foreign('retake_test_take_id')->references('id')->on('test_takes');
//            $table->foreign('test_id')->references('id')->on('tests');
//            $table->foreign('user_id')->references('id')->on('users');
//            $table->foreign('discussed_user_id')->references('id')->on('users');
        });
    }

    /**
     * CREATE TABLE `tests` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NULL DEFAULT NULL,
     * `updated_at` timestamp NULL DEFAULT NULL,
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `subject_id` int(10) unsigned NOT NULL,
     * `education_level_id` int(10) unsigned NOT NULL,
     * `period_id` int(10) unsigned NOT NULL,
     * `author_id` int(10) unsigned NOT NULL,
     * `test_kind_id` int(10) unsigned NOT NULL,
     * `system_test_id` int(10) unsigned DEFAULT NULL,
     * `name` varchar(140) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `abbreviation` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `education_level_year` int(10) unsigned NOT NULL,
     * `status` int(11) NOT NULL DEFAULT '0',
     * `introduction` text COLLATE utf8_unicode_ci,
     * `shuffle` tinyint(1) NOT NULL,
     * `is_system_test` tinyint(1) NOT NULL,
     * `question_count` int(10) unsigned NOT NULL DEFAULT '0',
     * `is_open_source_content` tinyint(1) DEFAULT NULL,
     * `demo` tinyint(1) NOT NULL DEFAULT '0',
     * `metadata` text COLLATE utf8_unicode_ci,
     * `external_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `published` tinyint(1) NOT NULL DEFAULT '1',
     * `uuid` binary(16) DEFAULT NULL,
     * `owner_id` int(11) DEFAULT NULL,
     * `derived_test_id` int(11) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `tests_uuid_unique` (`uuid`),
     * KEY `fk_tests_subjects1_idx` (`subject_id`),
     * KEY `fk_tests_education_levels1_idx` (`education_level_id`),
     * KEY `fk_tests_periods1_idx` (`period_id`),
     * KEY `fk_tests_users1_idx` (`author_id`),
     * KEY `fk_tests_test_kind1_idx` (`test_kind_id`),
     * KEY `fk_tests_tests1_idx` (`system_test_id`),
     * KEY `tests_demo_index` (`demo`),
     * KEY `tests_scope_index` (`scope`),
     * CONSTRAINT `fk_tests_education_levels1` FOREIGN KEY (`education_level_id`) REFERENCES `education_levels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_tests_periods1` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_tests_subjects1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_tests_test_kind1` FOREIGN KEY (`test_kind_id`) REFERENCES `test_kinds` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_tests_tests1` FOREIGN KEY (`system_test_id`) REFERENCES `tests` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
     * CONSTRAINT `fk_tests_users1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=20791 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createTests()
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->integer('subject_id')->unsigned();
            $table->integer('education_level_id')->unsigned();
            $table->integer('period_id')->unsigned();
            $table->integer('author_id')->unsigned();
            $table->integer('test_kind_id')->unsigned();
            $table->integer('system_test_id')->unsigned()->nullable();
            $table->string('name', 140)->nullable();
            $table->string('abbreviation', 20)->nullable();
            $table->integer('education_level_year')->unsigned();
            $table->integer('status')->default('0');
            $table->text('introduction');
            $table->tinyInteger('shuffle');
            $table->tinyInteger('is_system_test')->nullable();
            $table->integer('question_count')->unsigned()->default('0');
            $table->tinyInteger('is_open_source_content')->nullable();
            $table->tinyInteger('demo')->default('0');
            $table->text('metadata')->nullable();
            $table->string('external_id')->nullable();
            $table->string('scope')->nullable();
            $table->tinyInteger('published')->default('1');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->integer('owner_id')->nullable();
            $table->integer('derived_test_id')->nullable();
            $table->boolean('draft')->default(false);
//            $table->foreign('education_level_id')->references('id')->on('education_levels');
//            $table->foreign('period_id')->references('id')->on('periods');
//            $table->foreign('subject_id')->references('id')->on('subjects');
//            $table->foreign('test_kind_id')->references('id')->on('test_kinds');
//            $table->foreign('system_test_id')->references('id')->on('tests');
//            $table->foreign('author_id')->references('id')->on('users');
        });
    }

    /**
     * CREATE TABLE `text2speech` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `user_id` int(11) NOT NULL,
     * `price` double(5,2) NOT NULL,
     * `active` tinyint(1) NOT NULL DEFAULT '1',
     * `acceptedby` int(11) NOT NULL,
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * KEY `text2speech_user_id_index` (`user_id`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=430 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createText2speech()
    {
        Schema::create('text2speech', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->default('0000-00-00 00:00:00');
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
            $table->integer('user_id');
            $table->double('price', 5, 2);
            $table->tinyInteger('active')->default('1');
            $table->integer('acceptedby');
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * CREATE TABLE `text2speech_log` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `user_id` int(11) NOT NULL,
     * `who` int(11) NOT NULL,
     * `action` enum('ACCEPTED','ENABLED','DISABLED') COLLATE utf8_unicode_ci NOT NULL,
     * PRIMARY KEY (`id`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=452 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createText2speechLog()
    {
        Schema::create('text2speech_log', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->default('0000-00-00 00:00:00');
            $table->timestamp('updated_at')->default('0000-00-00 00:00:00');
            $table->integer('user_id');
            $table->integer('who');
            $table->enum('action', ['ACCEPTED', 'ENABLED', 'DISABLED']);
        });
    }

    /**
     * CREATE TABLE `umbrella_organization_addresses` (
     * `address_id` int(10) unsigned NOT NULL,
     * `umbrella_organization_id` int(10) unsigned NOT NULL,
     * `type` enum('MAIN','INVOICE','OTHER') COLLATE utf8_unicode_ci NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * PRIMARY KEY (`address_id`,`umbrella_organization_id`,`type`),
     * KEY `fk_umbrella_organization_addresses_addresses1_idx` (`address_id`),
     * KEY `fk_umbrella_organization_addresses_umbrella_organizations1_idx` (`umbrella_organization_id`),
     * CONSTRAINT `fk_umbrella_organization_addresses_addresses1` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_umbrella_organization_addresses_umbrella_organizations1` FOREIGN KEY (`umbrella_organization_id`) REFERENCES `umbrella_organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createUmbrellaOrganizationAddresses()
    {
        Schema::create('umbrella_organization_addresses', function (Blueprint $table) {
            $table->integer('address_id')->unsigned();
            $table->integer('umbrella_organization_id')->unsigned();
            $table->enum('type', ['MAIN', 'INVOICE', 'OTHER']);
            $table->timestamps();
            $table->softDeletes();;
            $table->primary(['address_id', 'umbrella_organization_id', 'type'])->name(
                'prim_umbrella_organization_addresses'
            );
//            $table->foreign('address_id')->references('id')->on('addresses');
//            $table->foreign('umbrella_organization_id')->references('id')->on('umbrella_organizations');
        });
    }
    /**
     * CREATE TABLE `umbrella_organization_contacts` (
     * `umbrella_organization_id` int(10) unsigned NOT NULL,
     * `contact_id` int(10) unsigned NOT NULL,
     * `type` enum('FINANCE','TECHNICAL','IMPLEMENTATION','OTHER') COLLATE utf8_unicode_ci NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * PRIMARY KEY (`umbrella_organization_id`,`contact_id`,`type`),
     * KEY `fk_umbrella_organization_contacts_umbrella_organizations1_idx` (`umbrella_organization_id`),
     * KEY `fk_umbrella_organization_contacts_contacts1_idx` (`contact_id`),
     * CONSTRAINT `fk_umbrella_organization_contacts_contacts1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_umbrella_organization_contacts_umbrella_organizations1` FOREIGN KEY (`umbrella_organization_id`) REFERENCES `umbrella_organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */

    /**
     * CREATE TABLE `umbrella_organizations` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `user_id` int(10) unsigned NOT NULL,
     * `customer_code` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `name` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `main_address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `main_postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
     * `main_city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `main_country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `invoice_address` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `invoice_postal` varchar(7) COLLATE utf8_unicode_ci NOT NULL,
     * `invoice_city` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `invoice_country` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
     * `count_active_licenses` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_active_teachers` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_expired_licenses` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_licenses` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_questions` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_students` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_teachers` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_tests` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_tests_taken` int(10) unsigned NOT NULL DEFAULT '0',
     * `external_main_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `umbrella_organizations_uuid_unique` (`uuid`),
     * KEY `fk_umbrella_organizations_users1_idx` (`user_id`),
     * CONSTRAINT `fk_umbrella_organizations_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createUmbrellaOrganizationContacts()
    {
        Schema::create('umbrella_organizations', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('user_id')->unsigned();
            $table->string('customer_code', 60)->nullable();
            $table->string('name', 60);
            $table->string('main_address', 60);
            $table->string('main_postal', 7);
            $table->string('main_city', 60);
            $table->string('main_country', 60);
            $table->string('invoice_address', 60);
            $table->string('invoice_postal', 7);
            $table->string('invoice_city', 60);
            $table->string('invoice_country', 60);
            $table->integer('count_active_licenses')->unsigned()->default('0');
            $table->integer('count_active_teachers')->unsigned()->default('0');
            $table->integer('count_expired_licenses')->unsigned()->default('0');
            $table->integer('count_licenses')->unsigned()->default('0');
            $table->integer('count_questions')->unsigned()->default('0');
            $table->integer('count_students')->unsigned()->default('0');
            $table->integer('count_teachers')->unsigned()->default('0');
            $table->integer('count_tests')->unsigned()->default('0');
            $table->integer('count_tests_taken')->unsigned()->default('0');
            $table->string('external_main_code', 50)->nullable();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
//            $table->foreign('user_id')->references('id')->on('users');
        });
    }


    /**
     * CREATE TABLE `user_roles` (
     * `user_id` int(10) unsigned NOT NULL,
     * `role_id` int(10) unsigned NOT NULL,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * PRIMARY KEY (`user_id`,`role_id`),
     * KEY `fk_user_has_roles_users1_idx` (`user_id`),
     * KEY `fk_user_has_roles_roles1_idx` (`role_id`),
     * CONSTRAINT `fk_user_has_roles_roles1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_user_has_roles_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createUserRoles()
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();;
            $table->primary(['user_id', 'role_id']);
//            $table->foreign('role_id')->references('id')->on('roles');
//            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * CREATE TABLE `users` (
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `sales_organization_id` int(10) unsigned DEFAULT NULL,
     * `school_id` int(10) unsigned DEFAULT NULL,
     * `school_location_id` int(10) unsigned DEFAULT NULL,
     * `username` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `password` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `session_hash` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `name_first` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `name_suffix` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `name` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `abbreviation` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `external_id` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `api_key` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `gender` enum('Male','Female','Other') COLLATE utf8_unicode_ci DEFAULT NULL,
     * `time_dispensation` tinyint(1) NOT NULL DEFAULT '0',
     * `send_welcome_email` tinyint(1) NOT NULL DEFAULT '0',
     * `note` text COLLATE utf8_unicode_ci,
     * `profile_image_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `profile_image_size` int(10) unsigned DEFAULT NULL,
     * `profile_image_mime_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `profile_image_extension` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
     * `count_accounts` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_active_licenses` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_expired_licenses` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_last_test_taken` date DEFAULT NULL,
     * `count_licenses` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_questions` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_students` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_teachers` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_tests` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_tests_taken` int(10) unsigned NOT NULL DEFAULT '0',
     * `count_tests_discussed` int(10) unsigned NOT NULL DEFAULT '0',
     * `text2speech` tinyint(1) NOT NULL DEFAULT '0',
     * `demo` tinyint(1) NOT NULL DEFAULT '0',
     * `invited_by` int(11) DEFAULT NULL,
     * `uuid` binary(16) DEFAULT NULL,
     * `account_verified` datetime DEFAULT NULL,
     * `intense` tinyint(1) NOT NULL DEFAULT '0',
     * `guest` tinyint(1) NOT NULL DEFAULT '0',
     * `test_take_code_id` int(11) DEFAULT NULL,
     * PRIMARY KEY (`id`),
     * UNIQUE KEY `users_api_key_unique` (`api_key`),
     * UNIQUE KEY `users_uuid_unique` (`uuid`),
     * KEY `fk_users_sales_organizations1_idx` (`sales_organization_id`),
     * KEY `fk_users_schools1_idx` (`school_id`),
     * KEY `fk_users_school_locations1_idx` (`school_location_id`),
     * KEY `username-deleted_at` (`username`,`deleted_at`),
     * CONSTRAINT `fk_users_sales_organizations1` FOREIGN KEY (`sales_organization_id`) REFERENCES `sales_organizations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_users_school_locations1` FOREIGN KEY (`school_location_id`) REFERENCES `school_locations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_users_schools1` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=68432 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
     */
    private function createUsers()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();;
            $table->integer('sales_organization_id')->unsigned()->nullable();
            $table->integer('school_id')->unsigned()->nullable();
            $table->integer('school_location_id')->unsigned()->nullable();
            $table->string('username', 60)->nullable();
            $table->string('password', 60)->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->string('session_hash', 100)->nullable();
            $table->string('name_first', 45)->nullable();
            $table->string('name_suffix', 45)->nullable();
            $table->string('name', 45)->nullable();
            $table->string('abbreviation', 10)->nullable();
            $table->string('external_id', 45)->nullable();
            $table->string('api_key', 100)->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->tinyInteger('time_dispensation')->default('0');
            $table->tinyInteger('send_welcome_email')->default('0');
            $table->text('note')->nullable();
            $table->string('profile_image_name')->nullable();
            $table->integer('profile_image_size')->unsigned()->nullable();
            $table->string('profile_image_mime_type')->nullable();
            $table->string('profile_image_extension', 10)->nullable();
            $table->integer('count_accounts')->unsigned()->default('0');
            $table->integer('count_active_licenses')->unsigned()->default('0');
            $table->integer('count_expired_licenses')->unsigned()->default('0');
            $table->date('count_last_test_taken')->nullable();
            $table->integer('count_licenses')->unsigned()->default('0');
            $table->integer('count_questions')->unsigned()->default('0');
            $table->integer('count_students')->unsigned()->default('0');
            $table->integer('count_teachers')->unsigned()->default('0');
            $table->integer('count_tests')->unsigned()->default('0');
            $table->integer('count_tests_taken')->unsigned()->default('0');
            $table->integer('count_tests_discussed')->unsigned()->default('0');
            $table->tinyInteger('text2speech')->default('0');
            $table->tinyInteger('demo')->default('0');
            $table->integer('invited_by')->nullable();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->datetime('account_verified')->nullable();
            $table->tinyInteger('intense')->default('0');
            $table->tinyInteger('guest')->default('0');
            $table->integer('test_take_code_id')->nullable();
            $table->boolean('is_examcoordinator')->default(false);
            $table->enum('is_examcoordinator_for', ['NONE', 'SCHOOL_LOCATION', 'SCHOOL'])->nullable();
            $table->timestamp('password_expiration_date')->nullable();
            $table->boolean('has_package')->default(0);

//            $table->foreign('sales_organization_id')->references('id')->on('sales_organizations')->nullable();
//            $table->foreign('school_location_id')->references('id')->on('school_locations')->nullable();
//            $table->foreign('school_id')->references('id')->on('schools')->nullable();
        });
    }
//
//# Dump of table trial_periods
//# ------------------------------------------------------------
//
//CREATE TABLE `trial_periods` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`deleted_at` timestamp NULL default NULL,
//`user_id` bigint(20) NOT NULL,
//`trial_until` datetime NOT NULL,
//`uuid` binary(16) NOT NULL,
//`school_location_id` bigint(20) default NULL,
//PRIMARY KEY (`id`),
//UNIQUE KEY `trial_periods_uuid_unique` (`uuid`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function trial_periods()
    {
        Schema::create('trial_periods', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->bigInteger('user_id');
            $table->dateTime('trial_until');
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->bigInteger('school_location_id')->nullable();
        });

        TrialPeriod::whereNull('deleted_at')->each(function (TrialPeriod $tp) {
            logger('school location id ' . $tp->user->school_location_id);
            $tp->update(['school_location_id' => $tp->user->school_location_id]);
        });
    }

//
//
//# Dump of table user_infos_dont_shows
//# ------------------------------------------------------------
//
//CREATE TABLE `user_infos_dont_shows` (
//`user_id` int(11) NOT NULL,
//`info_id` bigint(20) unsigned NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//PRIMARY KEY (`user_id`, `info_id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
//
    private function createUserInfosDontShows()
    {
        Schema::create('user_infos_dont_shows', function (Blueprint $table) {
            $table->integer('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('info_id')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['user_id', 'info_id']);
        });
    }

//
//# Dump of table uwlr_soap_entries
//# ------------------------------------------------------------
//
//CREATE TABLE `uwlr_soap_entries` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`uwlr_soap_result_id` bigint(20) NOT NULL,
//`key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`object` longtext COLLATE utf8_unicode_ci NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`import_progress` varchar(255) COLLATE utf8_unicode_ci default NULL,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;
    private function createUwlrSoapEntries()
    {
        Schema::create('uwlr_soap_entries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('uwlr_soap_result_id');
            $table->string('key');
            $table->longText('object');
            $table->timestamps();
            $table->softDeletes();;
            $table->string('import_progress')->nullable();
        });
    }

//
//
//# Dump of table uwlr_soap_results
//# ------------------------------------------------------------
//
//CREATE TABLE `uwlr_soap_results` (
//`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//`source` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`client_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`client_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`school_year` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`brin_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`xsdversie` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`dependance_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
//`created_at` timestamp NULL default NULL,
//`updated_at` timestamp NULL default NULL,
//`error_messages` text COLLATE utf8_unicode_ci,
//`status` varchar(255) COLLATE utf8_unicode_ci NOT NULL default 'NEW',
//`failure_messages` text COLLATE utf8_unicode_ci,
//`username_who_imported` varchar(255) COLLATE utf8_unicode_ci NOT NULL default 'system',
//`xml_hash` text COLLATE utf8_unicode_ci,
//`import_progress` varchar(255) COLLATE utf8_unicode_ci default NULL,
//`log` text COLLATE utf8_unicode_ci,
//PRIMARY KEY (`id`)
//) ENGINE = InnoDB default CHARSET = utf8 COLLATE = utf8_unicode_ci;/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */
//;/*!40101 SET SQL_MODE=@OLD_SQL_MODE */
//;/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */
//;/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
//;/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
//;/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
//;
    private function createUwlrSoapResults()
    {
        Schema::create('uwlr_soap_results', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('client_code');
            $table->string('client_name');
            $table->string('school_year');
            $table->string('brin_code');
            $table->string('xsdversie');
            $table->string('dependance_code');
            $table->timestamps();
            $table->softDeletes();;
            $table->text('failure_messages')->nullable();
            $table->text('error_messages')->nullable();
            $table->string('status')->default('NEW');
            $table->string('username_who_imported')->default('system');
            $table->text('xml_hash')->nullable();
            $table->string('import_progress')->nullable();
            $table->text('log')->nullable();
        });
    }

//
    private function createAttainments()
    {
        Schema::create('attainments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('base_subject_id')->unsigned()->index('fk_attainments_base_subjects1_idx');
            $table->integer('education_level_id')->unsigned()->index('fk_attainments_education_levels1_idx');
            $table->integer('attainment_id')->nullable();
            $table->string('code', 45);
            $table->string('subcode', 45)->nullable();
            $table->text('description', 65535)->nullable();
            $table->enum('status', ['ACTIVE', 'REPLACED', 'OLD'])->default('ACTIVE');
            $table->boolean('is_learning_goal')->default(0);
            $table->efficientUuid('uuid')->index()->unique()->nullable();
//            $table->foreign('education_level_id', 'fk_attainments_education_levels1')->references('id')->on('education_levels')->onUpdate('CASCADE')->onDelete('CASCADE');
//            $table->foreign('base_subject_id', 'fk_attainments_base_subjects1')->references('id')->on('base_subjects')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    private function createAttachements()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('question_group_id')->unsigned()->nullable()->index('fk_attachments_question_groups1_idx');
            $table->integer('question_id')->unsigned()->nullable()->index('fk_attachments_questions1_idx');
            $table->string('type', 45);
            $table->string('title')->nullable();
            $table->json('json')->nullable();
            $table->text('description')->nullable();
            $table->mediumText('text')->nullable();
            $table->string('link', 45)->nullable();
            $table->string('file_name')->nullable();
            $table->efficientUuid('uuid')->index()->unique()->nullable();
            $table->integer('file_size')->unsigned()->nullable();
            $table->string('file_mime_type')->nullable();
            $table->string('file_extension', 10)->nullable();
        });
    }

    /**
     * CREATE TABLE `average_ratings` (
     * `id` int unsigned NOT NULL AUTO_INCREMENT,
     * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
     * `deleted_at` timestamp NULL DEFAULT NULL,
     * `rating` decimal(8,4) unsigned NOT NULL,
     * `user_id` int unsigned NOT NULL,
     * `school_class_id` int unsigned NOT NULL,
     * `subject_id` int unsigned NOT NULL,
     * PRIMARY KEY (`id`),
     * KEY `fk_average_ratings_users1_idx` (`user_id`),
     * KEY `fk_average_ratings_school_classes1_idx` (`school_class_id`),
     * KEY `fk_average_ratings_subjects1_idx` (`subject_id`),
     * CONSTRAINT `fk_average_ratings_school_classes1` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_average_ratings_subjects1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
     * CONSTRAINT `fk_average_ratings_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
     * ) ENGINE=InnoDB AUTO_INCREMENT=2520 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
     */

    private function createAverageRating()
    {
        Schema::create('average_ratings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->decimal('rating', 8, 4);
            $table->integer('user_id',)->unsigned();
            $table->integer('school_class_id',)->unsigned();
            $table->integer('subject_id',)->unsigned();
        });
    }


    public function createUserSystemSettings()
    {
        if (!Schema::hasTable('user_system_settings')) {
            Schema::create('user_system_settings', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->softDeletes();
                $table->unsignedBigInteger('user_id');
                $table->string('title');
                $table->text('value');
            });
        }
    }

    public function createUserFeatureSettings()
    {
        if (!Schema::hasTable('user_feature_settings')) {
            Schema::create('user_feature_settings', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->softDeletes();
                $table->unsignedBigInteger('user_id');
                $table->string('title');
                $table->text('value');
            });
        }
    }

    public function createMailsSend()
    {
        if (!Schema::hasTable('mails_send')) {
            Schema::create('mails_send', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->unsigned();
                $table->string('mailable');
                $table->timestamps();
            });
//            Schema::table('mails_send', function (Blueprint $table) {
//                $table->foreign('user_id')->references('id')->on('users');
//            });
        }
    }

    public function createRttiExportLogs()
    {
        if (!Schema::hasTable('rtti_export_logs')) {
            Schema::create('rtti_export_logs', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->integer('test_take_id');
                $table->integer('user_id');
                $table->string('url');
                $table->longText('export');
                $table->text('result')->nullable();
                $table->text('error')->nullable();
                $table->boolean('has_errors')->default(false);
                $table->text('response')->nullable();
                $table->string('reference', 50)->nullable();
            });
        }
    }

    public function createAppFeatureSettings(): void
    {
        Schema::create('app_feature_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('title');
            $table->text('value');
        });
    }

    public function createWordLists(): void
    {
        Schema::create('word_lists', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->efficientUuid('uuid')->index()->unique();
            $table->string('name');
            $table->integer('user_id')->foreign('user_id')->references('id')->on('users');

            $table->integer('subject_id')->foreign('subject_id')->references('id')->on('subjects');
            $table->integer('education_level_id')->foreign('education_level_id')->references('id')->on('education_levels');
            $table->integer('education_level_year');
            $table->integer('school_location_id')->foreign('school_location_id')->references('id')->on('school_locations');
        });
    }

    private function createWords()
    {
        Schema::create('words', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->efficientUuid('uuid')->index()->unique();

            $table->string('text');
            $table->unsignedBigInteger('word_id')
                ->nullable()
                ->foreign('word_id')
                ->references('id')
                ->on('words')
                ->onDelete('cascade');

            $table->string('type');
            $table->integer('user_id')->foreign('user_id')->references('id')->on('users');

            $table->integer('subject_id')->foreign('subject_id')->references('id')->on('subjects');
            $table->integer('education_level_id')->foreign('education_level_id')->references('id')->on('education_levels');
            $table->integer('education_level_year');
            $table->integer('school_location_id')->foreign('school_location_id')->references('id')->on('school_locations');
        });
    }

    public function createVersions(): void
    {
        Schema::create('versions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->efficientUuid('uuid')->index()->unique();

            $table->string('name');
            $table->integer('versionable_id')->index();
            $table->string('versionable_type');
            $table->unsignedBigInteger('original_id')->nullable();
            $table->integer('user_id')->foreign('user_id')->references('id')->on('users');
        });
    }

    private function createWordListWord()
    {
        Schema::create('word_list_word', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('word_list_id');
            $table->unsignedBigInteger('word_id');
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('word_list_id')
                ->references('id')
                ->on('word_lists')
                ->onDelete('cascade');

            $table->foreign('word_id')
                ->references('id')
                ->on('words')
                ->onDelete('cascade');

            // Index to speed up look-ups
            $table->index(['word_list_id', 'word_id']);
        });
    }
}
