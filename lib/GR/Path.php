<?php

namespace GR;

class Path
{
  static function stripFirstSegment($path)
  {
    $trimmed = ltrim($path, '/');
    $slash_position = strpos($trimmed, '/');
    if ($slash_position === false) {
      return null;
    }
    return substr($trimmed, $slash_position + 1);
  }

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
