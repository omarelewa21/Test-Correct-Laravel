<?php

class ArrayOfCtpToetsOnderdeel implements \ArrayAccess, \Iterator, \Countable
{

    /**
     * @var ctpToetsOnderdeel[] $toetsonderdeel
     */
    protected $toetsonderdeel = null;

    
    public function __construct()
    {
    
    }

    /**
     * @return ctpToetsOnderdeel[]
     */
    public function getToetsonderdeel()
    {
      return $this->toetsonderdeel;
    }

    /**
     * @param ctpToetsOnderdeel[] $toetsonderdeel
     * @return ArrayOfCtpToetsOnderdeel
     */
    public function setToetsonderdeel(array $toetsonderdeel = null)
    {
      $this->toetsonderdeel = $toetsonderdeel;
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
      return isset($this->toetsonderdeel[$offset]);
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to retrieve
     * @return ctpToetsOnderdeel
     */
    public function offsetGet($offset)
    {
      return $this->toetsonderdeel[$offset];
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to assign the value to
     * @param ctpToetsOnderdeel $value The value to set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
      if (!isset($offset)) {
        $this->toetsonderdeel[] = $value;
      } else {
        $this->toetsonderdeel[$offset] = $value;
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
      unset($this->toetsonderdeel[$offset]);
    }

    /**
     * Iterator implementation
     *
     * @return ctpToetsOnderdeel Return the current element
     */
    public function current()
    {
      return current($this->toetsonderdeel);
    }

    /**
     * Iterator implementation
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
      next($this->toetsonderdeel);
    }

    /**
     * Iterator implementation
     *
     * @return string|null Return the key of the current element or null
     */
    public function key()
    {
      return key($this->toetsonderdeel);
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
      reset($this->toetsonderdeel);
    }

    /**
     * Countable implementation
     *
     * @return ctpToetsOnderdeel Return count of elements
     */
    public function count()
    {
      return count($this->toetsonderdeel);
    }

}
