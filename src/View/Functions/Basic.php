<?php namespace Chukdo\View\Functions;

Use \Closure;
Use \Chukdo\Contracts\View\Functions as FunctionsInterface;
Use \Chukdo\View\View;
Use \Chukdo\Helper\Str;

/**
 * Fonctions basic pour le moteur de vue
 *
 * @package     View
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Basic implements FunctionsInterface
{
    /**
     * @param View $view
     */
    public function register(View $view): void
    {
        foreach (get_class_methods($this) as $method) {
            if ($method != 'register') {
                $view->registerFunction($method, Closure::fromCallable([$this, $method]));
            }
        }
    }

    /**
     * @param string $data
     * @param string $search
     * @return bool
     */
    public function contain(string $data, string $search): bool
    {
        return Str::contain($data, $search);
    }

    /**
     * @param string $data
     * @return string
     */
    public function removeSpecialChars(string $data): string
    {
        return Str::removeSpecialChars($data);
    }


}