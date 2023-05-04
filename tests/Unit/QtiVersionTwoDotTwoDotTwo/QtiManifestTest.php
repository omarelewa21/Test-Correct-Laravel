<?php

namespace Tests\Unit\QtiVersionTwoDotTwoDotTwo;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use tcCore\QtiModels\QtiManifest;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QtiManifestTest extends TestCase
{
    /** @test */
    public function it_has_a_collection_of_resources()
    {
        $instance = (new QtiManifest)->setOriginalXml($this->getXml());
        $this->assertInstanceOf(Collection::class, $instance->getResources());

        $hrefs = $instance->getResources()->map(function ($resource) {
            return $resource->href;
        });


        $requiredHrefs = [
            'depitems/330001.xml',
            'depitems/330008.xml',
            'depitems/330011.xml',
            'depitems/330016.xml',
            'depitems/330041.xml',
            'depitems/330065.xml',
            'depitems/330134.xml',
            'depitems/330160.xml',
            'depitems/330160 alt 1.xml',
            'depitems/330160 alt 2.xml',
            'depitems/330194.xml',
            'depitems/330222.xml',
            'depitems/Test item 370003a.xml',
            'depitems/Test item 370003b.xml',
            'depitems/Test item 370004.xml',
            'depitems/Test item 370005.xml',
            'depitems/Test item 370012a.xml',
            'depitems/Test item 370012b.xml',
            'depitems/Test item 370017a.xml',
            'depitems/Test item 370017b.xml',
            'depitems/Test item 370019a.xml',
            'depitems/Test item 370019b.xml',
            'depitems/Test item 370020a.xml',
            'depitems/Test item 370020b.xml',
            'depitems/testitem simpele formule editor voor invoer.xml',
            'depitems/testitem symbolen.xml',
        ];

        $this->assertCount(count($requiredHrefs), $instance->getResources());

        foreach ($requiredHrefs as $value) {
            $this->assertTrue($hrefs->contains($value));
        }
    }

    /** @test */
    public function it_has_a_collection_of_meta_data()
    {
        $instance = (new QtiManifest)->setOriginalXml($this->getXml());
        $this->assertInstanceOf(Collection::class, $instance->getMetaData());
        $this->assertEquals('IMS Content', $instance->getMetaDataItem('schema'));
        $this->assertEquals('2.2', $instance->getMetaDataItem('schemaversion'));
    }

    /** @test */
    public function it_can_read_xml_manifest()
    {
        $instance = new QtiManifest;
        $this->assertEmpty($instance->getOriginalXml());

        $instance->setOriginalXml($this->getXml());

        $this->assertNotEmpty($instance->getOriginalXml());
    }

    private function getXml($name = 'default')
    {
        $manifest = [
            'default' => __DIR__ . '/../../_fixtures_qti/Test-maatwerktoetsen_v01/imsmanifest.xml',
            'vmbo-economie' => __DIR__ . '/../../_fixtures_qti/economie-wiskundeA-niet-definitief/Economie-VMBO/economie-VMBO_pakket_test-correct_20200827-212636/imsmanifest.xml',
            'wiskunde' => __DIR__ . '/../../_fixtures_qti/economie-wiskundeA-niet-definitief/Wiskunde-A/wiskunde-A-pakket_test-correct_20200827-142449/imsmanifest.xml',
        ];


        if (array_key_exists($name, $manifest) && file_exists($manifest[$name])) {
            return file_get_contents(
                $manifest[$name]
            );
        }
        throw new \Exception(sprintf('cannot find manifest for key %s.', $name));

    }

    /** @test */
    public function it_can_return_a_array_with_title_and_other_metadata()
    {
        $instance = (new QtiManifest)->setOriginalXml($this->getXml());

        $this->assertEquals(
            [
                'id' => 'Test-maatwerktoetsen_v01',
                'name' => 'Test item 370004 - 1.8-04',
                'version' => '1',
                'guid' => 'bcb71360-c0ee-49d0-969e-cc4c786d0862',
                'testType' => 'test',
            ],
            $instance->getProperties()
        );
    }

    /** @test */
    public function it_can_return_a_name()
    {
        $instance = (new QtiManifest)->setOriginalXml($this->getXml());
        $this->assertEquals(
            'Test item 370004 - 1.8-04',
            $instance->getName()
        );
    }

    /** @test */
    public function it_can_load_wiskunde_manifest()
    {
        $instance = (new QtiManifest)->setOriginalXml($this->getXml('wiskunde'));
        $this->assertCount(155, $instance->getTestResourcesList());
    }

    /** @test */
    public function it_can_load_the_economie_manifest()
    {
        $instance = (new QtiManifest)->setOriginalXml($this->getXml('vmbo-economie'));

        $this->assertCount(95, $instance->getTestResourcesList());
    }

    /** @test */
    public function it_can_generate_a_test_name_list_with_resource_hrefs()
    {
        $instance = (new QtiManifest)->setOriginalXml($this->getXml('vmbo-economie'));
        $this->assertCount(19,
            $instance->getTestListWithResources()
        );

        // elke test heeft 5 items in zich;
        collect($instance->getTestListWithResources())->each(function($value, $key) {
            $this->assertCount(5, $value);
        });
    }
}
