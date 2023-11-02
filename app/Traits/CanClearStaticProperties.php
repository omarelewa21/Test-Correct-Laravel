<?php

namespace tcCore\Traits;

trait CanClearStaticProperties
{

    /**
     * Clear all cached questions to prevent leaking between Unit Tests
     * @return void
     */
    public static function invalidateAllCache()
    {
        $reflectionClass = new \ReflectionClass(static::class);

        $defaultProperties = $reflectionClass->getDefaultProperties();
        $staticProperties = array_keys($reflectionClass->getStaticProperties());


        foreach ($staticProperties as $staticProperty) {
            static::$$staticProperty = $defaultProperties[$staticProperty];
        }
    }
}