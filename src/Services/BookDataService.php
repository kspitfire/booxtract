<?php

namespace Booxtract\Services;

use Booxtract\DataObjects\ParsedData;
use Booxtract\Services\Parsers\BookParserInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Main service for ebooks processing.
 */
class BookDataService
{
    /**
     * Chunks to clean from file name.
     */
    const RESTRICTED_CHUNKS = [
        '[litres]', '«', '»', '"', '…', '#',
        '“', '”'
    ];

    /**
     * Map for replacing unsafe symbols for file naming.
     */
    const REPLACERS = [
        '[' => '(',
        ']' => ')',
        '—' => '-',
        '−' => '-',
        '–' => '-',
        '?' => '.',
    ];

    /**
     * @var BookParserInterface
     */
    private $parser;

    /**
     * @param BookParserInterface $parser
     */
    public function setParser(BookParserInterface $parser): void
    {
        $this->parser = $parser;
    }

    /**
     * Parses ebook metadata using set up parser.
     *
     * @param SplFileInfo $file File
     *
     * @return ParsedData
     *
     * @throws \Exception
     */
    public function parse(SplFileInfo $file): ParsedData
    {
        if (null === $this->parser) {
            throw new \Exception(sprintf('Book parser did not set'));
        }

        return $this->parser->parse($file);
    }

    /**
     * Construct proper file name.
     *
     * @param ParsedData $data      Ebook metadata
     * @param string     $extension File extension
     *
     * @return string
     */
    public function getProperFileName(ParsedData $data, string $extension): string
    {
        $name = '';
        // TODO: check old russian

        if ($data->getLanguage() !== BookLangUtils::LANG_RU) {
            $name .= sprintf('%s ', BookLangUtils::LANG_MAP[$data->getLanguage()]);
        }

        $author = '';
        $authorData = $data->getAuthors();

        foreach ($authorData as $i => $item) {
            if ($i > 1) {
                $author = rtrim(rtrim($author), ',');
                $author .= ' и др.';
                break;
            }

            if (false === empty($item->getLastName())) {
                $author .= sprintf('%s', ucfirst($item->getLastName()));
            }

            if (false === empty($item->getFirstName())) {
                $author .= sprintf(' %s.', mb_strtoupper(mb_substr($item->getFirstName(), 0, 1)));
            }

            if (false === empty($item->getMiddleName())) {
                $author .= sprintf('%s.', mb_strtoupper(mb_substr($item->getMiddleName(), 0, 1)));
            }

            if (count($authorData) >= 2 && $i < (count($authorData) - 1)) {
                $author .= ', ';
            }
        }

        if (false === empty($author)) {
            $name .= sprintf('%s - %s', $author, $this->sanitizeString($data->getTitle()));
        } else {
            $name .= $this->sanitizeString($data->getTitle());
        }

        if (1 !== $data->getEdition()) {
            if (BookLangUtils::LANG_RU !== $data->getLanguage()) {
                $name .= sprintf(' (%d ed.)', $data->getEdition());
            } else {
                $name .= sprintf(' (%d-е изд.)', $data->getEdition());
            }
        }

        if (false === empty($data->getIssueDate())) {
            $name .= sprintf(' [%d]', $data->getIssueDate());
        }

        $name .= sprintf('.%s', $extension);

        return $name;
    }

    /**
     * Sanitizes string from all symbols not allowed for file naming and other junk chunks.
     *
     * @param string $str String to sanitize
     *
     * @return string
     */
    private function sanitizeString(string $str): string
    {
        $replaced = $str;

        foreach (self::RESTRICTED_CHUNKS as $chunk) {
            $replaced = str_replace($chunk, '', $replaced);
        }

        foreach (self::REPLACERS as $target => $replace) {
            $replaced = str_replace($target, $replace, $replaced);
        }

        return trim($replaced);
    }
}
