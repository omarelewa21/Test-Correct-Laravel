<?php

namespace tcCore\Factories\Traits;

trait PropertyGetableByName
{
    public function getPropertyByName(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }
        throw new \Exception("Property that was tried to access does not exist. (property name: '{$name}')");
    }
    public function __get($name)
    {
        return $this->$name;
    }
    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}