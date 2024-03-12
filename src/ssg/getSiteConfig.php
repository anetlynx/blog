<?php

use function Safe\parse_ini_file;
use function Safe\realpath;

class SiteType extends ShapeType {
  public readonly string $INPUT_PATH;
  public readonly string $OUTPUT_PATH;
  public readonly string $OUTPUT_URL;
  public readonly string $ASSETS_PATH;
  public readonly string $IMAGES_PATH;
  public readonly string $COMPONENTS_PATH;
  public readonly string $COMPONENTS_NAMESPACE;

  public function __construct(array $props) {
    $this->INPUT_PATH = $props['INPUT_PATH'];
    $this->OUTPUT_PATH = $props['OUTPUT_PATH'];
    $this->OUTPUT_URL = $props['OUTPUT_URL'];
    $this->ASSETS_PATH = $props['ASSETS_PATH'];
    $this->IMAGES_PATH = $props['IMAGES_PATH'];
    $this->COMPONENTS_PATH = $props['COMPONENTS_PATH'];
    $this->COMPONENTS_NAMESPACE = $props['COMPONENTS_NAMESPACE'];
  }
}

function getSiteConfig(string $SITE_CONFIG_PATH = null): SiteType {
  static $config = null;

  if (!$config) {
    if (!$SITE_CONFIG_PATH) {
      throw new Exception("Site config path is required");
    }

    try {
      $site = parse_ini_file(getcwd().'/'.$SITE_CONFIG_PATH, true, INI_SCANNER_TYPED);
    } catch (Exception $e) {
      throw new Exception("Failed to parse site ini file: ".$SITE_CONFIG_PATH);
    }

    [
      'INPUT_PATH' => $INPUT_PATH,
      'OUTPUT_PATH' => $OUTPUT_PATH,
      'ASSETS_PATH' => $ASSETS_PATH,
      'IMAGES_PATH' => $IMAGES_PATH,
      'COMPONENTS_PATH' => $COMPONENTS_PATH,
      'COMPONENTS_NAMESPACE' => $COMPONENTS_NAMESPACE,
      'OUTPUT_URL' => $OUTPUT_URL,
    ] = $site;

    $INPUT_PATH = realpath(rtrim($INPUT_PATH, '/'));
    $OUTPUT_PATH = realpath(rtrim($OUTPUT_PATH, '/'));
    $ASSETS_PATH = realpath(rtrim($ASSETS_PATH, '/'));
    $IMAGES_PATH = realpath(rtrim($IMAGES_PATH, '/'));

    if (!is_dir($INPUT_PATH)) {
      throw new Exception("Input path does not exist: {$INPUT_PATH}");
    }

    if (!is_dir($OUTPUT_PATH)) {
      throw new Exception("Output path does not exist: {$OUTPUT_PATH}");
    }

    if (!is_dir($ASSETS_PATH)) {
      throw new Exception("Assets path does not exist: {$ASSETS_PATH}");
    }

    if (!is_dir($IMAGES_PATH)) {
      throw new Exception("Images path does not exist: {$IMAGES_PATH}");
    }

    if (strpos($ASSETS_PATH, $OUTPUT_PATH) === false) {
      throw new Exception("Assets path must be inside the output path");
    }

    if (strpos($ASSETS_PATH, $OUTPUT_PATH.'/_') === false) {
      throw new Exception("Assets path must be prefixed with an underscore");
    }

    if (strpos($IMAGES_PATH, $OUTPUT_PATH) === false) {
      throw new Exception("Images path must be inside the output path");
    }

    if (strpos($IMAGES_PATH, $OUTPUT_PATH.'/_') === false) {
      throw new Exception("Images path must be prefixed with an underscore");
    }

    $site['INPUT_PATH'] = $INPUT_PATH;
    $site['OUTPUT_PATH'] = $OUTPUT_PATH;
    $site['OUTPUT_URL'] = $OUTPUT_URL;
    $site['ASSETS_PATH'] = $ASSETS_PATH;
    $site['IMAGES_PATH'] = $IMAGES_PATH;
    $site['COMPONENTS_PATH'] = $COMPONENTS_PATH;
    $site['COMPONENTS_NAMESPACE'] = $COMPONENTS_NAMESPACE;

    $config = new SiteType($site);
  }

  return $config;
}
