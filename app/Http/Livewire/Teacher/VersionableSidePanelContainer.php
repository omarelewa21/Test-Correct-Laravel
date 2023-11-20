<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Contracts\View\View;
use tcCore\Http\Livewire\TCComponent;

class VersionableSidePanelContainer extends TCComponent
{
    public array $sliderButtonOptions = [];
    public bool $sliderButtonDisabled = false;
    public string $sliderButtonSelected = 'lists';
    public bool $showSliderButtons = true;
    public bool $closeOnFirstAdd = false;

    protected function getListeners(): array
    {
        return [
            'newAttributes' => 'handleUpdatedAttributes'
        ];
    }

    public function mount(): void
    {
        $this->setSliderButtonOptions();
    }

    public function render(): View
    {
        return view('livewire.teacher.versionable-side-panel-container');
    }

    private function setSliderButtonOptions()
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

        foreach ($event['attributes'] as $attribute => $value) {
            if (property_exists($this, $attribute)) {
                $this->$attribute = $value;
            }
        }
    }
}
