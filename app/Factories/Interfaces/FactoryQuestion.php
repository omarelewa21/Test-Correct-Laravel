<?php

namespace tcCore\Factories\Interfaces;

use tcCore\Test;

Interface FactoryQuestion {

    public static function create();

    public function setProperties(array $properties);

    public function store();

    public function setTestModel(Test $testModel);

}