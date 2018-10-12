<?php namespace tcCore\Lib\Question;

class Factory {

    public static function makeQuestion($type) {
        $class = 'tcCore\\'.$type;
        $instance = null;
        if (class_exists($class)) {
            $instance = new $class();
        }

        if (!$instance instanceof \tcCore\Question){
            return false;
        }

        return $instance;
    }
}