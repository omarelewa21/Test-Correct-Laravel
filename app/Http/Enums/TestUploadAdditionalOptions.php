<?php

namespace tcCore\Http\Enums;

enum TestUploadAdditionalOptions: int
{
    case None = 0;
    case Taxonomy = 1;
    case LearningGoals = 2;
    case Attainments = 3;
    case TaxonomyAndLearningGoals = 4;
    case TaxonomyAndAttainments = 5;
}
