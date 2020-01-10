<?php

namespace Chukdo\Contracts\Oauth2;

use Chukdo\Contracts\Json\Json;

/**
 * Interface Oauth2 Owner Details
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Owner
{
    /**
     * @return string|null
     */
    public function getId(): ?string;

    /**
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * @return Json
     */
    public function values(): Json;
}
