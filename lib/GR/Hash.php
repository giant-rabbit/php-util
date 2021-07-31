<?php

namespace GR;

use GR\InvalidArgument;

class Hash
{
  static function fetch($array, $index, $default = NULL)
  {
      if (!is_array($array)) {
        throw new InvalidArgument("Passed " . gettype($array) . " to fetch, but you must pass an array.");
      }
      if (array_key_exists($index, $array))
      {
          return $array[$index];
      }
      return $default;
  }
}
