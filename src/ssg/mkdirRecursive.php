<?php

function mkdirRecursive(string $path, int $mode = 0777, bool $recursive = true): bool {
  if (is_dir($path)) {
    return true;
  }

  return mkdir($path, $mode, $recursive);
}
