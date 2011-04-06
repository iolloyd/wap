<?php
class dynamic{

  private $object = NULL;
  private $vars   = array();
  private $arrays = array();

  function __construct($obj) {
    $this->object = $obj;
  }

  private function __get($var) {
    if (in_array($var, array_keys($this->vars))) {
      return $this->vars[$var];
    }
    else {
      return $this->object->$var;
    }
  }

  private function __set($var, $val) {
    if (in_array($var, array_keys($this->vars))) {
      $this->vars[$var] = $val;
    }
    else {
      $this->$object->$var = $val;
    }
  }

  private function __isset($var) {
    if (in_array($var, array_keys($this->vars))) {
      return TRUE;
    }
    return isset($this->object->$var);
  }

  private function __unset($var) {
    unset($this->object->$var);
    unset($this->vars[$var]);
  }

  public function registerFields(&$vars) {
    $this->vars = $vars;
  }

  function __call($method, $args) {
    return call_user_func_array(array($this->object, $method), $args);
  }
}

