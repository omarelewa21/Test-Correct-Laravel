<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;

class CmsInfoScreen
{
    private $instance;

    public function __construct(OpenShort $instance) {
        $this->instance = $instance;
    }

    public function preparePropertyBag(){
        $questionOptions = [
            'add_to_database'        => 0,
            'decimal_score'          => 0,
            'discuss'                => 0,
            "is_open_source_content" => 0,
            'note_type'              => 'NONE',
            'score'                  => 0,
            'all_or_nothing'         => false,
        ];
        foreach ($questionOptions as $key => $value) {
            $this->instance->question[$key] = $value;
        }

    }


    public function getTranslationKey() {
        return __('cms.infoscreen-question');
    }



}
