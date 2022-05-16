<?php

namespace tcCore\Http\Traits;

use Illuminate\Support\Facades\Validator;

trait WithCmsCustomRulesHandling
{
    /**
     * @return bool
     * The instance data first needs to be restructured/'prepared for save'd before it can be validated correctly.
     * After it fails/passes it should be restructured back to be useable by the CMS.
     *
     * This is used to see if it passes the validation without actually validating it (dirty confirmation)
     * -RR
     */
    public function passesCustomMandatoryRules(): bool
    {
        $this->prepareForSave();
        $passedValidation = !(Validator::make((array)$this->instance, $this->instance->getRulesFromProvider())->fails());
        $this->createAnswerStruct();

        return $passedValidation;
    }
}