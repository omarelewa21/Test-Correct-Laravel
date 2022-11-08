<?php

namespace tcCore\Http\Traits;

use Illuminate\Support\Str;

trait WithQueryStringSyncing
{
    private bool $shouldEmitQueryStringChange = true;
    public $originalQueryString;

    public $isChild = true;

    protected function getListeners()
    {
        return $this->listeners + collect($this->queryString)->mapWithKeys(function ($value, $key) {
            if (is_array($value)) {
                $value = $key;
            }
            return [$this->getListenEventName($value) => 'queryStringChange'];
        })->toArray();
    }

    public function bootedWithQueryStringSyncing()
    {
        $this->originalQueryString = $this->setOriginalQueryString();
    }

    public function renderingWithQueryStringSyncing()
    {
        $diffQueryString = array_diff($this->setOriginalQueryString(), $this->originalQueryString);
        if (!empty($diffQueryString) && $this->shouldEmitQueryStringChange) {
            collect($diffQueryString)->each(function ($value, $key) {

                $this->emit($this->getChangeEventName($key), $key, $value);
            });
        }
    }

    private function setOriginalQueryString()
    {
        $originalQueryString = [];

        foreach ($this->queryString as $key => $query) {
            if (is_array($query)) {
                $originalQueryString[$key] = $this->$key;
                continue;
            }
            $originalQueryString[$query] = $this->$query;

        }
        return $originalQueryString;
    }

    public function queryStringChange($attribute, $value)
    {
        $this->shouldEmitQueryStringChange = !$this->isChild;
        if (property_exists($this, $attribute)) {
            $this->$attribute = $value;
        }
    }

    private function getListenEventName($attribute)
    {
        $methodPrefix = $this->isChild ? 'child' : 'parent';
        return $methodPrefix . 'QueryStringChange' . Str::upper($attribute);
    }

    private function getChangeEventName($attribute)
    {
        $methodPrefix = $this->isChild ? 'parent' : 'child';
        return $methodPrefix . 'QueryStringChange' . Str::upper($attribute);
    }
}