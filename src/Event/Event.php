<?php

namespace Chukdo\Event;

/**
 * Gestion des evenements.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Event
{
    /**
     * Tableau des ecouteurs.
     * @param array $listeners
     */
    protected $listeners = [];

    /**
     * Vide un Evenement (Ecouteur).
     * @param string $event evenement sur lequel ecouter
     */
    public function flush( string $event ): void {
        if( isset($this->listeners[ $event ]) ) {
            $this->listeners[ $event ] = [];
        }
    }

    /**
     * Ecouteur.
     * @param string $event    evenement sur lequel ecouter
     * @param mixed  $listener ecouteur (closure)
     */
    public function listen( string $event, $listener ): void {
        if( !isset($this->listeners[ $event ]) ) {
            $this->listeners[ $event ] = [];
        }

        $this->listeners[ $event ][] = $listener;
    }

    /**
     * Trigger
     * Declenche les triggers en cascade (dans l'ordre des ajouts).
     * @param string       $event   evenement sur lequel declencher le trigger
     * @param string|array $payload parametres a passer
     */
    public function fire( string $event, $payload = [] ): void {
        if( !is_array($payload) ) {
            $payload = [ $payload ];
        }

        foreach( $this->getListeners($event) as $listener ) {
            /* Stop la propagation si une reponse = false */
            if( call_user_func_array($listener,
                    $payload) === false ) {
                return;
            }
        }
    }

    /**
     * Retourne la liste des ecouteurs.
     * @param string $event evenement sur lequel ecouter
     * @return array
     */
    public function getListeners( string $event ): array {
        if( isset($this->listeners[ $event ]) ) {
            return $this->listeners[ $event ];
        }

        return [];
    }
}
