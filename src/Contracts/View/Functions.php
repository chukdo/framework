<?php namespace Chukdo\Contracts\View;

use Chukdo\View\View;

/**
 * Interface d'enregistrement des functions pour le moteur de vue
 *
 * @package    Contracts
 * @version    1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Interface Functions
{
    /**
     * @param View $view
     */
    public function register( View $view ): void;
}