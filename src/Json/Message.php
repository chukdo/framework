<?php

namespace Chukdo\Json;

/**
 * Gestion des messages.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Message extends Json
{
    /**
     * @var
     */
    protected $name;

    /**
     * Message constructor.
     * @param string $name
     */
    public function __construct( string $name )
    {
        $this->name = $name;

        parent::__construct([]);
    }

    /**
     * @param string|null $title
     * @param string|null $color
     * @param string|null $widthFirstCol
     * @return string
     */
    public function toHtml( string $title = null, string $color = null, string $widthFirstCol = null ): string
    {
        return parent::toHtml($this->name, $color);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'result'   => $this->name,
            'messages' => $this->getArrayCopy(),
        ];
    }
}
