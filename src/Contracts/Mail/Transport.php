<?php

namespace Chukdo\Contracts\Mail;
/**
 * Interface des formatteurs.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Transport
{
    /**
     * @param string $from
     * @param string $to
     * @param string $message
     * @param string $headers
     * @param string $host
     *
     * @return bool
     */
    public function sendMail( string $from, string $to, string $message, string $headers, string $host = 'localhost' ): bool;
}
