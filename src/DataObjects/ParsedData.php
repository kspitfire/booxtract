<?php

namespace Booxtract\DataObjects;

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
     * @var []BookPersonData
     */
    private $authors;

    /**
     * @var []BookPersonData|null
     */
    private $translators;

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
}