<?php

function outDocumentPath(string $document, bool $relative = false): string {
  $realpath = realpath($document);

  if (!$realpath) {
    throw new Exception("File does not exist: {$document}");
  }

  $basename = basename($realpath);

  $out = substr($realpath, strlen((getSiteConfig()->INPUT_PATH)."/"));

  if ($basename !== 'index.md') {
    $out = preg_replace('/\.md$/', '/index.html', $out);
  }

  $out = preg_replace('/\.md$/', '.html', $out);
  $out = preg_replace('/\\_.+?\\//', '', $out);

  if ($relative) {
    return $out;
  } else {
    return (getSiteConfig()->OUTPUT_PATH)."/{$out}";
  }
}
