<?php

namespace Booxtract\Services\Parsers;

use Booxtract\DataObjects\BookPersonData;
use Booxtract\DataObjects\ParsedData;
use Booxtract\Services\BookLangUtils;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Finder\SplFileInfo;

/**
 * FictionBook metadata parser..
 */
class FictionBookParser implements BookParserInterface
{
    /**
     * Files searching mask.
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
        $isFiction = $this->getIsFiction();
        $data->setIsFiction($isFiction);

        if (true === $isFiction) {
            $data->setIsPoetry($this->getIsPoetry());
        }

        $data->setTitle($this->getBookTitle())
            ->setEdition(1)
            ->setIssueDate($this->getIssueDate())
            ->setLanguage(!empty($this->collectedData['title-info']['lang']) ? $this->collectedData['title-info']['lang'] : BookLangUtils::LANG_RU)
            ->setPublisherName(!empty($this->collectedData['publish-info']['publisher']) ? $this->collectedData['publish-info']['publisher'] : null)
            ->setCity(!empty($this->collectedData['publish-info']['city']) ? $this->collectedData['publish-info']['city'] : null)
            ->setIsbn(!empty($this->collectedData['publish-info']['isbn']) ? $this->collectedData['publish-info']['isbn'] : null)
            ->setOriginLanguage(!empty($this->collectedData['title-info']['src-lang']) ? $this->collectedData['title-info']['src-lang'] : null)
            ->setOriginTitle(!empty($this->collectedData['src-title-info']['book-title']) ? $this->collectedData['src-title-info']['book-title'] : null);

        foreach ($this->collectedData['title-info']['author'] as $author) {
            $data->setAuthor($author);
        }

        if (false === empty($this->collectedData['src-title-info']['author'])) {
            foreach ($this->collectedData['src-title-info']['author'] as $author) {
                $data->setOriginAuthor($author);
            }
        }

        if (false === empty($this->collectedData['title-info']['translator'])) {
            foreach ($this->collectedData['title-info']['translator'] as $translator) {
                $data->setTranslator($translator);
            }
        }

        return $data;
    }

    /**
     * Detects, is current book a fiction or not (not by default).
     *
     * @return bool
     */
    private function getIsFiction(): bool
    {
        $matrix = [
            'fiction' => 0,
            'nonfiction' => 0,
        ];

        if (false === empty($this->collectedData['title-info']['genre'])) {
            foreach ($this->collectedData['title-info']['genre'] as $genre) {
                $detectFiction = preg_match_all("/prose|detective|thriller|sf_|horror|dramaturgy|det_|humor|^story|child|poetry|adventure/", $genre);

                if (true === empty($detectFiction)) {
                    $checkAdv = preg_match_all("/adv_/", $genre);

                    if (false === empty($checkAdv) && !strpos(mb_strtolower($genre), 'adv_geo') && !strpos(mb_strtolower($genre), 'adv_animal')) {
                        $detectFiction = true;
                    }
                }

                if (false === empty($detectFiction)) {
                    $matrix['fiction'] += 1;
                } else {
                    $matrix['nonfiction'] += 1;
                }
            }
        }

        return $matrix['fiction'] > $matrix['nonfiction'];
    }

    /**
     * Detects is current book is a poetry book.
     *
     * @return bool
     */
    private function getIsPoetry(): bool
    {
        if (false === empty($this->collectedData['title-info']['genre'])) {
            foreach ($this->collectedData['title-info']['genre'] as $genre) {
                if (false === empty(preg_match_all("/poetry/", $genre))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get book title.
     *
     * @return string
     */
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

    /**
     * Return issue date, if collected.
     *
     * @return string|null
     */
    private function getIssueDate(): ?string
    {
        $dates = [];

        if (false === empty($this->collectedData['publish-info']['year'])) {
            $dates[] = $this->collectedData['publish-info']['year'];
        }

        if (false === empty($this->collectedData['title-info']['date'])) {
            $dates[] = $this->collectedData['title-info']['date'];
        }

        $finalDate = null;

        foreach ($dates as $date) {
            if (false === empty($date)) {
                // not ony year case
                if (strlen($date) > 4) {
                    try {
                        $dateTime = new \DateTime($date);
                        $finalDate = $dateTime->format('Y');
                    } catch (\Exception $ex) {}
                } else {
                    $finalDate = $date;
                }

                if (false === empty($finalDate)) {
                    break;
                }
            }
        }

        return $finalDate;
    }

    /**
     * Collects metadata.
     */
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

    /**
     * Helper method to collect person's metadata.
     *
     * @param Crawler $nodes Node list
     *
     * @return BookPersonData
     */
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
