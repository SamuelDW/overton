<?php

declare(strict_types=1);

namespace App\Utility;

use InvalidArgumentException;

class Url
{
    /**
     * Gets the base url for a given url i.e https://www.domain.name
     * @param string $url
     * @throws \InvalidArgumentException
     * @return string
     */
    public static function getBaseUrl(string $url): string
    {
        $parts = parse_url($url);

        if (!isset($parts['scheme'], $parts['host'])) {
            throw new InvalidArgumentException("Invalid URL: $url");
        }

        $scheme = $parts['scheme'];
        $host = $parts['host'];

        // Optionally include port if it exists
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        return "$scheme://$host$port";
    }

    /**
     * Gets the domain from a given url i.e domain.name from https://www.domain.name
     * @param string $url
     * @param bool $stripWww
     * @throws \InvalidArgumentException
     * @return mixed|string
     */
    public static function getDomain(string $url, bool $stripWww = true): string
    {
        $parts = parse_url($url);

        if (!isset($parts['host'])) {
            throw new InvalidArgumentException("Invalid URL: $url");
        }

        $host = $parts['host'];

        if ($stripWww && str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        return $host;
    }
}