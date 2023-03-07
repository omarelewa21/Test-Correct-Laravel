<?php

declare(strict_types=1);

interface com_wiris_util_sys_AccessProvider
{
    public function isEnabled();

    public function requireAccess();
}
