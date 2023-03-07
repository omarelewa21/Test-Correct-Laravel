<?php

declare(strict_types=1);

final class php_Boot
{
    public function __construct()
    {
    }

    public static $qtypes;

    public static $ttypes;

    public static $tpaths;

    public static $skip_constructor = false;

    public function __toString()
    {
        return 'php.Boot';
    }
}

$_hx_class_prefix = null;

function _hx_add($a, $b)
{
    if ( ! _hx_is_numeric($a) || ! _hx_is_numeric($b)) {
        return $a.$b;
    } else {
        return $a + $b;
    }
}

function _hx_anonymous($arr = [])
{
    $o = new _hx_anonymous();
    foreach ($arr as $k => $v) {
        $o->$k = $v;
    }

    return $o;
}

final class _hx_array implements ArrayAccess, IteratorAggregate
{
    public $»a;

    public $length;

    public function __construct($a = [])
    {
        $this->»a = $a;
        $this->length = count($a);
    }

    public function concat($a)
    {
        return new _hx_array(array_merge($this->»a, $a->»a));
    }

    public function copy()
    {
        return new _hx_array($this->»a);
    }

    public function &get($index)
    {
        if (isset($this->»a[$index])) {
            return $this->»a[$index];
        }
    }

    public function insert($pos, $x): void
    {
        array_splice($this->»a, $pos, 0, [$x]);
        $this->length++;
    }

    public function iterator()
    {
        return new _hx_array_iterator($this->»a);
    }

    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return $this->iterator();
    }

    public function join($sep)
    {
        return implode($sep, $this->»a);
    }

    public function pop()
    {
        $r = array_pop($this->»a);
        $this->length = count($this->»a);

        return $r;
    }

    public function push($x)
    {
        $this->»a[] = $x;

        return ++$this->length;
    }

    public function remove($x)
    {
        for ($i = 0; $i < count($this->»a); $i++) {
            if ($this->»a[$i] === $x) {
                unset($this->»a[$i]);
                $this->»a = array_values($this->»a);
                $this->length--;

                return true;
            }
        }

        return false;
    }

    public function removeAt($pos)
    {
        if (array_key_exists($pos, $this->»a)) {
            unset($this->»a[$pos]);
            $this->length--;

            return true;
        } else {
            return false;
        }
    }

    public function reverse(): void
    {
        $this->»a = array_reverse($this->»a, false);
    }

    public function shift()
    {
        $r = array_shift($this->»a);
        $this->length = count($this->»a);

        return $r;
    }

    public function slice($pos, $end)
    {
        if (null === $end) {
            return new _hx_array(array_slice($this->»a, $pos));
        } else {
            return new _hx_array(array_slice($this->»a, $pos, $end - $pos));
        }
    }

    public function sort($f): void
    {
        usort($this->»a, $f);
    }

    public function splice($pos, $len)
    {
        if ($len < 0) {
            $len = 0;
        }
        $nh = new _hx_array(array_splice($this->»a, $pos, $len));
        $this->length = count($this->»a);

        return $nh;
    }

    public function toString()
    {
        return '['.implode(',', array_map('_hx_string_rec', $this->»a, [])).']';
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function unshift($x): void
    {
        array_unshift($this->»a, $x);
        $this->length++;
    }

    // ArrayAccess methods:
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->»a[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (isset($this->»a[$offset])) {
            return $this->»a[$offset];
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if ($this->length <= $offset) {
            $this->»a = array_merge($this->»a, array_fill(0, $offset + 1 - $this->length, null));
            $this->length = $offset + 1;
        }

        return $this->»a[$offset] = $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        return $this->removeAt($offset);
    }
}

final class _hx_array_iterator implements Iterator
{
    private $»a;

    private $»i;

    public function __construct($a)
    {
        $this->»a = $a;
        $this->»i = 0;
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        if ( ! $this->hasNext()) {
            return;
        }

        return $this->»a[$this->»i++];
    }

    public function hasNext()
    {
        return $this->»i < count($this->»a);
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        if ( ! $this->hasNext()) {
            return false;
        }

        return $this->»a[$this->»i];
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->»i;
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return false !== $this->current();
    }

    #[\ReturnTypeWillChange]
    public function rewind(): void
    {
        $this->»i = 0;
    }

    public function size()
    {
        return count($this->»a);
    }
}

function _hx_array_get($a, $pos)
{
    return $a[$pos];
}

function _hx_array_increment($a, $pos)
{
    return $a[$pos] += 1;
}
function _hx_array_decrement($a, $pos)
{
    return $a[$pos] -= 1;
}

function _hx_array_assign($a, $i, $v)
{
    return $a[$i] = $v;
}

final class _hx_break_exception extends Exception
{
}

function _hx_cast($v, $type)
{
    if (Std::is($v, $type)) {
        return $v;
    } else {
        throw new HException('Class cast error');
    }
}

function _hx_char_at($o, $i)
{
    $c = mb_substr($o, $i, 1);

    return false === $c ? '' : $c;
}

function _hx_char_code_at($s, $pos)
{
    if ($pos < 0 || $pos >= mb_strlen($s)) {
        return;
    }

    return ord($s[$pos]);
}

function _hx_deref($o)
{
    return $o;
}

function _hx_equal($x, $y)
{
    if (null === $x) {
        return null === $y;
    } else {
        if (null === $y) {
            return false;
        } else {
            if ((is_float($x) || is_int($x)) && (is_float($y) || is_int($y))) {
                return $x === $y;
            } else {
                return $x === $y;
            }
        }
    }
}

function _hx_mod($x, $y)
{
    if (is_int($x) && is_int($y)) {
        if (0 === $y) {
            return 0;
        }

        return $x % $y;
    }
    if ( ! is_nan($x) && ! is_nan($y) && ! is_finite($y) && is_finite($x)) {
        return $x;
    }

    return fmod($x, $y);
}

function _hx_error_handler($errno, $errmsg, $filename, $linenum, $vars = ''): void
{
    $msg = $errmsg.' (errno: '.$errno.') in '.$filename.' at line #'.$linenum;
    $e = new HException($msg, $errmsg, $errno, _hx_anonymous(['fileName' => 'Boot.hx', 'lineNumber' => __LINE__, 'className' => 'php.Boot', 'methodName' => '_hx_error_handler']));
    $e->setFile($filename);
    $e->setLine($linenum);
    throw $e;
}

function _hx_exception_handler($e): void
{
    if (0 === strncasecmp(PHP_SAPI, 'cli', 3)) {
        $msg = $e->getMessage();
        $nl = "\n";
        $pre = '';
        $post = '';
    } else {
        $msg = '<b>'.$e->getMessage().'</b>';
        $nl = '<br />';
        $pre = '<pre>';
        $post = '</pre>';
    }
    if (isset($GLOBALS['%s'])) {
        $stack = '';
        $i = $GLOBALS['%s']->length;
        while (--$i >= 0) {
            $stack .= 'Called from '.$GLOBALS['%s'][$i].$nl;
        }
        exit($pre.'uncaught exception: '.$msg.$nl.$nl.$stack.$post);
    } else {
        exit($pre.'uncaught exception: '.$msg.$nl.$nl.'in file: '.$e->getFile().' line '.$e->getLine().$nl.$e->getTraceAsString().$post);
    }
}

function _hx_explode($delimiter, $s)
{
    if ('' === $delimiter) {
        return new _hx_array(mb_str_split($s, 1));
    }

    return new _hx_array(explode($delimiter, $s));
}

function _hx_explode2($s, $delimiter)
{
    if ('' === $delimiter) {
        return new _hx_array(mb_str_split($s, 1));
    }

    return new _hx_array(explode($delimiter, $s));
}

function _hx_field($o, $field)
{
    if (_hx_has_field($o, $field)) {
        if ($o instanceof _hx_type) {
            if (is_callable($c = [$o->__tname__, $field]) && ! property_exists($o->__tname__, $field)) {
                return $c;
            } else {
                $name = $o->__tname__;

                return eval('return '.$name.'::$'.$field.';');
            }
        } else {
            if (is_string($o)) {
                if ('length' === $field) {
                    return mb_strlen($o);
                } else {
                    switch($field) {
                        case 'charAt': return [new _hx_lambda([&$o], '_hx_char_at'), 'execute'];
                        case 'charCodeAt': return [new _hx_lambda([&$o], '_hx_char_code_at'), 'execute'];
                        case 'indexOf': return [new _hx_lambda([&$o], '_hx_index_of'), 'execute'];
                        case 'lastIndexOf': return [new _hx_lambda([&$o], '_hx_last_index_of'), 'execute'];
                        case 'split': return [new _hx_lambda([&$o], '_hx_explode2'), 'execute'];
                        case 'substr': return [new _hx_lambda([&$o], '_hx_substr'), 'execute'];
                        case 'toUpperCase': return [new _hx_lambda([&$o], 'strtoupper'), 'execute'];
                        case 'toLowerCase': return [new _hx_lambda([&$o], 'strtolower'), 'execute'];
                        case 'toString': return [new _hx_lambda([&$o], '_hx_deref'), 'execute'];
                    }

                    return;
                }
            } else {
                if (property_exists($o, $field)) {
                    if (is_array($o->$field) && is_callable($o->$field)) {
                        return $o->$field;
                    } else {
                        if (is_string($o->$field) && _hx_is_lambda($o->$field)) {
                            return [$o, $field];
                        } else {
                            return $o->$field;
                        }
                    }
                } elseif (isset($o->»dynamics[$field])) {
                    return $o->»dynamics[$field];
                } else {
                    return [$o, $field];
                }
            }
        }
    } else {
        return;
    }
}

function _hx_get_object_vars($o)
{
    $a = array_keys(get_object_vars($o));
    if (isset($o->»dynamics)) {
        $a = array_merge($a, array_keys($o->»dynamics));
    }
    $arr = [];
    for ($i = 0; $i < count($a); $i++) {
        $k = ''.$a[$i];
        if ('»' !== mb_substr($k, 0, 1)) {
            $arr[] = $k;
        }
    }

    return $arr;
}

function _hx_has_field($o, $field)
{
    return
        (is_object($o) && (method_exists($o, $field) || isset($o->$field) || property_exists($o, $field) || isset($o->»dynamics[$field])))
        ||
        (is_string($o) && (in_array($field, ['toUpperCase', 'toLowerCase', 'charAt', 'charCodeAt', 'indexOf', 'lastIndexOf', 'split', 'substr', 'toString', 'length'])));
}

function _hx_index_of($s, $value, $startIndex = null)
{
    $startIndex ??= 0;
    $x = mb_strpos($s, $value, $startIndex);
    if (false === $x) {
        return -1;
    } else {
        return $x;
    }
}

function _hx_instanceof($v, $t)
{
    if (null === $t) {
        return false;
    }
    switch($t->__tname__) {
        case 'Array': return is_array($v);
        case 'String': return is_string($v) && ! _hx_is_lambda($v);
        case 'Bool': return is_bool($v);
        case 'Int': return is_int($v) || (is_float($v) && intval($v) === $v && ! is_nan($v));
        case 'Float': return is_float($v) || is_int($v);
        case 'Dynamic': return true;
        case 'Class': return ($v instanceof _hx_class || $v instanceof _hx_interface) && 'Enum' !== $v->__tname__;
        case 'Enum': return $v instanceof _hx_enum;
        default: return is_a($v, $t->__tname__);
    }
}

function _hx_is_lambda($s)
{
    return (is_string($s) && mb_substr($s, 0, 8) === chr(0).'lambda_') || (is_array($s) && count($s) > 0 && (is_a($s[0], '_hx_lambda') || is_a($s[0], '_hx_lambda2')));
}

function _hx_is_numeric($v)
{
    return is_numeric($v) && ! is_string($v);
}

function _hx_last_index_of($s, $value, $startIndex = null)
{
    $x = mb_strrpos($s, $value, null === $startIndex ? 0 : mb_strlen($s) - $startIndex);
    if (false === $x) {
        return -1;
    } else {
        return $x;
    }
}

function _hx_len($o)
{
    return is_string($o) ? mb_strlen($o) : $o->length;
}

final class _hx_list_iterator implements Iterator
{
    private $»h;

    private $»list;

    private $»counter;

    public function __construct($list)
    {
        $this->»list = $list;
        $this->rewind();
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        if (null === $this->»h) {
            return;
        }
        $this->»counter++;
        $x = $this->»h[0];
        $this->»h = $this->»h[1];

        return $x;
    }

    public function hasNext()
    {
        return null !== $this->»h;
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        if ( ! $this->hasNext()) {
            return;
        }

        return $this->»h[0];
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->»counter;
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return null !== $this->current();
    }

    #[\ReturnTypeWillChange]
    public function rewind(): void
    {
        $this->»counter = -1;
        $this->»h = $this->»list->h;
    }

    public function size()
    {
        return $this->»list->length;
    }
}

function _hx_null(): void
{
}

final class _hx_nullob
{
    public function _throw(): void
    {
        throw new HException('Null object');
    }

    public function __call($f, $a): void
    {
        $this->_throw();
    }

    public function __get($f): void
    {
        $this->_throw();
    }

    public function __set($f, $v): void
    {
        $this->_throw();
    }

    public function __isset($f)
    {
        $this->_throw();
    }

    public function __unset($f): void
    {
        $this->_throw();
    }

    public function __toString()
    {
        return 'null';
    }

    public static $inst;
}

_hx_nullob::$inst = new _hx_nullob();

function _hx_nullob()
{
    return _hx_nullob::$inst;
}

function _hx_qtype($n)
{
    return php_Boot::$qtypes[$n] ?? null;
}

function _hx_register_type($t): void
{
    php_Boot::$qtypes[$t->__qname__] = $t;
    php_Boot::$ttypes[$t->__tname__] = $t;
    if (null !== $t->__path__) {
        php_Boot::$tpaths[$t->__tname__] = $t->__path__;
    }
}

function _hx_set_method($o, $field, $func): void
{
    $value[0]->scope = $o;
    $o->$field = $func;
}

function _hx_shift_right($v, $n)
{
    $z = 0x80000000;
    if ($z & $v) {
        $v = ($v >> 1);
        $v &= (~$z);
        $v |= 0x40000000;
        $v = ($v >> ($n - 1));
    } else {
        $v = ($v >> $n);
    }

    return $v;
}

function _hx_string_call($s, $method, $params)
{
    if ( ! is_string($s)) {
        return call_user_func_array([$s, $method], $params);
    }
    switch($method) {
        case 'toUpperCase': return mb_strtoupper($s);
        case 'toLowerCase': return mb_strtolower($s);
        case 'charAt': return mb_substr($s, $params[0], 1);
        case 'charCodeAt': return _hx_char_code_at($s, $params[0]);
        case 'indexOf': return _hx_index_of($s, $params[0], (count($params) > 1 ? $params[1] : null));
        case 'lastIndexOf': return _hx_last_index_of($s, (count($params) > 1 ? $params[1] : null), null);
        case 'split': return _hx_explode($params[0], $s);
        case 'substr': return _hx_substr($s, $params[0], (count($params) > 1 ? $params[1] : null));
        case 'toString': return $s;
        default: throw new HException('Invalid Operation: '.$method);
    }
}

function _hx_string_rec($o, $s)
{
    if (null === $o) {
        return 'null';
    }
    if (mb_strlen($s) >= 5) {
        return '<...>';
    }
    if (is_int($o) || is_float($o)) {
        return ''.$o;
    }
    if (is_bool($o)) {
        return $o ? 'true' : 'false';
    }
    if (is_object($o)) {
        $c = get_class($o);
        if ($o instanceof Enum) {
            $b = $o->tag;
            if ( ! empty($o->params)) {
                $s .= '	';
                $b .= '(';
                for ($i = 0; $i < count($o->params); $i++) {
                    if ($i > 0) {
                        $b .= ','._hx_string_rec($o->params[$i], $s);
                    } else {
                        $b .= _hx_string_rec($o->params[$i], $s);
                    }
                }
                $b .= ')';
            }

            return $b;
        } else {
            if ($o instanceof _hx_anonymous) {
                if ($o->toString && is_callable($o->toString)) {
                    return call_user_func($o->toString);
                }
                $rfl = new ReflectionObject($o);
                $b2 = '{
';
                $s .= '	';
                $properties = $rfl->getProperties();

                for ($i = 0; $i < count($properties); $i++) {
                    $prop = $properties[$i];
                    $f = $prop->getName();
                    if ($i > 0) {
                        $b2 .= ', 
';
                    }
                    $b2 .= $s.$f.' : '._hx_string_rec($o->$f, $s);
                }
                $s = mb_substr($s, 1);
                $b2 .= '
'.$s.'}';

                return $b2;
            } else {
                if ($o instanceof _hx_type) {
                    return $o->__qname__;
                } else {
                    if (is_callable([$o, 'toString'])) {
                        return $o->toString();
                    } else {
                        if (is_callable([$o, '__toString'])) {
                            return $o->__toString();
                        } else {
                            return '['._hx_ttype($c).']';
                        }
                    }
                }
            }
        }
    }
    if (is_string($o)) {
        if (_hx_is_lambda($o)) {
            return '»function»';
        }
        //		if(strlen($s) > 0)    return '"' . str_replace('"', '\"', $o) . '"';
        else {
            return $o;
        }
    }
    if (is_array($o)) {
        if (is_callable($o)) {
            return '»function»';
        }
        $str = '[';
        $s .= '	';
        $first = true;
        $assoc = true;
        foreach ($o as $k => $v) {
            if ($first && 0 === $k) {
                $assoc = false;
            }
            $str .= ($first ? '' : ',').($assoc
                ? _hx_string_rec($k, $s).'=>'._hx_string_rec($o[$k], $s)
                : _hx_string_rec($o[$k], $s)
            );
            $first = false;
        }
        $str .= ']';

        return $str;
    }

    return '';
}

function _hx_substr($s, $pos, $len)
{
    if (null !== $pos && 0 !== $pos && null !== $len && $len < 0) {
        return '';
    }
    if (null === $len) {
        $len = mb_strlen($s);
    }
    if ($pos < 0) {
        $pos = mb_strlen($s) + $pos;
        if ($pos < 0) {
            $pos = 0;
        }
    } elseif ($len < 0) {
        $len = mb_strlen($s) + $len - $pos;
    }
    $s = mb_substr($s, $pos, $len);
    if (false === $s) {
        return '';
    } else {
        return $s;
    }
}

function _hx_trace($v, $i): void
{
    $msg = null !== $i ? $i->fileName.':'.$i->lineNumber.': ' : '';
    echo $msg._hx_string_rec($v, '').'
';
}

function _hx_ttype($n)
{
    return php_Boot::$ttypes[$n] ?? null;
}

function _hx_make_var_args()
{
    $args = func_get_args();
    $f = array_shift($args);

    return call_user_func($f, new _hx_array($args));
}

final class _hx_anonymous extends stdClass
{
    public function __call($m, $a)
    {
        return call_user_func_array($this->$m, $a);
    }

    public function __set($n, $v): void
    {
        $this->$n = $v;
    }

    public function &__get($n)
    {
        if (isset($this->$n)) {
            return $this->$n;
        }
        $null = null;

        return $null;
    }

    public function __isset($n)
    {
        return isset($this->$n);
    }

    public function __unset($n): void
    {
        unset($this->$n);
    }

    public function __toString()
    {
        $rfl = new ReflectionObject($this);
        $b = '{ ';
        $properties = $rfl->getProperties();
        $first = true;
        foreach ($properties as $prop) {
            if ($first) {
                $first = false;
            } else {
                $b .= ', ';
            }
            $f = $prop->getName();
            $b .= $f.' => '.$this->$f;
        }
        $b .= ' }';

        return $b;
    }
}

final class _hx_type
{
    public $__tname__;

    public $__qname__;

    public $__path__;

    public function __construct($cn, $qn, $path = null)
    {
        $this->__tname__ = $cn;
        $this->__qname__ = $qn;
        $this->__path__ = $path;
        if (property_exists($cn, '__meta__')) {
            $this->__meta__ = eval($cn.'::$__meta__');
        }
    }

    public function toString()
    {
        return $this->__toString();
    }

    public function __toString()
    {
        return $this->__qname__;
    }

    private $rfl = false;

    public function __rfl__()
    {
        if (false !== $this->rfl) {
            return $this->rfl;
        }
        if (class_exists($this->__tname__) || interface_exists($this->__tname__)) {
            $this->rfl = new ReflectionClass($this->__tname__);
        } else {
            $this->rfl = null;
        }

        return $this->rfl;
    }

    public function __call($n, $a)
    {
        return call_user_func_array([$this->__tname__, $n], $a);
    }

    public function __get($n)
    {
        if (($r = $this->__rfl__()) === null) {
            return;
        }
        if ($r->hasProperty($n)) {
            return $r->getStaticPropertyValue($n);
        } elseif ($r->hasMethod($n)) {
            return [$r, $n];
        } else {
            return;
        }
    }

    public function __set($n, $v)
    {
        if (($r = $this->__rfl__()) === null) {
            return;
        }

        return $r->setStaticPropertyValue($n, $v);
    }

    public function __isset($n)
    {
        if (($r = $this->__rfl__()) === null) {
            return;
        }

        return $r->hasProperty($n) || $r->hasMethod($n);
    }
}

final class _hx_class extends _hx_type
{
}

final class _hx_enum extends _hx_type
{
}

final class _hx_interface extends _hx_type
{
}

final class HException extends Exception
{
    public function __construct($e, $message = null, $code = 0, $p = null)
    {
        $message = _hx_string_rec($e, '').$message;
        parent::__construct($message, $code);
        $this->e = $e;
        $this->p = $p;
    }

    public $e;

    public $p;

    public function setLine($l): void
    {
        $this->line = $l;
    }

    public function setFile($f): void
    {
        $this->file = $f;
    }
}

final class _hx_lambda
{
    public function __construct($locals, $func)
    {
        $this->locals = $locals;
        $this->func = $func;
    }

    public $locals;

    public $func;

    public function execute()
    {
        // if use $this->locals directly in array_merge it works only if I make the assignement loop,
        // so I've decided to reference $arr
        $arr = [];
        for ($i = 0; $i < count($this->locals); $i++) {
            $arr[] = &$this->locals[$i];
        }
        $args = func_get_args();

        return call_user_func_array($this->func, array_merge($arr, $args));
    }
}

final class Enum
{
    public function __construct($tag, $index, $params = null)
    {
        $this->tag = $tag;
        $this->index = $index;
        $this->params = $params;
    }

    public $tag;

    public $index;

    public $params;

    public function __toString()
    {
        return $this->tag;
    }
}

error_reporting(E_ALL & ~E_STRICT);
set_error_handler('_hx_error_handler', E_ALL);
set_exception_handler('_hx_exception_handler');

php_Boot::$qtypes = [];
php_Boot::$ttypes = [];
php_Boot::$tpaths = [];

_hx_register_type(new _hx_class('String', 'String'));
_hx_register_type(new _hx_class('_hx_array', 'Array'));
_hx_register_type(new _hx_class('Int', 'Int'));
_hx_register_type(new _hx_class('Float', 'Float'));
_hx_register_type(new _hx_class('Class', 'Class'));
_hx_register_type(new _hx_class('Enum', 'Enum'));
_hx_register_type(new _hx_class('Dynamic', 'Dynamic'));
_hx_register_type(new _hx_enum('Bool', 'Bool'));
_hx_register_type(new _hx_enum('Void', 'Void'));

$_hx_libdir = dirname(__FILE__).'/..';
$_hx_autload_cache_file = $_hx_libdir.'/../cache/haxe_autoload.php';
if ( ! file_exists($_hx_autload_cache_file)) {
    function _hx_build_paths($d, &$_hx_types_array, $pack, $prefix): void
    {
        $h = opendir($d);
        while (false !== ($f = readdir($h))) {
            $p = $d.'/'.$f;
            if ('.' === $f || '..' === $f) {
                continue;
            }
            if (is_file($p) && '.php' === mb_substr($f, -4)) {
                $bn = basename($f, '.php');
                if ($prefix) {
                    if ($prefix !== mb_substr($bn, 0, $lenprefix = mb_strlen($prefix))) {
                        continue;
                    }
                    $bn = mb_substr($bn, $lenprefix);
                }
                if ('.class' === mb_substr($bn, -6)) {
                    $bn = mb_substr($bn, 0, -6);
                    $t = 0;
                } elseif ('.enum' === mb_substr($bn, -5)) {
                    $bn = mb_substr($bn, 0, -5);
                    $t = 1;
                } elseif ('.interface' === mb_substr($bn, -10)) {
                    $bn = mb_substr($bn, 0, -10);
                    $t = 2;
                } elseif ('.extern' === mb_substr($bn, -7)) {
                    $bn = mb_substr($bn, 0, -7);
                    $t = 3;
                } else {
                    continue;
                }
                $qname = ('HList' === $bn && empty($pack)) ? 'List' : implode('.', array_merge($pack, [$bn]));
                $_hx_types_array[] = [
                    'path'    => $p,
                    'name'    => $prefix.$bn,
                    'type'    => $t,
                    'qname'   => $qname,
                    'phpname' => implode('_', array_merge($pack, [$prefix.$bn])),
                ];
            } elseif (is_dir($p)) {
                _hx_build_paths($p, $_hx_types_array, array_merge($pack, [$f]), $prefix);
            }
        }
        closedir($h);
    }

    $_hx_cache_content = '<?php

';
    $_hx_types_array = [];

    _hx_build_paths($_hx_libdir, $_hx_types_array, [], $_hx_class_prefix);

    for ($i = 0; $i < count($_hx_types_array); $i++) {
        $_hx_cache_content .= '_hx_register_type(new ';
        $t = null;
        if (0 === $_hx_types_array[$i]['type']) {
            $t = new _hx_class($_hx_types_array[$i]['phpname'], $_hx_types_array[$i]['qname'], $_hx_types_array[$i]['path']);
            $_hx_cache_content .= '_hx_class';
        } elseif (1 === $_hx_types_array[$i]['type']) {
            $t = new _hx_enum($_hx_types_array[$i]['phpname'], $_hx_types_array[$i]['qname'], $_hx_types_array[$i]['path']);
            $_hx_cache_content .= '_hx_enum';
        } elseif (2 === $_hx_types_array[$i]['type']) {
            $t = new _hx_interface($_hx_types_array[$i]['phpname'], $_hx_types_array[$i]['qname'], $_hx_types_array[$i]['path']);
            $_hx_cache_content .= '_hx_interface';
        } elseif (3 === $_hx_types_array[$i]['type']) {
            $t = new _hx_class($_hx_types_array[$i]['name'], $_hx_types_array[$i]['qname'], $_hx_types_array[$i]['path']);
            $_hx_cache_content .= '_hx_class';
        }
        _hx_register_type($t);
        $_hx_cache_content .= '(\''.(3 === $_hx_types_array[$i]['type'] ? $_hx_types_array[$i]['name'] : $_hx_types_array[$i]['phpname']).'\', \''.$_hx_types_array[$i]['qname'].'\', \''.$_hx_types_array[$i]['path'].'\'));
';
    }
    try {
        file_put_contents($_hx_autload_cache_file, $_hx_cache_content);
    } catch(Exception $e) {
    }
    unset($_hx_types_array, $_hx_cache_content);
} else {
    require $_hx_autload_cache_file;
}

function _hx_autoload($name)
{
    if ( ! isset(php_Boot::$tpaths[$name])) {
        return false;
    }
    require_once php_Boot::$tpaths[$name];

    return true;
}

if ( ! ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

spl_autoload_register('_hx_autoload');
