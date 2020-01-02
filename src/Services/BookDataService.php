<?php

namespace Booxtract\Services;

use Booxtract\DataObjects\ParsedData;
use Booxtract\Services\Parsers\BookParserInterface;
use Symfony\Component\Finder\SplFileInfo;

class BookDataService
{
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
     * Парсит данные о книге из файла.
     *
     * @param SplFileInfo $file Файл электронной книге
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
     * Возвращает правильное название файла.
     *
     * @param ParsedData $data      Метаданные книги
     * @param string     $extension Расширение файла
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

        $name .= sprintf('%s - %s', $author, $data->getTitle());

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

    private function sanitizeString(string $str): string
    {

    }
}
