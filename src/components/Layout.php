<?php

class LayoutProps extends \ShapeType {
  public ?string $title;
  public ?string $description;
  public ?string $css;
  public ?string $js;
  public ?array $children;
}

return [
  'Layout' => function (array $props) {
    [
      'title' => $title,
      'description' => $description,
      'css' => $css,
      'js' => $js,
      'children' => $children,
    ] = new LayoutProps($props);

    return (
      ['$', 'html', ['lang'=>"en"], [
        ['$', 'head', null, [
          ['$', 'meta', ['charset'=>"UTF-8"]],
          ['$', 'meta', ['name'=>"viewport", 'content'=>"width=device-width, viewport-fit=cover, initial-scale=1"]],
          ['$', 'meta', ['name'=>"theme-color", 'content'=>"#FFFFFF", 'media'=>"(prefers-color-scheme: light)"]],
          ['$', 'meta', ['name'=>"theme-color", 'content'=>"#000000", 'media'=>"(prefers-color-scheme: dark)"]],
          ['$', 'title', null, [($title), ' by @anetlynx']],
          ['$', 'meta', ['name'=>"description", 'content'=>($description)]],
          ['$', 'link', ['rel'=>"stylesheet", 'href'=>($css)]],
        ]],
        ['$', 'body', null, [
          ($children),
          ['$', 'script', ['defer'=>true, 'src'=>($js)]],
        ]],
      ]]
    );
  }
];
