<?php

declare(strict_types=1);

// This could be converted to YAML or JSON, rather than a PHP array, used this as its how cakephp does it and it's what I'm familiar with
return [
    'gov.uk' => [
        'title' => [
            'xpath' => "//meta[@property='og:title']",
            'attribute' => 'content',
        ],
        'authors' => [
            'xpath' => "//div[contains(@class, 'gem-c-metadata')]//a[contains(@class, 'govuk-link')]",
            'type' => 'text',
        ],
    ],
    // can add more fields as the list goes on
    // Can add more config like whitehouse etc
];