<?php

$new_path = 'docs/'.trim(preg_replace('/\/blog/', '', $_SERVER['REQUEST_URI']), '/');

if (is_dir($new_path)) {
    $new_path .= '/index.html';
}

// Get the file extension
$extension = pathinfo($new_path, PATHINFO_EXTENSION);

// Set the appropriate content type based on extension
$mime_types = array(
  'css' => 'text/css',
  'js' => 'application/javascript',
  'html' => 'text/html',
  // Add more mime types as needed
);

if (isset($mime_types[$extension])) {
  header('Content-Type: ' . $mime_types[$extension]);
} else {
  // Set mime type
  header('Content-Type: '.mime_content_type($new_path));
}

return readfile($new_path);
