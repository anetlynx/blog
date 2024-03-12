<?php
use function Safe\preg_replace;
use function Safe\realpath;

function getRealPathURL(string $path): string {
  try {
    $realpath = realpath($path);
  } catch (Exception $e) {
    throw new Exception("File does not exist: {$path}");
  }

  $prefix = getSiteConfig()->OUTPUT_URL;

  // Inside of the OUTPUT_PATH
  if (strpos($realpath, getSiteConfig()->OUTPUT_PATH) === 0) {
    return $prefix.str_replace(getSiteConfig()->OUTPUT_PATH, '', $realpath);
  } else if (strpos($realpath, getSiteConfig()->INPUT_PATH) === 0) {
    [
      'dirname' => $dirname,
      'extension' => $extension,
      'filename' => $filename
    ] = pathinfo($realpath);

    if ($extension === 'md') {
      $extension = 'html';

      if ($filename !== 'index') {
        $filename = "{$filename}/index";
      }
    }

    $virtualRealpath = "{$dirname}/{$filename}.{$extension}";

    return $prefix.preg_replace('/\\/\b_\w+\b/', '', str_replace(getSiteConfig()->INPUT_PATH, '', $virtualRealpath));
  } else {
    throw new Exception("Path must be inside the input or output path: {$path}");
  }
}
