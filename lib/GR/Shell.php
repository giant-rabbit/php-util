<?php

namespace GR;
use \Exception as Exception;

class PipeHandler {
  public $pipe;
  public $data = '';
  public $name;
  public $done = FALSE;

  function __construct($pipe, $name) {
    $this->pipe = $pipe;
    $this->name = $name;
  }
}

class Shell
{
  static function command($command, $options = array()) {
    $shell = new static();
    return $shell->run($command, $options);
  }

  function run($command, $options = array())
  {
    $options['throw_exception_on_nonzero'] = Hash::fetch($options, 'throw_exception_on_nonzero', TRUE);
    $options['print_command'] = Hash::fetch($options, 'print_command', FALSE);
    $options['input'] = Hash::fetch($options, 'input', NULL);
    if ($options['print_command'])
    {
      print("$command\n");
    }
    $descriptors_spec = array
    (
      0 => array('pipe', 'r'),
      1 => array('pipe', 'w'),
      2 => array('pipe', 'w'),
    );
    $process = proc_open($command, $descriptors_spec, $pipes);
    if ($process === FALSE)
    {
      throw new Exception("Unable to proc_open($command).");
    } 
    stream_set_blocking($pipes[1], FALSE);
    stream_set_blocking($pipes[2], FALSE);
    if ($options['input'])
    {
      fwrite($pipes[0], $options['input']);
    }
    fclose($pipes[0]);
    $pipe_handlers = [];
    $pipe_handlers[] = new PipeHandler($pipes[1], 'stdout');
    $pipe_handlers[] = new PipeHandler($pipes[2], 'stderr');
    while (!$pipe_handlers[0]->done && !$pipe_handlers[1]->done)
    {
      $read_streams = array($pipes[1], $pipes[2]);
      $write_streams = null;
      $exceptions = null;
      $result = stream_select($read_streams, $write_streams, $exceptions, NULL);
      if ($result === FALSE)
      {
        throw new Exception("Error running stream_select on pipe.");
      }
      if ($result > 0)
      { 
        foreach ($read_streams as $read_stream)
        {
          $pipe_handler = $pipe_handlers[0];
	  if ($read_stream == $pipe_handlers[1]->pipe) {
	    $pipe_handler = $pipe_handlers[1];
	  }
          $result = fgets($read_stream);
          if ($result === FALSE)
          {
            if (!feof($read_stream))
            {
              throw new Exception("Error reading from proc_open($command) {$pipe_handler->name} stream.");
            }
            else
            {
              $pipe_handler->done = TRUE;
            }
          }
          else
          {
	    $pipe_handler->data .= $result;
          }
        }
      }
    }
    fclose($pipes[1]);
    fclose($pipes[2]);
    $return_value = proc_close($process);
    if ($return_value != 0 && $options['throw_exception_on_nonzero'])
    {
      throw new Exception("Error running '$command'. Non-zero return value ($return_value)\nstdout:{$pipe_handlers[0]->data}\nstderr:{$pipe_handlers[1]->data}");
    }
    return array($pipe_handlers[0]->data, $pipe_handlers[1]->data);
  }
}
