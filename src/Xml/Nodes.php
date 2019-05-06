<?php

namespace Chukdo\Xml;

use Chukdo\Json\Json;

/**
 * Listes de noeuds XML.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Nodes extends Json
{
    /**
     * @param string $name
     * @param array  $params
     * @return $this|void
     */
    public function __call( string $name, array $params = [] )
    {
        foreach ( $this as $node ) {
            call_user_func_array([
                $node,
                $name,
            ],
                $params);
        }

        return $this;
    }
}
