<?php

function processAllDocumentLinks(DOMNodeList $links, string $documentPath): void {
  if (is_dir($documentPath)) {
    throw new Exception("Expecting a file, got a directory: {$documentPath}");
  }

  $dir = dirname($documentPath);

  foreach ($links as $link) {
    $href = $link->getAttribute('href');
    if (strpos($href, 'http') === 0) {
      $link->setAttribute('target', '_blank');
      $link->setAttribute('rel', 'noopener noreferrer');
    } else if (strpos($href, 'mailto:') === 0) {
      $link->setAttribute('target', '_blank');
      $link->setAttribute('rel', 'noopener noreferrer');
    } else {
      $link->setAttribute('href', outDocumentPath("{$dir}/{$href}", true));
    }
  }
}
