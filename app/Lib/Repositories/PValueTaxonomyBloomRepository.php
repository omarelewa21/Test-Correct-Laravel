<?php

namespace tcCore\Lib\Repositories;

class PValueTaxonomyBloomRepository extends PValueTaxonomyRepository
{
    public const OPTIONS = [
        "Onthouden",
        "Begrijpen",
        "Toepassen",
        "Analyseren",
        "Evalueren",
        "Creëren",
    ];
    public const DATABASE_FIELD = 'bloom';

}