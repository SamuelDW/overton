<?php

declare(strict_types=1);

namespace App\MetaData;

/**
 * A nice meta data object, so that there is some process and standardised objects for collecting data
 * Could depending on how much you want to store could store the entire html of the page, however I feel like that may be the wrong approach, if the page changes, no one would know
 * So could store a timestamp of last accessed and have a scheduler 
 */
class MetaData
{
    /**
     * The title of the page if it exists
     * @var 
     */
    public ?string $title = null;

    public array $authors = [];

    public ?string $publicationDate = null;

    public ?string $summary = null;

    /**
     * The url that the information has been taken from could possibly also do a datetime stamp
     * @var 
     */
    public ?string $url = null;

    // and so on and so on for all the other bits that may be wanted
    // could possibly have a function for running through everything
}