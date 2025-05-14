<?php

declare(strict_types=1);

// This could be converted to YAML or JSON
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
];