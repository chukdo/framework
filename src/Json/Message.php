<?php

namespace Chukdo\Json;

use Chukdo\Helper\To;

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
     * @param string $render
     */
    public function __construct( string $name )
    {
        $this->name = $name;

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
     * @param string|null $title
     * @param string|null $color
     * @param string|null $widthFirstCol
     * @return string
     */
    public function toHtml( string $title = null, string $color = null, string $widthFirstCol = null ): string
    {
        return To::html($this,
            $title
                ?: $this->name,
            $color,
            $widthFirstCol);
    }

    /**
     * @param string|null $title
     * @return string
     */
    public function toConsole( string $title = null ): string
    {
        ob_start();
        $tree = new \cli\Tree();
        $tree->setData($this->toArray());
        $tree->setRenderer(new \cli\tree\Ascii());
        $tree->display();
        $stdout = ob_get_contents();
        ob_end_clean();

        return $stdout;
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
