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
     * @var array
     */
    private $authors;

    /**
     * @var array|null
     */
    private $translators;

    /**
     * @var array|null
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

    public function __construct()
    {
        $this->isFiction = false;
        $this->isPoetry = false;
        $this->isCompilation = false;
        $this->totalParts = 1;
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

    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function setAuthor(BookPersonData $author): self
    {
        $this->authors[] = $author;

        return $this;
    }

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
}
