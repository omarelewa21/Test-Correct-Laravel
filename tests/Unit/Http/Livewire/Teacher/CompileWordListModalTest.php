<?php

namespace Tests\Unit\Http\Livewire\Teacher;

use Illuminate\Http\UploadedFile;
use Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use tcCore\Factories\FactoryTest;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimpleWithTest;
use tcCore\Http\Livewire\StudentPlayer\RelationQuestion;
use tcCore\Http\Livewire\Teacher\Cms\CompileWordListModal;
use tcCore\Imports\WordsImport;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\WordList;
use Tests\TestCase;

class CompileWordListModalTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimpleWithTest::class;
    private const VALID_FILE = 'import_wordlist.xlsx';
    private const INVALID_FILE = 'invalid_import_wordlist.xlsx';

    /** @test
     * @dataProvider validFileTypes
     */
    public function can_receive_xlsx_upload($type)
    {
        $uploadedExcel = UploadedFile::fake()->createWithContent(
            'import.' . $type,
            'temp file'
        );

        $property = Livewire::test(CompileWordListModal::class, ['wordData' => [], 'testUuid' => ''])
            ->assertSet('importFile', null)
            ->set('importFile', $uploadedExcel)
            ->assertHasNoErrors(['importFile'])
            ->get('importFile');

        $this->assertNotNull($property);
    }


    /** @test
     * @dataProvider invalidFileTypes
     */
    public function cannot_receive_other_types_than_xlsx_or_xls_upload($type)
    {
        $uploadedExcel = UploadedFile::fake()->createWithContent(
            'import.' . $type,
            'temp file'
        );

        $property = Livewire::test(CompileWordListModal::class, ['wordData' => [], 'testUuid' => ''])
            ->assertSet('importFile', null)
            ->set('importFile', $uploadedExcel)
            ->assertHasErrors(['importFile'])
            ->get('importFile');

        $this->assertNull($property);
    }

    /** @test */
    public function can_import_words_from_excel_file_into_a_new_list()
    {
        $this->actingAs(Teacher::first()->user);
        $test = Test::first();
        $uploadedExcel = $this->getWordListFile();
        $component = Livewire::test(CompileWordListModal::class, ['wordData' => [], 'testUuid' => $test->uuid]);

        $listUuids = $component->get('wordListUuids');
        $updatedListUuids = $component
            ->set('importFile', $uploadedExcel)
            ->call('importIntoList', true)
            ->get('wordListUuids');

        $this->assertEmpty($listUuids);
        $this->assertNotEmpty($updatedListUuids);
    }

    /** @test */
    public function can_convert_excel_to_words()
    {
        $importer = new WordsImport();

        Excel::import(
            $importer,
            $this->getWordListFile(),
        );

        $extracted = $this->extractedWordsFromExcel();
        $imported = $importer->getWordList();
        for ($i = 0; $i < count($importer->getWordList()); $i++) {
            $this->assertEquals($extracted[$i][0], $imported[$i]['subject']['text']);
            $this->assertEquals($extracted[$i][1], $imported[$i]['translation']['text']);
        }
    }

    /** @test */
    public function can_return_errors_with_incomplete_excel_file()
    {
        $test = Test::first();
        Livewire::test(CompileWordListModal::class, ['wordData' => [], 'testUuid' => $test->uuid])
            ->set('importFile', $this->getWordListFile(false))
            ->call('importIntoList', true)
            ->assertHasErrors('import_empty_values');
    }

    /** @test */
    public function can_import_excel_file_and_place_words_in_correct_column()
    {
        $test = Test::first();
        Livewire::test(CompileWordListModal::class, ['wordData' => [], 'testUuid' => $test->uuid])
            ->set('importFile', $this->getWordListFile(false, 'import_wordlist_empty_column_between.xlsx'))
            ->call('importIntoList', true)
            ->assertHasErrors('import_empty_values');
    }

    /** @test */
    public function can_delete_newly_created_list_when_no_words_were_created()
    {
        $test = Test::first();

        $beforeCount = WordList::count();
        $component = Livewire::test(CompileWordListModal::class, ['wordData' => [], 'testUuid' => $test->uuid]);

        $component->call('createNewList');
        $this->assertGreaterThan($beforeCount, WordList::count());

        $component->call('hydrateWordListUuids')
            ->call('close');
        $this->assertEquals($beforeCount, WordList::count());
    }

    public static function validFileTypes(): array
    {
        return [
            ['xls'],
            ['xlsx'],
        ];
    }

    public static function invalidFileTypes(): array
    {
        return [
            ['docx'],
            ['jpg'],
            ['png'],
            ['csv'],
            ['pdf'],
        ];
    }

    public static function extractedWordsFromExcel(): array
    {
        return [
            ['area', 'gebied'],
            ['book', 'boek'],
            ['business', 'bedrijf'],
            ['case', 'geval'],
            ['child', 'kind'],
            ['company', 'bedrijf'],
            ['country', 'land'],
            ['day', 'dag'],
            ['eye', 'oog'],
            ['fact', 'feit'],
            ['family', 'familie'],
            ['government', 'regering'],
            ['group', 'groep'],
            ['hand', 'hand'],
            ['home', 'thuis'],
            ['job', 'functie'],
            ['life', 'leven'],
            ['lot', 'kavel'],
            ['man', 'man'],
            ['money', 'geld'],
            ['month', 'maand'],
            ['mother', 'moeder'],
            ['Mr', 'Dhr'],
            ['night', 'nacht'],
            ['number', 'nummer'],
            ['part', 'deel'],
            ['people', 'mensen'],
            ['place', 'plaats'],
            ['point', 'punt'],
            ['problem', 'probleem'],
            ['program', 'programma'],
            ['question', 'vraag'],
            ['right', 'rechts'],
            ['room', 'kamer'],
            ['school', 'school'],
            ['state', 'staat'],
            ['story', 'verhaal'],
            ['student', 'student'],
            ['study', 'studie'],
            ['system', 'systeem'],
            ['thing', 'ding'],
            ['time', 'tijd'],
            ['water', 'water'],
            ['way', 'manier'],
            ['week', 'week'],
            ['woman', 'vrouw'],
            ['word', 'woord'],
            ['work', 'werk'],
            ['world', 'wereld'],
            ['year', 'jaar']
        ];
    }

    private function getWordListFile(bool $valid = true, string $fileName = ''): UploadedFile
    {
        if (!$fileName) {
            $fileName = $valid ? self::VALID_FILE : self::INVALID_FILE;
        }
        return UploadedFile::fake()->createWithContent(
            $fileName,
            file_get_contents(base_path('/tests/files/' . $fileName))
        );
    }
}
