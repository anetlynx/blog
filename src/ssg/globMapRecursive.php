<?php

function globMapRecursive(string $pattern, callable $callback, int $flags = 0) {
  // Process `_*` directories first
  foreach (glob(dirname($pattern).'/_*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
    console("<yellow>{$dir}</yellow>\n");
    globMapRecursive($dir.'/'.basename($pattern), $callback, $flags);
  }

  // Process `*` directories
  foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
    if (!str_starts_with(basename($dir), '_')) {
      console("<blue>{$dir}</blue>\n");
      globMapRecursive($dir.'/'.basename($pattern), $callback, $flags);
    }
  }

  // Process files
  array_map($callback, glob($pattern, $flags));

  return true;
}
