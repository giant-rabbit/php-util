<?php

namespace GR;

class Str
{
  static function starts_with($haystack, $needle)
  {
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
  }
}
