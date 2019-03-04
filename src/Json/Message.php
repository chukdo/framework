<?php namespace Chukdo\Json;

/**
 * Gestion des messages
 *
 * @package     Json
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Message extends Json
{
    /**
     * Message constructor.
     */
    public function __construct()
    {
        parent::__construct([
            'result'    => '',
            'messages'  => []
        ]);
    }

    /**
     * @param string $message
     * @return Message
     */
    public function notice(string $message): self
    {
        $this
            ->offsetSet('result', 'notice')
            ->offsetGet('messages')
            ->append($message);

        return $this;
    }

    /**
     * @param string $message
     * @return Message
     */
    public function success(string $message): self
    {
        $this
            ->offsetSet('result', 'success')
            ->offsetGet('messages')
            ->append($message);

        return $this;
    }

    /**
     * @param string $message
     * @return Message
     */
    public function warning(string $message): self
    {
        $this
            ->offsetSet('result', 'warning')
            ->offsetGet('messages')
            ->append($message);

        return $this;
    }

    /**
     * @param string $message
     * @return Message
     */
    public function error(string $message): self
    {
        $this
            ->offsetSet('result', 'error')
            ->offsetGet('messages')
            ->append($message);

        return $this;
    }
}
