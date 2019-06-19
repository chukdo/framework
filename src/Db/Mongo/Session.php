<?php

namespace Chukdo\Db\Mongo;

use MongoDB\Driver\Session as MongoSession;

/**
 * Mongo Session TRAIT.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Trait Session
{
    /**
     * @return MongoSession
     */
    public function session(): MongoSession
    {
        if ( isset($this->options[ 'session' ]) ) {
            return $this->options[ 'session' ];
        }
        else {
            return $this->options[ 'session' ] = $this->collection->mongo()
                ->mongo()
                ->startSession();
        }
    }

    /**
     * @param MongoSession $session
     * @return Session
     */
    public function setSession( MongoSession $session ): self
    {
        if ( isset($this->options[ 'session' ]) ) {
            $this->options[ 'session' ]->endSession();
        }

        $this->options[ 'session' ] = $session;

        return $this;
    }
}