<?php

namespace Chukdo\Contracts\Logger;
/**
 * Interface des formatteurs.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Formatter
{
    /**
     * Formatte un enregistrement.
     *
     * @param array $record
     *
     * @return mixed
     */
    public function sendMail( array $record );
}
