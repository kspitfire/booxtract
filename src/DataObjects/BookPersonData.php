<?php

namespace Booxtract\DataObjects;

/**
 * Формат данных о человеке.
 */
class BookPersonData
{
    /**
     * Имя
     *
     * @var string
     */
    private $firstName;

    /**
     * Фамилия
     *
     * @var string
     */
    private $lastName;

    /**
     * Отчество
     *
     * @var string
     */
    private $middleName;

    /**
     * @var string
     */
    private $nameAlias;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(?string $middleName): self
    {
        $this->middleName = $middleName;

        return $this;
    }

    public function getNameAlias(): ?string
    {
        return $this->nameAlias;
    }

    public function setNameAlias(?string $nameAlias): self
    {
        $this->nameAlias = $nameAlias;

        return $this;
    }
}