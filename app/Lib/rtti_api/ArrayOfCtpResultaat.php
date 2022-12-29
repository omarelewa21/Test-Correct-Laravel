<?php

class ArrayOfCtpResultaat implements \ArrayAccess, \Iterator, \Countable
{

    /**
     * @var ctpResultaat[] $resultaat
     */
    protected $resultaat = null;

    
    public function __construct()
    {
    
    }

    /**
     * @return ctpResultaat[]
     */
    public function getResultaat()
    {
      return $this->resultaat;
    }

    /**
     * @param ctpResultaat[] $resultaat
     * @return ArrayOfCtpResultaat
     */
    public function setResultaat(array $resultaat = null)
    {
      $this->resultaat = $resultaat;
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
      return isset($this->resultaat[$offset]);
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to retrieve
     * @return ctpResultaat
     */
    public function offsetGet($offset)
    {
      return $this->resultaat[$offset];
    }

    /**
     * ArrayAccess implementation
     *
     * @param mixed $offset The offset to assign the value to
     * @param ctpResultaat $value The value to set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
      if (!isset($offset)) {
        $this->resultaat[] = $value;
      } else {
        $this->resultaat[$offset] = $value;
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
      unset($this->resultaat[$offset]);
    }

    /**
     * Iterator implementation
     *
     * @return ctpResultaat Return the current element
     */
    public function current()
    {
      return current($this->resultaat);
    }

    /**
     * Iterator implementation
     * Move forward to next element
     *
     * @return void
     */
    public function next()
    {
      next($this->resultaat);
    }

    /**
     * Iterator implementation
     *
     * @return string|null Return the key of the current element or null
     */
    public function key()
    {
      return key($this->resultaat);
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
      reset($this->resultaat);
    }

    /**
     * Countable implementation
     *
     * @return ctpResultaat Return count of elements
     */
    public function count()
    {
      return count($this->resultaat);
    }

}
