<?php

namespace Booxtract\DataObjects;

/**
 * Ebook metadata data object.
 */
class ParsedData
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string|null
     */
    private $subtitle;

    /**
     * @var BookPersonData[]
     */
    private $authors;

    /**
     * @var BookPersonData[]|null
     */
    private $translators;

    /**
     * @var BookPersonData[]|null
     */
    private $originAuthors;

    /**
     * @var string|null
     */
    private $originTitle;

    /**
     * @var string|null
     */
    private $originSubtitle;

    /**
     * @var string|null
     */
    private $originIssueDate;

    /**
     * @var string|null
     */
    private $originLanguage;

    /**
     * @var string|null
     */
    private $publisherName;

    /**
     * @var int|null
     */
    private $issueDate;

    /**
     * @var int
     */
    private $edition;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string|null
     */
    private $city;

    /**
     * @var string|null
     */
    private $isbn;

    /**
     * @var bool
     */
    private $isFiction;

    /**
     * @var bool
     */
    private $isPoetry;

    /**
     * @var bool
     */
    private $isCompilation;

    /**
     * @var int
     */
    private $totalParts;

    /**
     * @var string[]
     */
    private $genres;

    public function __construct()
    {
        $this->isFiction = false;
        $this->isPoetry = false;
        $this->isCompilation = false;
        $this->totalParts = 1;
        $this->genres = [];
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): self
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * @return BookPersonData[]
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function setAuthor(BookPersonData $author): self
    {
        $this->authors[] = $author;

        return $this;
    }

    /**
     * @return BookPersonData[]|null
     */
    public function getTranslators(): ?array
    {
        return $this->translators;
    }

    public function setTranslator(?BookPersonData $translator): self
    {
        if (false === empty($translator)) {
            $this->translators[] = $translator;
        }

        return $this;
    }

    public function getPublisherName(): ?string
    {
        return $this->publisherName;
    }

    public function setPublisherName(?string $publisherName): self
    {
        $this->publisherName = $publisherName;

        return $this;
    }

    public function getIssueDate(): ?int
    {
        return $this->issueDate;
    }

    public function setIssueDate(?int $issueDate): self
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    public function getEdition(): int
    {
        return $this->edition;
    }

    public function setEdition(int $edition): self
    {
        $this->edition = $edition;

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(?string $isbn): self
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getOriginTitle(): ?string
    {
        return $this->originTitle;
    }

    public function setOriginTitle(?string $originTitle): self
    {
        $this->originTitle = $originTitle;

        return $this;
    }

    public function getOriginSubtitle(): ?string
    {
        return $this->originSubtitle;
    }

    public function setOriginSubtitle(?string $originSubtitle): self
    {
        $this->originSubtitle = $originSubtitle;

        return $this;
    }

    public function getOriginIssueDate(): ?string
    {
        return $this->originIssueDate;
    }

    public function setOriginIssueDate(?string $originIssueDate): self
    {
        $this->originIssueDate = $originIssueDate;

        return $this;
    }

    public function getOriginLanguage(): ?string
    {
        return $this->originLanguage;
    }

    public function setOriginLanguage(?string $originLanguage): self
    {
        $this->originLanguage = $originLanguage;

        return $this;
    }

    public function getOriginAuthors(): ?array
    {
        return $this->originAuthors;
    }

    public function setOriginAuthor(?BookPersonData $author): self
    {
        if (false === empty($author)) {
            $this->originAuthors[] = $author;
        }

        return $this;
    }

    public function isFiction(): bool
    {
        return $this->isFiction;
    }

    public function setIsFiction(bool $isFiction): self
    {
        $this->isFiction = $isFiction;

        return $this;
    }

    public function isCompilation(): bool
    {
        return $this->isCompilation;
    }

    public function setIsCompilation(bool $isCompilation): self
    {
        $this->isCompilation = $isCompilation;

        return $this;
    }

    public function getTotalParts(): int
    {
        return $this->totalParts;
    }

    public function setTotalParts(int $totalParts): self
    {
        $this->totalParts = $totalParts;

        return $this;
    }

    public function isPoetry(): bool
    {
        return $this->isPoetry;
    }

    public function setIsPoetry(bool $isPoetry): self
    {
        $this->isPoetry = $isPoetry;

        return $this;
    }

    public function setGenre(string $genre): self
    {
        $this->genres[] = $genre;

        return $this;
    }

    public function getGenres(): array
    {
        return $this->genres;
    }

    public function getDataAsPrintableString(): string
    {
        $printableString = '';
        $printableString .= sprintf('<comment>Title:</comment> <info>%s</info>%s', $this->getTitle(), \PHP_EOL);

        if (!empty($this->getSubtitle())) {
            $printableString .= sprintf('<comment>Subitle:</comment> <info>%s</info>%s', $this->getSubtitle(), \PHP_EOL);
        }

        if (!empty($this->getLanguage())) {
            $printableString .= sprintf('<comment>Lang:</comment> <info>%s</info>%s', $this->getLanguage(), \PHP_EOL);
        }

        if (!empty($this->getGenres())) {
            $printableString .= sprintf('<comment>Genres:</comment> <info>%s</info>%s', implode(', ', $this->getGenres()), \PHP_EOL);
        }

        if (!empty($this->getCity())) {
            $printableString .= sprintf('<comment>City:</comment> <info>%s</info>%s', $this->getCity(), \PHP_EOL);
        }

        if (!empty($this->getPublisherName())) {
            $printableString .= sprintf('<comment>Publisher:</comment> <info>%s</info>%s', $this->getPublisherName(), \PHP_EOL);
        }

        if (!empty($this->getIssueDate())) {
            $printableString .= sprintf('<comment>Year:</comment> <info>%s</info>%s', $this->getIssueDate(), \PHP_EOL);
        }

        if (!empty($this->getEdition())) {
            $printableString .= sprintf('<comment>Edition:</comment> <info>%d</info>%s', $this->getEdition(), \PHP_EOL);
        }

        if (!empty($this->getIsbn())) {
            $printableString .= sprintf('<comment>ISBN:</comment> <info>%s</info>%s', $this->getIsbn(), \PHP_EOL);
        }

        if (!empty($this->isFiction())) {
            $printableString .= sprintf('<comment>Is Fiction:</comment> <info>%s</info>%s', $this->isFiction() ? 'Yes' : 'No', \PHP_EOL);
        }

        if (!empty($this->isCompilation())) {
            $printableString .= sprintf('<comment>Is Compilation:</comment> <info>%s</info>%s', $this->isCompilation() ? 'Yes' : 'No', \PHP_EOL);
        }

        if (!empty($this->isPoetry())) {
            $printableString .= sprintf('<comment>Is Poetry:</comment> <info>%s</info>%s', $this->isPoetry() ? 'Yes' : 'No', \PHP_EOL);
        }

        if ($this->getTotalParts() > 1) {
            $printableString .= sprintf('<comment>Total parts:</comment> <info>%d</info>%s', $this->getTotalParts(), \PHP_EOL);
        }

        if (!empty($this->getOriginTitle())) {
            $printableString .= sprintf('<comment>Title (origin):</comment> <info>%s</info>%s', $this->getOriginTitle(), \PHP_EOL);
        }

        if (!empty($this->getOriginSubtitle())) {
            $printableString .= sprintf('<comment>Subtitle (origin):</comment> <info>%s</info>%s', $this->getOriginSubtitle(), \PHP_EOL);
        }

        if (!empty($this->getOriginIssueDate())) {
            $printableString .= sprintf('<comment>Year (origin):</comment> <info>%s</info>%s', $this->getOriginIssueDate(), \PHP_EOL);
        }

        if (!empty($this->getOriginLanguage())) {
            $printableString .= sprintf('<comment>Lang (origin):</comment> <info>%s</info>%s', $this->getOriginLanguage(), \PHP_EOL);
        }

        if (!empty($this->getAuthors())) {
            $printableString .= '<comment>Authors:</comment>'.\PHP_EOL;

            foreach ($this->getAuthors() as $key => $author) {
                $printableString .= sprintf(
                    "%s)\tSurname: <info>%s</info>\tName: <info>%s</info>\tPatronymic: <info>%s</info>",
                    '<comment>'.($key + 1).'</comment>',
                    $author->getLastName().\PHP_EOL,
                    $author->getFirstName().\PHP_EOL,
                    $author->getMiddleName().\PHP_EOL.\PHP_EOL
                );
            }
        }

        if (!empty($this->getOriginAuthors())) {
            $printableString .= '<comment>Authors (origin):</comment>'.\PHP_EOL;

            foreach ($this->getOriginAuthors() as $key => $author) {
                $printableString .= sprintf(
                    "%s)\tSurname: <info>%s</info>\tName: <info>%s</info>\tPatronymic: <info>%s</info>",
                    '<comment>'.($key + 1).'</comment>',
                    $author->getLastName().\PHP_EOL,
                    $author->getFirstName().\PHP_EOL,
                    $author->getMiddleName().\PHP_EOL.\PHP_EOL
                );
            }
        }

        if (!empty($this->getTranslators())) {
            $printableString .= '<comment>Translators:</comment>'.\PHP_EOL;

            foreach ($this->getTranslators() as $key => $author) {
                $printableString .= sprintf(
                    "%s)\tSurname: <info>%s</info>\tName: <info>%s</info>\tPatronymic: <info>%s</info>",
                    '<comment>'.($key + 1).'</comment>',
                    $author->getLastName().\PHP_EOL,
                    $author->getFirstName().\PHP_EOL,
                    $author->getMiddleName().\PHP_EOL.\PHP_EOL
                );
            }
        }

        return $printableString;
    }
}
