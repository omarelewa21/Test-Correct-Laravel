<?php

namespace Tests\Unit\Http\Livewire\Teacher;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use tcCore\FileManagement;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Livewire\Teacher\UploadTest;
use Tests\TestCase;

class UploadTestTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function can_open_upload_test_page_with_livewire_component_as_teacher()
    {
        $this->actingAs(self::getTeacherOne())
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
        $this->actingAs(self::getTeacherOne());
        $testName = 'Hogere kaaskundigheid 101';

        Storage::fake('test_uploads');

        $file = UploadedFile::fake()->create('test_pdf.pdf');

        $component = Livewire::test(UploadTest::class)
            ->set('testInfo.name', $testName)
            ->set('uploads', [$file])
            ->call('finishProcess')
            ->assertEmitted('openModal', 'teacher.upload-test-success-modal');

        $parent = FileManagement::whereName($testName)->first();
        $childName = $parent->children()->first()->name;

        $filePath = sprintf("%s/%s", self::getTeacherOne()->school_location_id, $childName);

        Storage::disk('test_uploads')->assertExists($filePath);
    }

    /** @test */
    public function can_process_multiple_uploaded_files()
    {
        $this->actingAs(self::getTeacherOne());
        $testName = 'Hogere kaaskundigheid 101';

        Storage::fake('test_uploads');

        $file = UploadedFile::fake()->create('test_pdf.pdf');
        $file2 = UploadedFile::fake()->create('test_pdf2.pdf');

        $component = Livewire::test(UploadTest::class)
            ->set('testInfo.name', $testName)
            ->set('uploads', [$file, $file2])
            ->call('finishProcess')
            ->assertEmitted('openModal', 'teacher.upload-test-success-modal');

        $parent = FileManagement::whereName($testName)->with('children')->first();

        $parent->children->each(function ($child) {
            $filePath = sprintf("%s/%s", self::getTeacherOne()->school_location_id, $child->name);
            Storage::disk('test_uploads')->assertExists($filePath);
        });
    }

    /** @test */
    public function cannot_process_1_uploaded_file_if_it_is_too_large()
    {
        $this->actingAs(self::getTeacherOne());
        $testName = 'Hogere kaaskundigheid 101';
        $maxUploadSizeInKiloBytes = 64000;

        $file = UploadedFile::fake()->create('test_pdf.pdf', $maxUploadSizeInKiloBytes+1);

        $component = Livewire::test(UploadTest::class)
            ->set('testInfo.name', $testName)
            ->set('uploads', [$file])
            ->assertHasErrors('uploads.0');
    }

    /** @test */
    public function cannot_process_multiple_uploaded_files_if_one_is_too_large()
    {
        $this->actingAs(self::getTeacherOne());
        $testName = 'Hogere kaaskundigheid 101';

        $component = Livewire::test(UploadTest::class);
        $maxUploadSizeInKiloBytes = $component->get('uploadRules.size.data');

        $file2 = UploadedFile::fake()->create('test_pdf2.pdf');
        $file = UploadedFile::fake()->create('test_pdf.pdf', $maxUploadSizeInKiloBytes+1);

        $component->set('testInfo.name', $testName)
            ->set('uploads', [$file, $file2])
            ->assertHasErrors('uploads.0');
    }

    /** @test */
    public function can_reset_form_uuid_after_upload_processing()
    {
        $this->actingAs(self::getTeacherOne());
        $testName = 'Hogere kaaskundigheid 101';

        Storage::fake('test_uploads');
        $file = UploadedFile::fake()->create('test_pdf.pdf');

        $component = Livewire::test(UploadTest::class)
            ->set('testInfo.name', $testName)
            ->set('uploads', [$file]);

        $oldFormUuid = $component->get('formUuid');

        $component->call('finishProcess');

        $newFormUuid = $component->get('formUuid');

        $this->assertNotEquals($oldFormUuid, $newFormUuid);
    }
}