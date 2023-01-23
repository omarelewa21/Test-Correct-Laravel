<?php

namespace tcCore\Traits;

use tcCore\Test;

trait UserPublishing
{
    public function isDraft(): bool
    {
        return $this->draft;
    }

    public function isPublished(): bool
    {
        return !$this->isDraft();
    }

    public function publish(): Test
    {
        $this->draft = false;
        return $this;
    }
}