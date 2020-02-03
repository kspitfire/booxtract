<?php

namespace Booxtract\DataObjects;

/**
 * Person metadata data object.
 */
class BookPersonData
{
    /**
     * First name.
     *
     * @var string
     */
    private $firstName;

    /**
     * Last name.
     *
     * @var string
     */
    private $lastName;

    /**
     * Middle name.
     *
     * @var string
     */
    private $middleName;

    /**
     * Nickname or something else.
     *
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
