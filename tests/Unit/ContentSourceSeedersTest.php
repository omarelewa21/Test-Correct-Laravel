<?php

namespace Tests\Unit;

use Database\Seeders\CitoAccountSeeder;
use Database\Seeders\CreathlonItemBankSeeder;
use Database\Seeders\ExamSchoolSeeder;
use Database\Seeders\OlympiadeArchiveItemBankSeeder;
use Database\Seeders\OlympiadeItemBankSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\SchoolLocation;
use tcCore\Test;
use tcCore\User;
use Tests\TestCase;

class ContentSourceSeedersTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Assert:
     *  school_location is created
     *  publishing author is created
     *  published (scope) tests and questions have been created
     *  published (draft) tests and questions have been created
     * @test
     */
    public function can_seed_olympiade_content()
    {
        $user = User::find(1486);
        \Auth::login($user);
        $this->actingAs($user);

        if (SchoolLocation::where('customer_code', '=', config('custom.olympiade_school_customercode'))
            ->exists()) {
            echo __CLASS__.": \033[31m olympiade school has already been seeded previously, so the data the tests rely on could be edited/corrupted \033[0m \n";
        } else {
            (new OlympiadeItemBankSeeder)->run();
        }

        //assert correct author username used for teacher user
        $this->assertTrue(
            User::where('username', '=', config('custom.olympiade_school_author'))
                ->exists()
        );
        //assert correct customerCode used for schoolLocation
        $this->assertTrue(
            SchoolLocation::where('customer_code', '=', config('custom.olympiade_school_customercode'))
                ->exists()
        );

        /**
         * Asserting Publishing Content Source/uitgevers toetsen (SCOPE and ABBREVIATION set)
        */
        //assert published tests
        $this->assertTrue(
            Test::where('scope', '=', 'published_olympiade')
                ->exists()
        );
        $this->assertTrue(
            Test::where('tests.scope', '=', 'published_olympiade')
                ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
                ->join('questions', 'questions.id', '=', 'test_questions.question_id')
                ->where('questions.scope', '=', 'published_olympiade')
                ->exists()
        );
        //assert unpublished tests
        $this->assertTrue(
            Test::where('scope', '=', 'not_published_olympiade')
                ->exists()
        );
        $this->assertTrue(
            Test::where('tests.scope', '=', 'not_published_olympiade')
                ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
                ->join('questions', 'questions.id', '=', 'test_questions.question_id')
                ->where('questions.scope', '=', 'not_published_olympiade')
                ->exists()
        );
        /**
         * Asserting Publishing (DRAFT true or false)
         */
        //assert tests are not drafts
        $this->assertTrue(
            Test::where('scope', '=', 'published_olympiade')
                ->where('draft', '=', false)
                ->exists()
        );

    }

    /**
     * Assert:
     *  school_location is created
     *  publishing author is created
     *  published (scope) tests and questions have been created
     *  published (draft) tests and questions have been created
     * @test
     */
    public function can_seed_olympiade_archive_content()
    {
        $user = User::find(1486);
        \Auth::login($user);
        $this->actingAs($user);

        if (SchoolLocation::where('customer_code', '=', config('custom.olympiade_archive_school_customercode'))
            ->exists()) {
            echo __CLASS__.": \033[31m olympiade Archive school has already been seeded previously, so the data the tests rely on could be edited/corrupted \033[0m \n";
        } else {
            (new OlympiadeArchiveItemBankSeeder)->run();
        }

        //assert correct author username used for teacher user
        $this->assertTrue(
            User::where('username', '=', config('custom.olympiade_archive_school_author'))
                ->exists()
        );
        //assert correct customerCode used for schoolLocation
        $this->assertTrue(
            SchoolLocation::where('customer_code', '=', config('custom.olympiade_archive_school_customercode'))
                ->exists()
        );

        /**
         * Asserting Publishing Content Source/uitgevers toetsen (SCOPE and ABBREVIATION set)
         */
        //assert published tests
        $this->assertTrue(
            Test::where('scope', '=', 'published_olympiade_archive')
                ->exists()
        );
        $this->assertTrue(
            Test::where('tests.scope', '=', 'published_olympiade_archive')
                ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
                ->join('questions', 'questions.id', '=', 'test_questions.question_id')
                ->where('questions.scope', '=', 'published_olympiade_archive')
                ->exists()
        );
        //assert unpublished tests
        $this->assertTrue(
            Test::where('scope', '=', 'not_published_olympiade_archive')
                ->exists()
        );
        $this->assertTrue(
            Test::where('tests.scope', '=', 'not_published_olympiade_archive')
                ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
                ->join('questions', 'questions.id', '=', 'test_questions.question_id')
                ->where('questions.scope', '=', 'not_published_olympiade_archive')
                ->exists()
        );
        /**
         * Asserting Publishing (DRAFT true or false)
         */
        //assert tests are not drafts
        $this->assertTrue(
            Test::where('scope', '=', 'published_olympiade_archive')
                ->where('draft', '=', false)
                ->exists()
        );

    }

    /**
     * Assert:
     *  school_location is created
     *  publishing author is created
     *  published (scope) tests and questions have been created
     *  published (draft) tests and questions have been created
     * @test
     */
    public function can_seed_creathlon_content()
    {
        $user = User::find(1486);
        \Auth::login($user);
        $this->actingAs($user);

        if (SchoolLocation::where('customer_code', '=', config('custom.creathlon_school_customercode'))
            ->exists()) {
            echo __CLASS__.": \033[31m creathlon school has already been seeded previously, so the data the tests rely on could be edited/corrupted \033[0m \n";
        } else {
            (new CreathlonItemBankSeeder())->run();
        }

        //assert correct author username used for teacher user
        $this->assertTrue(
            User::where('username', '=', config('custom.creathlon_school_author'))
                ->exists()
        );
        //assert correct customerCode used for schoolLocation
        $this->assertTrue(
            SchoolLocation::where('customer_code', '=', config('custom.creathlon_school_customercode'))
                ->exists()
        );

        /**
         * Asserting Publishing Content Source/uitgevers toetsen (SCOPE and ABBREVIATION set)
        */
        //assert published tests
        $this->assertTrue(
            Test::where('scope', '=', 'published_creathlon')
                ->exists()
        );
        $this->assertTrue(
            Test::where('tests.scope', '=', 'published_creathlon')
                ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
                ->join('questions', 'questions.id', '=', 'test_questions.question_id')
                ->where('questions.scope', '=', 'published_creathlon')
                ->exists()
        );
        //assert unpublished tests
        $this->assertTrue(
            Test::where('scope', '=', 'not_published_creathlon')
                ->exists()
        );
        $this->assertTrue(
            Test::where('tests.scope', '=', 'not_published_creathlon')
                ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
                ->join('questions', 'questions.id', '=', 'test_questions.question_id')
                ->where('questions.scope', '=', 'not_published_creathlon')
                ->exists()
        );
        /**
         * Asserting Publishing (DRAFT true or false)
         */
        //assert tests are not drafts
        $this->assertTrue(
            Test::where('scope', '=', 'published_creathlon')
                ->where('draft', '=', false)
                ->exists()
        );

    }

    /**
     * Assert:
     *  school_location is created
     *  publishing author is created
     *  published (scope) tests and questions have been created
     *  published (draft) tests and questions have been created
     * @test
     */
    public function can_seed_cito_school_location()
    {
        $user = User::find(1486);
        \Auth::login($user);
        $this->actingAs($user);

        $citoAuthorUsername = 'teacher-cito@test-correct.nl';
        $citoCustomerCode = 'CITO-TOETSENOPMAAT';

        if (SchoolLocation::where('customer_code', '=', $citoCustomerCode)
            ->exists()) {
            echo __CLASS__.": \033[31m cito school has already been seeded previously, so the data the tests rely on could be edited/corrupted \033[0m \n";
        } else {
            (new CitoAccountSeeder())->run();
        }

        //assert correct author username used for teacher user
        $this->assertTrue(
            User::where('username', '=', $citoAuthorUsername)
                ->exists()
        );
        //assert correct customerCode used for schoolLocation
        $this->assertTrue(
            SchoolLocation::where('customer_code', '=', $citoCustomerCode)
                ->exists()
        );

        /**
         * Asserting Publishing Content Source/uitgevers toetsen (SCOPE and ABBREVIATION set)
        */
        //assert published tests
        $this->assertTrue(
            Test::where('scope', '=', 'cito')
                ->exists()
        );
        $this->assertTrue(
            Test::where('tests.scope', '=', 'cito')
                ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
                ->join('questions', 'questions.id', '=', 'test_questions.question_id')
                ->where('questions.scope', '=', 'cito')
                ->exists()
        );

        /**
         * Asserting Publishing (DRAFT true or false)
         */
        //assert tests are not drafts
        $this->assertTrue(
            Test::where('scope', '=', 'cito')
                ->where('draft', '=', false)
                ->exists()
        );

    }


    /**
     * Assert:
     *  school_location is created
     *  publishing author is created
     *  published (scope) tests and questions have been created
     *  published (draft) tests and questions have been created
     * @test
     */
    public function can_seed_examen_school()
    {
        $user = User::find(1486);
        \Auth::login($user);
        $this->actingAs($user);

        if (SchoolLocation::where('customer_code', '=', config('custom.examschool_customercode'))
            ->exists()) {
            echo __CLASS__.": \033[31m examen school has already been seeded previously, so the data the tests rely on could be edited/corrupted \033[0m \n";
        } else {
            (new ExamSchoolSeeder())->run();
        }

        //assert correct author username used for teacher user
        $this->assertTrue(
            User::where('username', '=', config('custom.examschool_author'))
                ->exists()
        );
        //assert correct customerCode used for schoolLocation
        $this->assertTrue(
            SchoolLocation::where('customer_code', '=', config('custom.examschool_customercode'))
                ->exists()
        );

        /**
         * Asserting Publishing Content Source/uitgevers toetsen (SCOPE and ABBREVIATION set)
        */
        //assert published tests
        $this->assertTrue(
            Test::where('scope', '=', 'exam')
                ->exists()
        );
        $this->assertTrue(
            Test::where('tests.scope', '=', 'exam')
                ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
                ->join('questions', 'questions.id', '=', 'test_questions.question_id')
                ->where('questions.scope', '=', 'exam')
                ->exists()
        );

        /**
         * Asserting Publishing (DRAFT true or false)
         */
        //assert tests are not drafts
        $this->assertTrue(
            Test::where('scope', '=', 'exam')
                ->where('draft', '=', false)
                ->exists()
        );

    }

}