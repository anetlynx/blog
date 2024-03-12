<?php

class AppProps extends \ShapeType {
  public ?string $content;
  public ?string $css;
  public ?string $js;
  public ?array  $children;
  public ?string $description;
  public ?array  $posts;
  public ?string $title;
  public ?array  $tags;
}

return [
  'App' => function ($props) {
    [
      'content'     => $content,
      'css'         => $css,
      'js'          => $js,
      'children'    => $children,
      'description' => $description,
      'posts'       => $posts,
      'title'       => $title,
      'tags'        => $tags,
    ] = new AppProps($props);

    return (
      ['Layout', [
        'title'=>($title),
        'description'=>($description),
        'css'=>($css),
        'js'=>($js),
], [
        ['Page', [
          'title'=>($title),
          'content'=>($content),
          'posts'=>($posts),
          'tags'=>($tags),
]],
      ]]
    );
  },
];
