<?php

namespace tcCore\Factories\Traits;

trait RandomCharactersGeneratable
{
    /**
     * Generate random characters, for making Factory names unique
     * @param int $amount
     * @return mixed
     */
    public function randomCharacters(int $amount = 1)
    {
        $characters = collect(array_merge(
            range(48, 57),
            range(65, 90),
            range(97, 122),
        ));

        return $characters->random($amount)->reduce(function ($carry, $ascii) {
            return $carry . chr($ascii);
        }, '');
    }
}