<?php

use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

function getImageManager(): ImageManager {
  static $manager = null;

  if ($manager === null) {
    $manager = new ImageManager(new Intervention\Image\Drivers\Imagick\Driver);
  }

  return $manager;
}

function hashedImageFilename(string $hash, int $width, int $height, string $ext): string {
  return "{$hash}-{$width}x{$height}.{$ext}";
}

function storeResizedHashedImage(ImageInterface $original, string $hash, int $width, int $height, string $ext, int $quality): array {
  $hashedImageFilename = hashedImageFilename($hash, $width, $height, $ext);
  $realpath = getSiteConfig()->IMAGES_PATH. '/' . $hashedImageFilename;

  if (!file_exists($realpath)) {
    $original->cover($width, $height);
    $original->save($realpath, quality: $quality);

    success("\n".UTF8_SYMBOL_OK." {$realpath}");
  }

  return [$realpath, getRealPathURL($realpath)];
}

$manager = new ImageManager(new Intervention\Image\Drivers\Imagick\Driver);

function processAllDocumentImages(DOMNodeList $images, string $documentPath): void {
  if (is_dir($documentPath)) {
    throw new Exception("Expecting a file, got a directory: {$documentPath}");
  }

  $documentRealPath = realpath($documentPath);

  if ($documentRealPath === false) {
    throw new Exception("File does not exist: {$documentPath}");
  }

  $manager = getImageManager();

  foreach ($images as $image) {
    if (!$image instanceof DOMElement) {
      throw new Exception("Expected a DOMElement, got: " . get_class($image));
    }

    $src = $image->getAttribute('src');

    $image->setAttribute('loading', 'lazy');
    $alt = $image->getAttribute('alt');

    [$alt, $size] = explode('|', $alt);

    if ($size) {
      $image->setAttribute('alt', trim($alt));
      [$width, $height] = [...explode('x', $size), null, null];
    } else {
      $width = null;
      $height = null;
    }

    $dir = dirname($documentPath);
    $realpath = realpath($dir . '/' . $src);

    if ($realpath) {
      debug("\n{$dir}/{$src} (resizing)");

      $exif = exif_read_data($realpath, null, true);

      if (isset($exif['FILE']) && isset($exif['COMPUTED']) && count(array_keys($exif)) > 2) {
        throw new Exception("Sensitive EXIF data found in {$realpath}. Stopping the process.");
      }

      // Has file as Git would do
      $hash = hash_file('sha256', $realpath);
      $urlFriendlyHash = base62_encode($hash);

      $original = $manager->read($realpath);
      [$originalWidth, $originalHeight] = [$original->width(), $original->height()];

      if ($originalWidth > $originalHeight) {
        [, $at1920Out] = storeResizedHashedImage($original, hash: $urlFriendlyHash, width: 1920, height: round(($originalHeight / $originalWidth) * 1920), ext: 'jpeg', quality: 75);
      } else {
        [, $at1920Out] = storeResizedHashedImage($original, hash: $urlFriendlyHash, width: round(($originalWidth / $originalHeight) * 1920), height: 1920, ext: 'jpeg', quality: 75);
      }

      if ($width || $height) {
        $width = $width ?? round(($originalWidth / $originalHeight) * $height);
        $height = $height ?? round(($originalHeight / $originalWidth) * $width);

        $image->setAttribute('width', $width);
        $image->setAttribute('height', $height);

        // Round $width to the nearest multiple of 120
        $width = round($width / 120) * 120;
        $height = round(($originalHeight / $originalWidth) * $width);

        [, $at3xOut] = storeResizedHashedImage($original, hash: $urlFriendlyHash, width: $width * 3, height: $height * 3, ext: 'jpeg', quality: 80);
        [, $at2xOut] = storeResizedHashedImage($original, hash: $urlFriendlyHash, width: $width * 2, height: $height * 2, ext: 'jpeg', quality: 80);
        [, $at1xOut] = storeResizedHashedImage($original, hash: $urlFriendlyHash, width: $width, height: $height, ext: 'jpeg', quality: 80);

        $image->setAttribute('src', $at1xOut);
        $image->setAttribute('srcset', "{$at1xOut}, {$at2xOut} 2x, {$at3xOut} 3x");
      }

      $a = $image->ownerDocument->createElement('a');
      $a->setAttribute('href', $at1920Out);
      $a->setAttribute('target', '_blank');
      $a->appendChild($image->cloneNode(true));

      $image->replaceWith($a);
    }
  }
}
