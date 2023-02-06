<?php

namespace Tests\Unit\Http\Livewire\Teacher;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use tcCore\EducationLevel;
use tcCore\FileManagement;
use tcCore\FileManagementStatus;
use tcCore\Http\Livewire\Teacher\UploadTest;
use tcCore\Subject;
use tcCore\TestKind;
use tcCore\User;
use Tests\TestCase;

class UploadTestTest extends TestCase
{
    use DatabaseTransactions;

    protected $subjectUuid;
    protected $educationLevelUuid;
    protected $testKindUuid;
    protected $teacherOne;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teacherOne = User::whereUsername('rtti-d1@test-correct.nl')->first();
        $this->actingAs($this->teacherOne);
        $this->subjectUuid = Subject::filtered(['user_current' => $this->teacherOne->getKey()])->first()->uuid; /* Nederlands */
        $this->educationLevelUuid = EducationLevel::filtered(['user_id' => $this->teacherOne->getKey()])->first()->uuid; /* VWO */
        $this->testKindUuid = TestKind::orderBy('name')->first()->uuid; /* Formatief */
    }

    /** @test */
    public function can_open_upload_test_page_with_livewire_component_as_teacher()
    {
        $this->actingAs($this->teacherOne)
            ->get(route('teacher.upload-tests'))
            ->assertSuccessful()
            ->assertSeeLivewire(UploadTest::class);
    }

    /** @test */
    public function cannot_open_upload_test_page_with_livewire_component_as_student()
    {
        $this->actingAs(self::getStudentOne())
            ->get(route('teacher.upload-tests'))
            ->assertRedirect();
    }

    /** @test */
    public function can_process_1_uploaded_file()
    {
        $this->actingAs($this->teacherOne);
        $testName = 'Hogere kaaskundigheid 101';

        Storage::fake('test_uploads');

        $file = UploadedFile::fake()->create('test_pdf.pdf');

        $component = $this->getTestableLivewire()
            ->set('testInfo.name', $testName)
            ->set('uploads', [$file])
            ->call('finishProcess')
            ->assertEmitted('openModal', 'teacher.upload-test-success-modal');

        $parent = FileManagement::whereName($testName)->first();
        $childName = $parent->children()->first()->name;

        $filePath = sprintf("%s/%s", $this->teacherOne->school_location_id, $childName);

        Storage::disk('test_uploads')->assertExists($filePath);
    }

    /** @test */
    public function can_process_multiple_uploaded_files()
    {
        $this->actingAs($this->teacherOne);
        $testName = 'Hogere kaaskundigheid 101';

        Storage::fake('test_uploads');

        $file = UploadedFile::fake()->create('test_pdf.pdf');
        $file2 = UploadedFile::fake()->create('test_pdf2.pdf');

        $component = $this->getTestableLivewire()
            ->set('testInfo.name', $testName)
            ->set('uploads', [$file, $file2])
            ->call('finishProcess')
            ->assertEmitted('openModal', 'teacher.upload-test-success-modal');

        $parent = FileManagement::whereName($testName)->with('children')->first();

        $parent->children->each(function ($child) {
            $this->assertEquals($child->subject()->first()->getKey(), Subject::whereUuid($this->subjectUuid)->first()->getKey());
            $filePath = sprintf("%s/%s", $this->teacherOne->school_location_id, $child->name);
            Storage::disk('test_uploads')->assertExists($filePath);
        });
    }

    /** @test */
    public function cannot_process_1_uploaded_file_if_it_is_too_large()
    {
        $this->actingAs($this->teacherOne);
        $testName = 'Hogere kaaskundigheid 101';
        $maxUploadSizeInKiloBytes = 64000;

        $file = UploadedFile::fake()->create('test_pdf.pdf', $maxUploadSizeInKiloBytes + 1);

        $component = Livewire::test(UploadTest::class)
            ->set('testInfo.name', $testName)
            ->set('uploads', [$file])
            ->assertHasErrors('uploads.0');
    }

    /** @test */
    public function cannot_process_multiple_uploaded_files_if_one_is_too_large()
    {
        $this->actingAs($this->teacherOne);
        $testName = 'Hogere kaaskundigheid 101';

        $component = Livewire::test(UploadTest::class);
        $maxUploadSizeInKiloBytes = $component->get('uploadRules.size.data');

        $file2 = UploadedFile::fake()->create('test_pdf2.pdf');
        $file = UploadedFile::fake()->create('test_pdf.pdf', $maxUploadSizeInKiloBytes + 1);

        $component->set('testInfo.name', $testName)
            ->set('uploads', [$file, $file2])
            ->assertHasErrors('uploads.0');
    }

    /** @test */
    public function can_reset_form_uuid_after_upload_processing()
    {
        $this->actingAs($this->teacherOne);
        $testName = 'Hogere kaaskundigheid 101';

        Storage::fake('test_uploads');
        $file = UploadedFile::fake()->create('test_pdf.pdf');

        $component = $this->getTestableLivewire()
            ->set('testInfo.name', $testName)
            ->set('uploads', [$file]);

        $oldFormUuid = $component->get('formUuid');

        $component->call('finishProcess');

        $newFormUuid = $component->get('formUuid');

        $this->assertNotEquals($oldFormUuid, $newFormUuid);
    }

    /** @test */
    public function can_fill_contains_publisher_content_column_is_true_in_database()
    {
        $this->actingAs($this->teacherOne);
        $testName = 'Hogere kaaskundigheid 101';

        Storage::fake('test_uploads');
        $file = UploadedFile::fake()->create('test_pdf.pdf');

        $component = $this->getTestableLivewire()
            ->set('testInfo.name', $testName)
            ->set('uploads', [$file])
            ->call('finishProcess');

        $parent = FileManagement::whereName($testName)->with('children')->first();

        $this->assertTrue($parent->contains_publisher_content);
    }

    /** @test */
    public function can_fill_contains_publisher_content_column_is_false_in_database()
    {
        $this->actingAs($this->teacherOne);
        $testName = 'Hogere kaaskundigheid 101';

        Storage::fake('test_uploads');
        $file = UploadedFile::fake()->create('test_pdf.pdf');

        $component = $this->getTestableLivewire()
            ->set('testInfo.name', $testName)
            ->set('testInfo.contains_publisher_content', false)
            ->set('uploads', [$file])
            ->call('finishProcess');

        $parent = FileManagement::whereName($testName)->with('children')->first();

        $this->assertFalse($parent->contains_publisher_content);
    }

    /** @test */
    public function can_set_default_status_of_provided_for_new_file_management()
    {
        $this->actingAs($this->teacherOne);
        $testName = 'Hogere kaaskundigheid 101';

        Storage::fake('test_uploads');
        $file = UploadedFile::fake()->create('test_pdf.pdf');

        $component = $this->getTestableLivewire()
            ->set('testInfo.name', $testName)
            ->set('uploads', [$file])
            ->call('finishProcess');
        $fileManagementStatusId = FileManagement::whereName($testName)->first()->value('file_management_status_id');
        $this->assertEquals(FileManagementStatus::STATUS_PROVIDED, $fileManagementStatusId);
    }

    /**
     * @return \Livewire\Testing\TestableLivewire
     */
    private function getTestableLivewire(): \Livewire\Testing\TestableLivewire
    {
        $dummyData = collect([
            'testInfo.name'                       => 'Lekker uploaden!',
            'testInfo.subject_uuid'               => $this->subjectUuid,
            'testInfo.education_level_uuid'       => $this->educationLevelUuid,
            'testInfo.education_level_year'       => 3,
            'testInfo.test_kind_uuid'             => $this->testKindUuid,
            'testInfo.contains_publisher_content' => true,
            'checkInfo.question_model'            => true,
            'checkInfo.answer_model'              => true,
            'checkInfo.attachments'               => true,
            'checkInfo.elaboration_attachments'   => true,
        ]);

        $component = Livewire::test(UploadTest::class);

        $dummyData->each(function ($value, $key) use ($component) {
            $component->set($key, $value);
        });

        return $component;
    }

    /** @test */
    public function can_not_use_test_uploader_when_in_temp_school()
    {
        $this->actingAs(self::getTeacherOne());

        $component = $this->getTestableLivewire()
            ->call('handleUploadPermissionsForUser')
            ->assertEmitted('openModal', 'teacher.upload-test-not-allowed-modal');
    }

    /** @test */
    public function can_reset_test_info_when_choosing_to_upload_another_test_without_copying_data()
    {
        $this->actingAs($this->teacherOne);

        $component = $this->getTestableLivewire();
        $component->assertSet('testInfo.name', 'Lekker uploaden!');

        $component->call('uploadAnotherTest', false)
            ->assertSet('testInfo.name', '')
            ->assertNotEmitted('openModal', 'teacher.upload-test-success-modal');
    }

    /** @test */
    public function can_keep_test_info_when_choosing_to_upload_another_test_with_existing_data()
    {
        $this->actingAs($this->teacherOne);

        $this->getTestableLivewire()
            ->assertSet('testInfo.name', 'Lekker uploaden!')
            ->call('uploadAnotherTest', true)
            ->assertSet('testInfo.name', 'Lekker uploaden!')
            ->assertNotEmitted('openModal', 'teacher.upload-test-success-modal');
    }

    /** @test */
    public function cannot_upload_a_subsequent_test_with_the_same_name()
    {
        $this->getTestableLivewire()
            ->set('testInfo.name', 'Coole naam')
            ->call('uploadAnotherTest', true)
            ->assertSet('testInfo.name', 'Coole naam')
            ->call('finishProcess')
            ->assertHasErrors('name')
            ->set('testInfo.name', 'Nieuwe naam!')
            ->call('finishProcess')
            ->assertHasNoErrors('name');
    }

    /** @test */
    public function can_process_a_subsequent_upload_request()
    {
        $this->actingAs($this->teacherOne);
        $testName = 'subsequent test name';
        $secondTestName = $testName . '2';

        Storage::fake('test_uploads');

        $file = UploadedFile::fake()->create('test_pdf.pdf');
        $newFile = UploadedFile::fake()->create('test_pdf2.pdf');

        $this->getTestableLivewire()
            ->set('testInfo.name', $testName)
            ->set('uploads', [$file])
            ->call('uploadAnotherTest', true)
            ->set('testInfo.name', $secondTestName)
            ->assertSet('uploads', [])
            ->set('uploads', [$newFile])
            ->call('finishProcess');


        $parent = FileManagement::whereName($testName)->with('children')->first();
        $parent->children->each(function ($child) {
            $this->assertEquals($child->subject()->first()->getKey(), Subject::whereUuid($this->subjectUuid)->first()->getKey());
            $filePath = sprintf("%s/%s", $this->teacherOne->school_location_id, $child->name);
            Storage::disk('test_uploads')->assertExists($filePath);
        });

        $secondParent = FileManagement::whereName($secondTestName)->with('children')->first();
        $secondParent->children->each(function ($child) {
            $this->assertEquals(
                $child->subject()->first()->getKey(),
                Subject::whereUuid($this->subjectUuid)->first()->getKey()
            );
            $filePath = sprintf("%s/%s", $this->teacherOne->school_location_id, $child->name);
            Storage::disk('test_uploads')->assertExists($filePath);
        });

        $this->assertNotEquals($parent->getKey(), $secondParent->getKey());
        $this->assertEquals(
            [
                $parent->subject_id,
                $parent->education_level_id,
                $parent->education_level_year,
            ],
            [
                $secondParent->subject_id,
                $secondParent->education_level_id,
                $secondParent->education_level_year,
            ]);
    }
}