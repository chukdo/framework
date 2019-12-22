<?php

namespace Chukdo\Db\Mongo\Aggregate;

use Chukdo\DB\Mongo\Collection;
use Chukdo\Json\Json;

/**
 * Aggregate.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Aggregate
{
    /**
     * @var Collection
     */
    protected Collection $collection;

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var array
     */
    protected array $pipe = [];

    /**
     * Aggregate constructor.
     *
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->collection = $collection;
    }

    /**
     * @return PipelineStage
     */
    public function stage(): PipelineStage
    {
        return $this->pipe[] = new PipelineStage();
    }

    /**
     * @param bool $allowDiskUse
     * @param bool $bypassDocumentValidation
     *
     * @return Json
     */
    public function all( bool $allowDiskUse = false, bool $bypassDocumentValidation = false ): Json
    {
        return new Json( $this->cursor( [ 'allowDiskUse'             => $allowDiskUse,
                                          'bypassDocumentValidation' => $bypassDocumentValidation,
                                          'useCursor'                => true, ] ) );
    }

    /**
     * @param array $options
     *
     * @return Cursor
     */
    public function cursor( array $options = [] ): Cursor
    {
        return new Cursor( $this->collection->client()
                                            ->aggregate( $this->projection(), $options ) );
    }

    /**
     * @return array
     */
    public function projection(): array
    {
        $projection = [];

        foreach ( $this->pipe as $stage ) {
            foreach ( $stage->projection() as $k => $v ) {
                $projection[][ $k ] = $v;
            }
        }

        return $projection;
    }

    /**
     * @return Json
     */
    public function explain(): Json
    {
        return new Json( new Cursor( $this->collection->client()
                                                      ->aggregate( $this->projection(), [ 'explain'   => true,
                                                                                          'useCursor' => true, ] ) ) );
    }

}