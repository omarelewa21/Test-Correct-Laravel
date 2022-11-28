<?php

namespace tcCore\Http\Livewire\Teacher\Analyses;

use Livewire\Component;
use tcCore\EducationLevel;
use tcCore\Lib\Repositories\PValueTaxonomyBloomRepository;
use tcCore\Lib\Repositories\PValueTaxonomyMillerRepository;
use tcCore\Lib\Repositories\PValueTaxonomyRTTIRepository;
use tcCore\Period;
use tcCore\User;
use tcCore\Http\Livewire\Student\Analyses\AnalysesDashboard as StudentAnalysesDashboard;

abstract class AnalysesDashboard extends StudentAnalysesDashboard
{
    const FILTER_SESSION_KEY = 'TEACHER_ANALYSES_FILTER';


}
