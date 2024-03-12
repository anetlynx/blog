<?php

function parseParagraphImages(DOMElement $domElement): DOMElement {
  if ($domElement->nodeName === 'p') {
    $imgNodeCount = $domElement->getElementsByTagName('img')->length;

    if ($imgNodeCount > 1) {
      $gallery = $domElement->ownerDocument->createElement('div');
      assert($gallery instanceof DOMElement);
      $gallery->setAttribute('class', 'gallery');

      $galleryContent = $domElement->ownerDocument->createElement('div');
      assert($galleryContent instanceof DOMElement);
      $galleryContent->setAttribute('class', 'gallery-content');

      $gallery->appendChild($galleryContent);

      foreach ($domElement->childNodes as $pChildNode) {
        $galleryContent->appendChild($pChildNode->cloneNode(true));
      }

      return $gallery;
    } else {
      return $domElement;
    }
  } else {
    return $domElement;
  }
}
