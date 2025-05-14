<?php

declare(strict_types=1);

// This could be converted to YAML or JSON, rather than a PHP array, used this as its how cakephp does it and it's what I'm familiar with
return [
    'gov.uk' => [
        'links' => [
            'xpath' => "//div[@id='js-results']//li/div/a",
            'attribute' => 'content',
        ],
    ],
];