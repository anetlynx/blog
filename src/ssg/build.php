<?php const DEBUG = true;

use function Safe\file_put_contents;
use function Safe\file_get_contents;
use function Safe\preg_match;
use Attitude\PHPX\Renderer\Renderer;

require_once 'vendor/autoload.php';
require_once 'src/index.php';

$components = require_once 'src/components/index.php';


// TODO: Allow to pass the path to the site config file
$config = getSiteConfig('site.ini');

$renderer = new Renderer();
$renderer->pretty = true;
$frontMatterParser = new \Hyn\Frontmatter\Parser(new \cebe\markdown\GithubMarkdown);
$frontMatterParser->setFrontmatter(\Hyn\Frontmatter\Frontmatters\YamlFrontmatter::class);

const VALID_PATH_REGEX = '/^[a-z0-9-_\.]+$/';

$GLOBALS['processed'] = [];

function setFileMeta(string $url, array $meta): void {
  $GLOBALS['processed'][$url] = $meta;
}

function getFileMeta(string $url, ?string $key = null) {
  if (array_key_exists($url, $GLOBALS['processed'])) {
    if ($key) {
      return $GLOBALS['processed'][$url][$key] ?? null;
    } else {
      return $GLOBALS['processed'][$url];
    }
  } else {
    throw new Exception("Meta for {$url}");
  }
}

globMapRecursive('content/*.md', function ($file) use ($components, $frontMatterParser, $renderer) {
  $breadcrumbs = explode('/', $file);

  foreach ($breadcrumbs as $breadcrumb) {
    if (!preg_match(VALID_PATH_REGEX, $breadcrumb)) {
      throw new Exception("Invalid file name: {$file} at '{$breadcrumb}' found. Stopping the process. Allowed characters are: a-z, 0-9, - and .");
    } else if (substr_count($file, '_') > 1) {
      throw new Exception("Invalid file name: {$file}. Stopping the process. Only one underscore is allowed.");
    }
  }

  console("<pink>{$file}</pink>");

  $fileContents = file_get_contents($file);

  ['markdown' => $markdown, 'meta' => $meta, 'html' => $html] = $frontMatterParser->parse($fileContents);

  $dom = new DOMDocument();
  $dom->preserveWhiteSpace = true;
  $dom->formatOutput = true;
  $dom->strictErrorChecking = false;
  $dom->loadHTML('<!DOCTYPE html><meta charset="UTF-8">'.$html);

  $links = $dom->getElementsByTagName('a');
  processAllDocumentLinks($links, $file);

  $images = $dom->getElementsByTagName('img');
  processAllDocumentImages($images, $file);

  $pageContent = [];
  $body = $dom->getElementsByTagName('body')->item(0);

  if ($body) {
    foreach ($body->childNodes as $childNode) {
      if ($childNode->nodeName === 'p') {
        $pageContent[] = $dom->saveHTML(parseParagraphImages($childNode));
      } else if ($childNode->nodeName === 'blockquote') {
        $pageContent[] = $dom->saveHTML(parseBlockquoteAsCallout($childNode));
      } else if ($childNode->nodeType === XML_TEXT_NODE) {
        if (trim($childNode->textContent) !== '') {
          $pageContent[] = $childNode->textContent;
        }
      } else {
        $pageContent[] = $dom->saveHTML($childNode);
      }
    }
  }

  $content = str_replace("\n<", "\n\t\t\t\t<", "\n\t\t\t".implode("\n\t\t\t", $pageContent)."\n\t\t\t");
  $posts = null;

  if (basename($file) === 'index.md') {
    $indexDirectoryPath = dirname($file);
    debug("\n<yellow>{$indexDirectoryPath}</yellow> (index)");

    $files = glob("{$indexDirectoryPath}/_*/{*.md,*/index.md}", GLOB_BRACE);
    $files = array_reverse($files);

    if (count($files) > 0) {
      $posts = [];

      $posts = array_map(function ($file) {
        return getRealPathURL($file);
      }, $files);
    }
  }

  $outDocumentPath = outDocumentPath($file);
  $outDocumentDir = dirname($outDocumentPath);

  mkdirRecursive($outDocumentDir);

  if (file_put_contents($outDocumentPath, (
    "<!DOCTYPE html>\n".
    $renderer->render(['App', [
      ...((array) $meta),
      'content' => $content,
      'posts' => $posts,
      'css' => getRealPathURL(getSiteConfig()->ASSETS_PATH.'/css/styles.css'),
      'js' => getRealPathURL(getSiteConfig()->ASSETS_PATH.'/js/scripts.js'),
    ]], $components)
  ))) {
    setFileMeta(getRealPathURL($file), $meta ?? []);

    debug("\n{$outDocumentPath}");
    success(" ".UTF8_SYMBOL_OK."\n");
  } else {
    throw new Exception("Failed to write: {$outDocumentPath}");
  }
}, GLOB_NOSORT);

console("<success>".UTF8_SYMBOL_OK." Done building the site.</success>");
