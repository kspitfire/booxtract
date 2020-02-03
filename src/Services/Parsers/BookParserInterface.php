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
     */
    public function parse(SplFileInfo $file): ParsedData;

    /**
     * Return file mask of supported files as string.
     */
    public static function getFileMask(): string;
}
