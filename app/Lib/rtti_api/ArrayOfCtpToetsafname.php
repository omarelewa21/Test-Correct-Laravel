<?php

class ArrayOfCtpToetsafname implements \ArrayAccess, \Iterator, \Countable
{

    /**
     * @var ctpToetsafname[] $toetsafname
     */
    protected $toetsafname = null;

    
    public function __construct()
    {
    
    }

    /**
     * @return ctpToetsafname[]
     */
    public function getToetsafname()
    {
      return $this->toetsafname;
    }

    /**
     * @param ctpToetsafname[] $toetsafname
     * @return ArrayOfCtpToetsafname
     */
    public function setToetsafname(array $toetsafname = null)
    {
      $this->toetsafname = $toetsafname;
      return $this;
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset An offset to check for
     * @return boolean true on success or false on failure
     */
    public function offsetExists($offset)
    {
      return isset($this->toetsafname[$offset]);
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to retrieve
     * @return ctpToetsafname
     */
    public function offsetGet($offset)
    {
      return $this->toetsafname[$offset];
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to assign the value to
     * @param ctpToetsafname $value The value to set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
      if (!isset($offset)) {
        $this->toetsafname[] = $value;
      } else {
        $this->toetsafname[$offset] = $value;
      }
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to unset
     * @return void
     */
    public function offsetUnset($offset)
    {
      unset($this->toetsafname[$offset]);
    }

    /**
     * Iterator implementation
     *
     * @return ctpToetsafname Return the current element
     */
    public function current()
    {
      return current($this->toetsafname);
    }

    /**
     * Iterator implementation
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
      next($this->toetsafname);
    }

    /**
     * Iterator implementation
     *
     * @return string|null Return the key of the current element or null
     */
    public function key()
    {
      return key($this->toetsafname);
    }

    /**
     * Iterator implementation
     *
     * @return boolean Return the validity of the current position
     */
    public function valid()
    {
      return $this->key() !== null;
    }

    /**
     * Iterator implementation
     * Rewind the Iterator to the first element
     *
     * @return void
     */
    public function rewind()
    {
      reset($this->toetsafname);
    }

    /**
     * Countable implementation
     *
     * @return ctpToetsafname Return count of elements
     */
    public function count()
    {
      return count($this->toetsafname);
    }

}
