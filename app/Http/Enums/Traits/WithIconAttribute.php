<?php

namespace tcCore\Http\Enums\Traits;

use tcCore\Http\Enums\Attributes\Icon;

trait WithIconAttribute
{
    public function getIconComponentName()
    {
        $instance = self::getAttributeInstance($this, Icon::class);
        if (!$instance) {
            return null;
        }

        return $instance->iconName;
    }

}