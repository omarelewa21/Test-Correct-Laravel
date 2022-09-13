<?php

namespace tcCore\Lib\Repositories;

class PValueTaxonomyMillerRepository extends PValueTaxonomyRepository
{
    public const OPTIONS = [
        "Weten",
        "Weten hoe",
        "Laten zien",
        "Doen",
    ];
    public const DATABASE_FIELD = 'miller';

}