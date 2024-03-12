<?php

use function Safe\date;

class PageProps extends \ShapeType {
  public ?string $title;
  public ?string $content;
  public ?array $posts;
  public ?array $tags;
}

return [
  'Page' => function (array $props) {
    [
      'title' => $title,
      'content' => $content,
      'posts' => $posts,
      'tags' => $tags,
    ] = new PageProps($props);

    return (
      [
        ['header', null, [
          ['div', ['className'=>"content"], [
            ['Navigation', ['data'=>(['non-printable' => true]), 'title'=>"Anet Lynx", 'url'=>(getSiteConfig()->OUTPUT_URL)], [
            ]],
            ['h1', null, [($title)]],
            ($tags
              ? (
                ['div', ['className'=>"tags-list"], [
                  ['ul', ['className'=>"content"], [(array_map(fn($tag) => ['li', null, ['#', ($tag)]], $tags))]],
                ]]
              )
              : null
            ),
          ]],
        ]],
        ['div', ['className'=>"content"], [
          ['main', ['dangerouslySetInnerHTML'=>($content)]],
          ($posts
            ? (
              ['aside', null, [
                ['div', ['className'=>"content"], [
                  ['ul', null, [
                    (array_map(function ($post) {
                      $title = getFileMeta($post, 'title') ?? basename(pathinfo($post, PATHINFO_DIRNAME));

                      return ['li', null, [['a', ['href'=>($post)], [($title)]]]];
                    }, $posts)),
                  ]],
                ]],
              ]]
            )
            : null
          ),
        ]],
        ['footer', null, [
          ['div', ['className'=>"content"], [
            ['p', null, ['©', (date('Y')), ' @anetlynx']],
            ['p', ['xmlns:cc'=>"http://creativecommons.org/ns#", 'className'=>"license"], ['This work is licensed under ', ['a', ['href'=>"http://creativecommons.org/licenses/by-nc/4.0/?ref=chooser-v1", 'target'=>"_blank", 'rel'=>"license noopener noreferrer"], ['CC BY-NC 4.0', ['img', ['style'=>"height:22px!important;margin-left:3px;vertical-align:text-bottom;", 'src'=>"https://mirrors.creativecommons.org/presskit/icons/cc.svg?ref=chooser-v1"]], ['img', ['style'=>"height:22px!important;margin-left:3px;vertical-align:text-bottom;", 'src'=>"https://mirrors.creativecommons.org/presskit/icons/by.svg?ref=chooser-v1"]], ['img', ['style'=>"height:22px!important;margin-left:3px;vertical-align:text-bottom;", 'src'=>"https://mirrors.creativecommons.org/presskit/icons/nc.svg?ref=chooser-v1"]]]]]],
          ]],
        ]],
      ]
    );
  }
];
