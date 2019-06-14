<?php

namespace Chukdo\Contracts\Json;

use Chukdo\Json\Collect;
use Chukdo\Xml\Xml;

/**
 * Interface de gestion des documents JSON.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Json
{
    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return Xml
     */
    public function toXml(): Xml;

    /**
     * @return Collect
     */
    public function collect(): Collect;
}