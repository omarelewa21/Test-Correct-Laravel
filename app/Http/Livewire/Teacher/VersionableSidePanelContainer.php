<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Livewire\TCComponent;
use tcCore\WordList;

class VersionableSidePanelContainer extends TCComponent
{
    public array $sliderButtonOptions = [];
    public bool $sliderButtonDisabled = false;
    public string $sliderButtonSelected = 'lists';
    public bool $showSliderButtons = true;
    public bool $closeOnFirstAdd = false;
    public string $listUuid = '';
    public array $used = [];
    public string $currentListName = '';

    protected function getListeners(): array
    {
        return [
            'newAttributes' => 'handleUpdatedAttributes'
        ];
    }

    public function mount(): void
    {
        $this->setSliderButtonOptions();
        $this->setCurrentListName();
    }

    public function render(): View
    {
        return view('livewire.teacher.versionable-side-panel-container');
    }

    private function setSliderButtonOptions(): void
    {
        $this->sliderButtonOptions = [
            'lists' => __('cms.Woordenlijstenbank'),
            'words' => __('cms.Woordenbank'),
        ];
    }


    public function handleUpdatedAttributes($event): void
    {
        if (!isset($event['attributes'])) {
            return;
        }

        $shouldNotifyChildrenProperties = [
            WordsOverview::class     => ['used.words'],
            WordListsOverview::class => ['used.lists'],
        ];

        foreach ($shouldNotifyChildrenProperties as $class => $props) {
            $updates = array_filter(Arr::only($event['attributes'], $props));

            if (!empty($updates)) {
                $this->emitTo($class, 'usedPropertiesUpdated', $updates);
            }
        }

        foreach ($event['attributes'] as $attribute => $value) {
            if (property_exists($this, $attribute)) {
                $this->$attribute = $value;
            }
        }

        $this->setCurrentListName();
    }

    private function setCurrentListName(): void
    {
        $this->currentListName = '';

        if (Uuid::isValid($this->listUuid)) {
            $this->currentListName = WordList::whereUuid($this->listUuid)->value('name');
        }
    }
}
