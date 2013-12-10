<?php

namespace GR;

class Hash
{
  static function fetch($array, $index, $default = NULL)
  {
      if (array_key_exists($index, $array))
      {
          return $array[$index];
      }
      return $default;
  }
}
