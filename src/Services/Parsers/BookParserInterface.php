<?php

namespace Booxtract\Services\Parsers;

use Booxtract\DataObjects\ParsedData;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Этот интерфейс определяет, как должен выглядеть парсер метаданных.
 */
interface BookParserInterface
{
    /**
     * Парсер метаданных книги из файла.
     *
     * @param SplFileInfo $file Файл электронной книги
     *
     * @return ParsedData
     */
    public function parse(SplFileInfo $file): ParsedData;
}
