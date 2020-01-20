<?php

namespace Booxtract\Services\Parsers;

use Booxtract\DataObjects\BookPersonData;
use Booxtract\DataObjects\ParsedData;
use Booxtract\Services\BookLangUtils;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Finder\SplFileInfo;

/**
 * FictionBook metadata parser.
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

        $isFiction = $this->getIsFiction();
        $data->setIsFiction($isFiction);

        if (true === $isFiction) {
            $data->setIsPoetry($this->getIsPoetry());
        }

        $titlesData = $this->processTitles($data->getAuthors());

        if (false === empty($titlesData['subtitle'])) {
            $data->setSubtitle($titlesData['subtitle']);
        }

        $data->setTitle($titlesData['title'])
            ->setEdition(1)
            ->setIssueDate($this->getIssueDate())
            ->setLanguage(!empty($this->collectedData['title-info']['lang']) ? mb_strtolower($this->collectedData['title-info']['lang']) : BookLangUtils::LANG_RU)
            ->setPublisherName(!empty($this->collectedData['publish-info']['publisher']) ? $this->collectedData['publish-info']['publisher'] : null)
            ->setCity(!empty($this->collectedData['publish-info']['city']) ? $this->collectedData['publish-info']['city'] : null)
            ->setIsbn(!empty($this->collectedData['publish-info']['isbn']) ? $this->collectedData['publish-info']['isbn'] : null)
            ->setOriginLanguage(!empty($this->collectedData['title-info']['src-lang']) ? $this->collectedData['title-info']['src-lang'] : null)
            ->setOriginTitle(!empty($this->collectedData['src-title-info']['book-title']) ? $this->collectedData['src-title-info']['book-title'] : null);

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
     * Analyzes collected data and trying to split subtitle and title of the book.
     *
     * @param array $authorData Authors data
     *
     * @return array ['title' => (string), 'subtitle' => (string)]
     */
    private function processTitles(array $authorData): array
    {
        $output = [];
        $selected = ['title' => '', 'subtitle' => ''];

        if (true === isset($this->collectedData['title-info'])) {
            if (false === empty($this->collectedData['title-info']['book-title'])) {
                $title = $this->collectedData['title-info']['book-title'];
                $title = str_replace('[', '(', $title);
                $title = str_replace(']', ')', $title);
                $output['title-info'] = $this->splitTitle($title);
            }
        }

        if (true === isset($this->collectedData['publish-info'])) {
            if (false === empty($this->collectedData['publish-info']['book-name'])) {
                $title = $this->collectedData['publish-info']['book-name'];
                $title = str_replace('[', '(', $title);
                $title = str_replace(']', ')', $title);
                $output['publish-info'] = $this->splitTitle($title);
            }
        }

        if (true === isset($this->collectedData['src-title-info'])) {
            if (false === empty($this->collectedData['src-title-info']['book-title'])) {
                $title = $this->collectedData['src-title-info']['book-title'];
                $title = str_replace('[', '(', $title);
                $title = str_replace(']', ')', $title);
                $output['src-title-info'] = $this->splitTitle($title);
            }
        }

        // naming logics

        if (true === isset($output['title-info'])) {
            foreach ($output['title-info'] as $item) {
                $selected['title'] = $item['title'];

                if (false === empty($item['subtitle'])) {
                    $selected['subtitle'] = $item['subtitle'];
                    break;
                }
            }
        }

        if (true === empty($selected) || true === empty($selected['subtitle'])) {
            if (false === empty($output['publish-info'])) {
                foreach ($output['publish-info'] as $item) {
                    if (false === empty($item['subtitle'])
                        && !$this->isBibliographyDescription($item['title'])
                        && !$this->isBibliographyDescription($item['subtitle'])
                    ) {
                        // clean up
                        $publishSelected = $item;

                        foreach ($item as $key => $quant) {
                            $chunks = explode('/', $quant);

                            if (count($chunks) > 1) {
                                $publishSelected[$key] = trim($chunks[0]);
                            }
                        }

                        // check for author
                        /** @var BookPersonData $author */
                        foreach ($authorData as $author) {
                            $publishSelected['title'] = $this->cleanAuthorNameFromTitle($publishSelected['title'], $author);
                            $publishSelected['subtitle'] = $this->cleanAuthorNameFromTitle($publishSelected['subtitle'], $author);
                        }

                        break;
                    }
                }

                if (false === empty($publishSelected['title'])) {
                    if (false === isset($selected['title']) && strlen($publishSelected['title']) > 2) {
                        $selected['title'] = $publishSelected['title'];
                    }
                }

                // если определили подзаголовок
                // TODO: проверка на сборник
                if (false === empty($publishSelected['subtitle']) && strlen(trim($publishSelected['subtitle'])) > 2) {
                    if (true === isset($selected['title']) && false === mb_strpos($selected['title'], $publishSelected['subtitle'])) {
                        $position = mb_strpos($publishSelected['subtitle'], $selected['title']);

                        if (false !== $position) {
                            $subtitle = str_replace($selected['title'], '', $publishSelected['subtitle']);
                            $subtitle = trim($subtitle, '.');
                            $subtitle = trim($subtitle, ':');
                            $selected['subtitle'] = $subtitle;
                        } else {
                            $selected['subtitle'] = $publishSelected['subtitle'];
                        }
                    }
                }
            }
        }

        foreach ($selected as &$item) {
            $item = preg_replace('/^\s+/', '', $item);
            $item = preg_replace('/\s+$/', '', $item);
        }

        return $selected;
    }

    /**
     * Is given title string a bibliography description.
     *
     * @param string $title String
     *
     * @return string
     */
    private function isBibliographyDescription(string $title): string
    {
        return preg_match('/.\s?—/', $title);
    }

    /**
     * Cleans up authors name from title.
     *
     * @param string         $title  Title
     * @param BookPersonData $author Authors' data
     *
     * @return string
     */
    private function cleanAuthorNameFromTitle(string $title, BookPersonData $author): string
    {
        $found = false;
        $fullName = sprintf('%s %s %s', $author->getLastName(), $author->getFirstName(), $author->getMiddleName());
        $shortName = sprintf(
            '%s %s. %s.',
            $author->getLastName(),
            mb_strtoupper(mb_substr($author->getFirstName(), 0, 1)),
            mb_strtoupper(mb_substr($author->getMiddleName(), 0, 1))
        );

        if (mb_strpos($title, $fullName)) {
            $found = true;
            $title = str_replace($fullName, '', $title);
        }

        if (mb_strpos($title, $shortName)) {
            $found = true;
            $title = str_replace($shortName, '', $title);
        }

        if (false === $found) {
            $title = str_replace($author->getLastName(), '', $title);
            $title = str_replace($author->getFirstName(), '', $title);
        }

        return trim($title);
    }

    /**
     * Trying to split book name for title and subtitle several ways.
     *
     * @param string $srcString Books title
     *
     * @return array
     */
    private function splitTitle(string $srcString): array
    {
        $output = [];

        // 1. try to split by point
        $output['point'] = $this->explodeTitle($srcString, '.');
        // 2. try to split by colon / semicolon
        $output['semicolon'] = $this->explodeTitle($srcString, ';');
        $output['colon'] = $this->explodeTitle($srcString, ':');
        // 3. try to split by slash
        $output['slash'] = $this->explodeTitle($srcString, '/');
        // 4. try to split by marks (?, !)
        $output['question'] = $this->explodeTitle($srcString, '?');
        $output['exclamation'] = $this->explodeTitle($srcString, '!');

        // 5. try to extract from parentheses
        if (true === empty($output)) {
            $template = [
                'title' => $srcString,
                'subtitle' => '',
            ];

            $matches = [];
            preg_match('#\((.*?)\)#', $srcString, $matches);

            if (false === empty($matches) && true === isset($matches[1])) {
                $template['subtitle'] = $matches[1];
                $template['title'] = trim(str_replace($matches[0], '', $srcString));
                $output['parentheses'] = $template;
            }
        }

        return $output;
    }

    /**
     * Explode string with a given delimiter on two chunks: before the first delimiter and other part.
     *
     * @param string $srcString String
     * @param string $delimiter Delimiter
     *
     * @return array
     */
    private function explodeTitle(string $srcString, string $delimiter): array
    {
        $template = [
            'title' => '',
            'subtitle' => '',
        ];

        $title = $srcString;
        $subTitle = '';
        $chunks = explode($delimiter, $srcString);

        if (true === is_array($chunks) && count($chunks) > 1) {
            $title = $chunks[0];

            foreach ($chunks as $pos => $chunk) {
                if (0 === $pos) {
                    continue;
                }

                if (count($chunks) > 2) {
                    $subTitle .= sprintf('%s %s', ($pos === 1) ? '' : $delimiter, trim($chunk));
                } else {
                    $subTitle .= trim($chunk);
                }
            }
        }

        $template['title'] = $title;

        if (false === empty($subTitle)) {
            $firstLetter = mb_strtoupper(mb_substr($subTitle, 0, 1));
            $template['subtitle'] = sprintf('%s%s', $firstLetter, mb_substr($subTitle, 1));
        }

        return $template;
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
