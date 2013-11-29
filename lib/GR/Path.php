<?php

namespace GR;

class Path
{
  static function join()
  {
    $path_parts = array();
    $args = func_get_args();
    foreach ($args as $arg) {
      if (is_array($arg)) {
        $path_parts = array_merge($path_parts, $arg);
      } else {
        $path_parts[] = $arg;
      }
    }
    return implode(DIRECTORY_SEPARATOR, $path_parts);
  }
}
