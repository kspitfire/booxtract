<?php

namespace Booxtract\Services\Parsers;

use Booxtract\DataObjects\BookPersonData;
use Booxtract\DataObjects\ParsedData;
use Booxtract\Services\BookLangUtils;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Парсер метаданных для формата fb2.
 */
class FictionBookParser implements BookParserInterface
{
    /**
     * Маска для поиска файла.
     */
    const FILE_MASK = '*.fb2';

    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @var array
     */
    private $collectedData;

    public function __construct()
    {
        $this->collectedData = [];
    }

    /**
     * {@inheritdoc}
     */
    public function parse(SplFileInfo $file): ParsedData
    {
        $this->collectedData['file-name'] = $file->getFilename();
        $this->crawler = new Crawler();
        $this->crawler->addXmlContent($file->getContents());
        $this->collectedData = [];
        $this->collectData();

        $data = new ParsedData();
        $data->setTitle($this->getBookTitle())
            ->setEdition(1)
            ->setIssueDate($this->getIssueDate())
            ->setLanguage(!empty($this->collectedData['title-info']['lang']) ? $this->collectedData['title-info']['lang'] : BookLangUtils::LANG_RU)
            ->setPublisherName(!empty($this->collectedData['publish-info']['publisher']) ? $this->collectedData['publish-info']['publisher'] : null)
            ->setCity(!empty($this->collectedData['publish-info']['city']) ? $this->collectedData['publish-info']['city'] : null)
            ->setIsbn(!empty($this->collectedData['publish-info']['isbn']) ? $this->collectedData['publish-info']['isbn'] : null);

        foreach ($this->collectedData['title-info']['author'] as $author) {
            $data->setAuthor($author);
        }

        if (false === empty($this->collectedData['title-info']['translator'])) {
            foreach ($this->collectedData['title-info']['translator'] as $translator) {
                $data->setTranslator($translator);
            }
        }

        return $data;
    }

    private function getBookTitle(): string
    {
        $titles = [];

        // 1. try title-info
        if (false === empty($this->collectedData['title-info']['book-title'])) {
            $titles[] = $this->collectedData['title-info']['book-title'];
        }

        // 2. try publish-info
        if (false === empty($this->collectedData['publish-info']['book-name'])) {
            $titles[] = $this->collectedData['publish-info']['book-name'];
        }

        // 3. try src-title-info
        if (true === empty($titles) && false === empty($this->collectedData['src-title-info']['book-title'])) {
            $titles[] = $this->collectedData['src-title-info']['book-title'];
        }

        // 4. try filename (TODO)

        // TODO: compare titles and choose the best (criteria?)
        // TODO: subtract subtitle
        // TODO: cut long titles

        return !empty($titles[0]) ? $titles[0] : '';
    }

    private function getIssueDate(): ?string
    {
        $dates = [];

        if (false === empty($this->collectedData['publish-info']['year'])) {
            $dates[] = $this->collectedData['publish-info']['year'];
        }

        if (false === empty($this->collectedData['title-info']['year'])) {
            $dates[] = $this->collectedData['title-info']['year'];
        }

        return !empty($dates[0]) ? $dates[0]: null;
    }

    private function collectData()
    {
        $this->crawler->children()->each(function (Crawler $crawler) {
            if ($crawler->nodeName() === 'description') {
                $crawler->children()->each(function (Crawler $child) {

                    // title-info
                    if ($child->nodeName() === 'title-info') {
                        $authors = [];
                        $translators = [];

                        $child->children()->each(function (Crawler $chunk) use (&$authors, &$translators) {
                            $key = $chunk->nodeName();

                            switch ($key) {
                                case 'lang':
                                case 'src-lang':
                                case 'date':
                                case 'book-title':
                                    if (false === empty($chunk->text())){
                                        $this->collectedData['title-info'][$key] = $chunk->text();
                                    }
                                    break;
                                case 'translator':
                                    $translators[] = $this->collectPersonData($chunk);
                                    break;
                                case 'author':
                                    $authors[] = $this->collectPersonData($chunk);
                                    break;
                                case 'genre':
                                    if (false === empty($chunk->text())) {
                                        $this->collectedData['title-info'][$key][] = $chunk->text();
                                    }
                                    break;
                            }
                        });

                        $this->collectedData['title-info']['author'] = $authors;
                        $this->collectedData['title-info']['translator'] = $translators;
                    }

                    // src-title-info
                    if ($child->nodeName() === 'src-title-info') {
                        $authors = [];

                        $child->children()->each(function (Crawler $chunk) use (&$authors) {
                            $key = $chunk->nodeName();

                            switch ($key) {
                                case 'date':
                                case 'book-title':
                                    if (false === empty($chunk->text())) {
                                        $this->collectedData['src-title-info'][$key] = $chunk->text();
                                    }
                                    break;
                                case 'author':
                                    $authors[] = $this->collectPersonData($chunk);
                                    break;
                            }
                        });

                        $this->collectedData['src-title-info']['author'] = $authors;
                    }

                    // publish-info
                    if ($child->nodeName() === 'publish-info') {
                        $child->children()->each(function (Crawler $chunk) {
                            $key = $chunk->nodeName();

                            switch ($key) {
                                case 'book-name':
                                case 'publisher':
                                case 'city':
                                case 'year':
                                case 'isbn':
                                    if (false === empty($chunk->text())) {
                                        $this->collectedData['publish-info'][$key] = $chunk->text();
                                    }
                                    break;
                            }
                        });
                    }
                });
            }
        });
    }

    private function collectPersonData(Crawler $nodes): BookPersonData
    {
        $person = new BookPersonData();

        $nodes->children()->each(function (Crawler $node) use (&$person) {
            if (false === empty($node->text())) {
                switch ($node->nodeName()) {
                    case 'first-name':
                        if (false === empty($node->text())) {
                            $person->setFirstName($node->text());
                        }
                        break;
                    case 'last-name':
                        if (false === empty($node->text())) {
                            $person->setLastName($node->text());
                        }
                        break;
                    case 'middle-name':
                        $person->setMiddleName($node->text());
                        break;
                }
            }
        });

        return $person;
    }
}
