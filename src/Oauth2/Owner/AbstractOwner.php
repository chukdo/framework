<?php

namespace Chukdo\Oauth2\Owner;

use Chukdo\Contracts\Oauth2\Owner as OwnerInterface;
use Chukdo\Json\Json;

/**
 * Oauth2 token
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Abstract Class AbstractOwner implements OwnerInterface
{
    /**
     * @var string
     */
    protected string $id;

    /**
     * @var string
     */
    protected ?string $company = null;

    /**
     * @var string
     */
    protected ?string $firstName = null;

    /**
     * @var string
     */
    protected ?string $lastName = null;

    /**
     * @var string
     */
    protected ?string $surname = null;

    /**
     * @var string
     */
    protected ?string $photoUrl = null;

    /**
     * @var string
     */
    protected ?string $locale = 'fr';

    /**
     * @var string
     */
    protected ?string $email = null;

    /**
     * @var string
     */
    protected ?string $address = null;

    /**
     * @var string
     */
    protected ?string $zipCode = null;

    /**
     * @var string
     */
    protected ?string $city = null;

    /**
     * @var string
     */
    protected ?string $country = null;

    /**
     * @var Json
     */
    protected Json $values;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getSurname(): string
    {
        return $this->surname;
    }

    /**
     * @return string|null
     */
    public function getPhotoUrl(): ?string
    {
        return $this->photoUrl;
    }

    /**
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @return string|null
     */
    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @return Json
     */
    public function values(): Json
    {
        return $this->values;
    }


}