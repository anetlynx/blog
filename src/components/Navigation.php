<?php

class NavigationProps extends \ShapeType {
  public string $url;
  public string $title;
  public ?array $children;
  public ?array $data;
}

return [
  'Navigation' => function (array $props) {
    [
      'url' => $url,
      'title' => $title,
      'children' => $children,
      'data' => $data,
    ] = new NavigationProps($props);

    return (
      ['nav', ['data'=>($data), 'id'=>"navigation", 'className'=>"navigation"], [
        ['a', ['href'=>($url), 'className'=>"navigation-link"], [
          ['strong', ['className'=>"navigation-title"], [($title)]],
        ]],
        ($children),
      ]]
    );
  },
];
