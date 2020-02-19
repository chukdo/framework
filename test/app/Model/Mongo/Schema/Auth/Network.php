<?php

namespace App\Model\Mongo\Schema\Auth;

use Chukdo\Db\Mongo\Schema\Schema;

class Network extends Schema
{
    /**
     * @param array $schema
     *
     * @return $this
     */
    public function init( array $schema = [] ): self
    {
        parent::init( [
                          'type'       => 'object',
                          'required'   => [ 'name' ],
                          'properties' => [
                              'name'    => [
                                  'type'       => 'object',
                                  'required'   => [
                                      'fullname',
                                  ],
                                  'properties' => [
                                      'first'    => [ 'type' => 'string' ],
                                      'last'     => [ 'type' => 'string' ],
                                      'surname'  => [ 'type' => 'string' ],
                                      'fullname' => [ 'type' => 'string' ],
                                      'photo'    => [ 'type' => 'string' ],
                                  ],
                              ],
                              'address' => [
                                  'type'       => 'object',
                                  'required'   => [
                                      'street',
                                      'zipcode',
                                      'city',
                                      'location',
                                  ],
                                  'properties' => [
                                      'street'   => [ 'type' => 'string' ],
                                      'zipcode'  => [ 'type' => 'string' ],
                                      'city'     => [ 'type' => 'string' ],
                                      'country'  => [ 'type' => 'string' ],
                                      'location' => [
                                          'type'       => 'object',
                                          'required'   => [
                                              'type',
                                              'coordinates',
                                          ],
                                          'properties' => [
                                              'type'        => [
                                                  'type' => 'string',
                                                  'enum' => [ 'point' ],
                                              ],
                                              'coordinates' => [
                                                  'type'     => 'array',
                                                  'minItems' => 2,
                                                  'maxItems' => 2,
                                                  'items'    => [
                                                      'type' => 'decimal',
                                                  ],
                                              ],
                                          ],
                                      ],
                                  ],
                              ],
                          ],
                      ] );


        return $this;
    }

    // fucntion init
    // get schema existant
    // si vide
    // defini avec le defaultSchema de network
    // si non vide
    // je le complete via le differentiel
}
