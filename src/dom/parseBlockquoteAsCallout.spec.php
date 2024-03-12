<?php require_once 'parseBlockquoteAsCallout.php';

describe('parseBlockquoteAsCallout', function() {
  $dom = new DOMDocument();
  $dom->strictErrorChecking = false;
  $dom->loadHTML(<<<HTML
<blockquote>
  <p>[!note] Some text to match
  First paragraph</p>
  <blockquote>
    <p>Nested paragraph</p>
    <blockquote>
      <p>[!danger]+
      Nested danger paragraph</p>
    </blockquote>
    <blockquote>
      <p>[!info]-
      nested info paragraph</p>
      <p>P</p>
    </blockquote>
    <p>p</p>
  </blockquote>
</blockquote>
HTML, LIBXML_NOBLANKS);

  $xml = new DOMDocument();
  $xml->preserveWhiteSpace = false;
  $xml->formatOutput = true;
  $xml->loadXML($dom->saveXML(), LIBXML_NOBLANKS);

  it('should parse blockquote as callout', function() use ($xml) {
    $blockquote = $xml->getElementsByTagName('blockquote')->item(0);
    $result = parseBlockquoteAsCallout($blockquote);

    $expected = <<<HTML
<details class="callout" data-type="note" open="true" data-toggle="false">
  <summary class="callout-summary" data-type="note">Some text to match</summary>
  <p>First paragraph</p>
  <blockquote>
    <p>Nested paragraph</p>
    <details class="callout" data-type="danger" open="true">
      <summary class="callout-summary" data-type="danger">Danger</summary>
      <p>Nested danger paragraph</p>
    </details>
    <details class="callout" data-type="info">
      <summary class="callout-summary" data-type="info">Info</summary>
      <p>nested info paragraph</p>
      <p>P</p>
    </details>
    <p>p</p>
  </blockquote>
</details>
HTML;
    expect($xml->saveXML($result))->toBe($expected);
  });
});
