<?php

class Type
{
    public function __construct()
    {
    }

    public static function getClass($o)
    {
        if ($o === null) {
            return null;
        }
        if (is_array($o)) {
            if (count($o) === 2 && is_callable($o)) {
                return null;
            }

            return _hx_ttype('Array');
        }
        if (is_string($o)) {
            if (_hx_is_lambda($o)) {
                return null;
            }

            return _hx_ttype('String');
        }
        if (! is_object($o)) {
            return null;
        }
        $c = get_class($o);
        if ($c === false || $c === '_hx_anonymous' || is_subclass_of($c, 'enum')) {
            return null;
        } else {
            return _hx_ttype($c);
        }
    }

    public static function getEnum($o)
    {
        if (! $o instanceof Enum) {
            return null;
        } else {
            return _hx_ttype(get_class($o));
        }
    }

    public static function getSuperClass($c)
    {
        $s = get_parent_class($c->__tname__);
        if ($s === false) {
            return null;
        } else {
            return _hx_ttype($s);
        }
    }

    public static function getClassName($c)
    {
        if ($c === null) {
            return null;
        }

        return $c->__qname__;
    }

    public static function getEnumName($e)
    {
        return $e->__qname__;
    }

    public static function resolveClass($name)
    {
        $c = _hx_qtype($name);
        if ($c instanceof _hx_class || $c instanceof _hx_interface) {
            return $c;
        } else {
            return null;
        }
    }

    public static function resolveEnum($name)
    {
        $e = _hx_qtype($name);
        if ($e instanceof _hx_enum) {
            return $e;
        } else {
            return null;
        }
    }

    public static function createInstance($cl, $args)
    {
        if ($cl->__qname__ === 'Array') {
            return new _hx_array([]);
        }
        if ($cl->__qname__ === 'String') {
            return $args[0];
        }
        $c = $cl->__rfl__();
        if ($c === null) {
            return null;
        }

        return $inst = $c->getConstructor() ? $c->newInstanceArgs($args->»a) : $c->newInstanceArgs();
    }

    public static function createEmptyInstance($cl)
    {
        if ($cl->__qname__ === 'Array') {
            return new _hx_array([]);
        }
        if ($cl->__qname__ === 'String') {
            return '';
        }
        try {
            php_Boot::$skip_constructor = true;
            $rfl = $cl->__rfl__();
            if ($rfl === null) {
                return null;
            }
            $m = $rfl->getConstructor();
            $nargs = $m->getNumberOfRequiredParameters();
            $i = null;
            if ($nargs > 0) {
                $args = array_fill(0, $m->getNumberOfRequiredParameters(), null);
                $i = $rfl->newInstanceArgs($args);
            } else {
                $i = $rfl->newInstanceArgs([]);
            }
            php_Boot::$skip_constructor = false;

            return $i;
        } catch(Exception $�e) {
            $_ex_ = ($�e instanceof HException) ? $�e->e : $�e;
            $e = $_ex_;

            php_Boot::$skip_constructor = false;
            throw new HException('Unable to instantiate '.Std::string($cl));
        }

        return null;
    }

    public static function createEnum($e, $constr, $params = null)
    {
        $f = Reflect::field($e, $constr);
        if ($f === null) {
            throw new HException('No such constructor '.$constr);
        }
        if (Reflect::isFunction($f)) {
            if ($params === null) {
                throw new HException('Constructor '.$constr.' need parameters');
            }

            return Reflect::callMethod($e, $f, $params);
        }
        if ($params !== null && $params->length !== 0) {
            throw new HException('Constructor '.$constr.' does not need parameters');
        }

        return $f;
    }

    public static function createEnumIndex($e, $index, $params = null)
    {
        $c = _hx_array_get(Type::getEnumConstructs($e), $index);
        if ($c === null) {
            throw new HException(_hx_string_rec($index, '').' is not a valid enum constructor index');
        }

        return Type::createEnum($e, $c, $params);
    }

    public static function getInstanceFields($c)
    {
        if ($c->__qname__ === 'String') {
            return new _hx_array(['substr', 'charAt', 'charCodeAt', 'indexOf', 'lastIndexOf', 'split', 'toLowerCase', 'toUpperCase', 'toString', 'length']);
        }
        if ($c->__qname__ === 'Array') {
            return new _hx_array(['push', 'concat', 'join', 'pop', 'reverse', 'shift', 'slice', 'sort', 'splice', 'toString', 'copy', 'unshift', 'insert', 'remove', 'iterator', 'length']);
        }

        $rfl = $c->__rfl__();
        if ($rfl === null) {
            return new _hx_array([]);
        }
        $r = [];
        $internals = ['__construct', '__call', '__get', '__set', '__isset', '__unset', '__toString'];
        $ms = $rfl->getMethods();
        foreach ($ms as $m) {
            $n = $m->getName();
            if (! $m->isStatic() && ! in_array($n, $internals)) {
                $r[] = $n;
            }
        }
        $ps = $rfl->getProperties();
        foreach ($ps as $p) {
            if (! $p->isStatic()) {
                $r[] = $p->getName();
            }
        }

        return new _hx_array(array_values(array_unique($r)));
    }

    public static function getClassFields($c)
    {
        if ($c->__qname__ === 'String') {
            return new _hx_array(['fromCharCode']);
        }
        if ($c->__qname__ === 'Array') {
            return new _hx_array([]);
        }

        $rfl = $c->__rfl__();
        if ($rfl === null) {
            return new _hx_array([]);
        }
        $ms = $rfl->getMethods();
        $r = [];
        foreach ($ms as $m) {
            if ($m->isStatic()) {
                $r[] = $m->getName();
            }
        }
        $ps = $rfl->getProperties();
        foreach ($ps as $p) {
            if ($p->isStatic()) {
                $r[] = $p->getName();
            }
        }

        return new _hx_array(array_unique($r));
    }

    public static function getEnumConstructs($e)
    {
        if ($e->__tname__ == 'Bool') {
            return new _hx_array(['true', 'false']);
        }
        if ($e->__tname__ == 'Void') {
            return new _hx_array([]);
        }

        return new _hx_array($e->__constructors);
    }

    public static function typeof($v)
    {
        if ($v === null) {
            return ValueType::$TNull;
        }
        if (is_array($v)) {
            if (is_callable($v)) {
                return ValueType::$TFunction;
            }

            return ValueType::TClass(_hx_qtype('Array'));
        }
        if (is_string($v)) {
            if (_hx_is_lambda($v)) {
                return ValueType::$TFunction;
            }

            return ValueType::TClass(_hx_qtype('String'));
        }
        if (is_bool($v)) {
            return ValueType::$TBool;
        }
        if (is_int($v)) {
            return ValueType::$TInt;
        }
        if (is_float($v)) {
            return ValueType::$TFloat;
        }
        if ($v instanceof _hx_anonymous) {
            return ValueType::$TObject;
        }
        if ($v instanceof _hx_enum) {
            return ValueType::$TObject;
        }
        if ($v instanceof _hx_class) {
            return ValueType::$TObject;
        }
        $c = _hx_ttype(get_class($v));
        if ($c instanceof _hx_enum) {
            return ValueType::TEnum($c);
        }
        if ($c instanceof _hx_class) {
            return ValueType::TClass($c);
        }

        return ValueType::$TUnknown;
    }

    public static function enumEq($a, $b)
    {
        if ($a == $b) {
            return true;
        }
        try {
            if (! _hx_equal($a->index, $b->index)) {
                return false;
            }

            $_g1 = 0;
            $_g = count($a->params);
            while ($_g1 < $_g) {
                $i = $_g1++;
                if (Type::getEnum($a->params[$i]) !== null) {
                    if (! Type::enumEq($a->params[$i], $b->params[$i])) {
                        return false;
                    }
                } else {
                    if (! _hx_equal($a->params[$i], $b->params[$i])) {
                        return false;
                    }
                }
                unset($i);
            }
        } catch(Exception $�e) {
            $_ex_ = ($�e instanceof HException) ? $�e->e : $�e;
            $e = $_ex_;

            return false;
        }

        return true;
    }

    public static function enumConstructor($e)
    {
        return $e->tag;
    }

    public static function enumParameters($e)
    {
        if (_hx_field($e, 'params') === null) {
            return new _hx_array([]);
        } else {
            return new _hx_array($e->params);
        }
    }

    public static function enumIndex($e)
    {
        return $e->index;
    }

    public static function allEnums($e)
    {
        $all = new _hx_array([]);

        $_g = 0;
        $_g1 = Type::getEnumConstructs($e);
        while ($_g < $_g1->length) {
            $c = $_g1[$_g];
            $_g++;
            $v = Reflect::field($e, $c);
            if (! Reflect::isFunction($v)) {
                $all->push($v);
            }
            unset($v,$c);
        }

        return $all;
    }

    public function __toString()
    {
        return 'Type';
    }
}
