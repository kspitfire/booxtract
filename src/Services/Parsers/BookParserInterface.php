<?php

namespace Booxtract\Services\Parsers;

use Booxtract\DataObjects\ParsedData;
use Symfony\Component\Finder\SplFileInfo;

/**
 * This interface describes how all ebooks parsers should looks like.
 */
interface BookParserInterface
{
    /**
     * Parse metadata from ebook file.
     *
     * @param SplFileInfo $file File
     *
     * @return ParsedData
     */
    public function parse(SplFileInfo $file): ParsedData;
}
