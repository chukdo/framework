<?php

namespace Chukdo\Json;

use Chukdo\Http\Response;

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
     * @var
     */
    protected $render;

    /**
     * Message constructor.
     * @param string $name
     * @param string $render
     */
    public function __construct( string $name, string $render = 'html' )
    {
        $this->name   = $name;
        $this->render = $render;

        parent::__construct([]);
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return $this->render;
    }

    /**
     * @param string|null $title
     * @param string|null $color
     * @param string|null $widthFirstCol
     * @return string
     */
    public function toHtml( string $title = null, string $color = null, string $widthFirstCol = null ): string
    {
        return parent::toHtml($title
            ?: $this->name,
            $color,
            $widthFirstCol);
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
