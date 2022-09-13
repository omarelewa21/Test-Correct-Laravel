<?php

namespace tcCore\Lib\Repositories;

class PValueTaxonomyRTTIRepository extends PValueTaxonomyRepository
{
    public const OPTIONS = [
        "R",
        "T1",
        "T2",
        "I",
    ];
    public const DATABASE_FIELD = 'rtti';

}