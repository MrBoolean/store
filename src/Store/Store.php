<?php
namespace Store;

use Countable;
use Iterator;
use InvalidArgumentException;
use IteratorAggregate;
use Serializable;

class Store implements Countable, Iterator, Serializable
{

  use Plugin\Filter, Plugin\Map, Plugin\Merge, Plugin\Fill;

  protected $parent;
  protected $data;

  public function __construct(array $data = null, $parent = null)
  {
    $this->parent = $parent;
    $this->data   = $data ?? [];
  }

  protected function transform($value)
  {
    if (is_array($value)) {
      return new Store($value, $this);
    }

    return $value;
  }

  public function getRoot()
  {
    $root = $this->getParent();

    while (null !== $root && null !== ($expected = $root->getParent())) {
      $root = $expected;
    }

    return $root;
  }

  public function getParent()
  {
    return $this->parent;
  }

  public function set($key, $value)
  {
    $this->data[$key] = $value;
  }

  public function get($key, $default = null)
  {
    if ($this->has($key)) {
      return $this->transform($this->data[$key]);
    }

    return $default;
  }

  public function all()
  {
    return $this->data;
  }

  public function has($key) : bool
  {
    return array_key_exists($key, $this->data);
  }

  public function count() : int
  {
    return count($this->data);
  }

  public function pushToStart() : Store
  {
    foreach (array_reverse(func_get_args()) as $argument) {
      array_unshift($this->data, $argument);
    }

    return $this;
  }

  public function pushToEnd() : Store
  {
    foreach (func_get_args() as $argument) {
      array_push($this->data, $argument);
    }

    return $this;
  }

  public function indexOf($value)
  {
    return array_search($value, $this->data);
  }

  public function isAssociative() : bool
  {
    $keys = array_keys($this->data);

    return array_keys($keys) !== $keys;
  }

  public function keys() : array
  {
    return array_keys($this->data);
  }

  public function values() : array
  {
    return array_values($this->data);
  }

  public function toArray() : array
  {
    $result = [];

    foreach ($this->data as $key => $value) {
      if ($value instanceof Store) {
        $value = $value->toArray();
      }

      $result[$key] = $value;
    }

    return $result;
  }

  public function toJSON() : string
  {
    return json_encode($this->toArray());
  }

  public function rewind()
  {
    reset($this->data);

    return $this;
  }

  public function current()
  {
    return current($this->data);
  }

  public function key()
  {
    return key($this->data);
  }

  public function next()
  {
    return next($this->data);
  }

  public function valid()
  {
    return isset($this->data[$this->key()]);
  }

  public function serialize() : string
  {
    return serialize($this->data);
  }

  public function unserialize($data) : Store
  {
    $this->data = unserialize($data);
    return $this;
  }

}