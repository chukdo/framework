<?php

namespace Chukdo\Json;

use Iterator;

/**
 * Manipulation des tableaux.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Iterate implements Iterator
{
    /**
     * @var array
     */
    protected array $arr = [];

    /**
     * @var int
     */
    protected int $offset = 0;

    /**
     * Iterate constructor.
     *
     * @param array $arr
     */
    public function __construct( array $arr = [] )
    {
        $this->arr = array_values( $arr );
    }

    /**
     * @return mixed|void
     */
    public function current()
    {
        return $this->arr[ $this->offset ];
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->offset;
    }

    public function next(): void
    {
        ++$this->offset;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return isset( $this->arr[ $this->offset ] );
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count( $this->arr );
    }

    /**
     * @return bool
     */
    public function empty(): bool
    {
        return count( $this->arr ) === 0;
    }

    /**
     * @param iterable $merge
     *
     * @return Iterate
     */
    public function merge( Iterable $merge ): self
    {
        foreach ( $merge as $append ) {
            $this->append( $append );
        }

        return $this;
    }

    /**
     * @param $append
     *
     * @return Iterate
     */
    public function append( $append ): self
    {
        $this->arr[] = $append;

        return $this;
    }

    /**
     * @param string $glue
     *
     * @return string
     */
    public function join( string $glue ): string
    {
        return implode( $glue, $this->arr );
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->arr;
    }

    /**
     * @return mixed|null
     */
    public function getFirst()
    {
        $first = reset( $this->arr );

        return $first
            ?: null;
    }

    /**
     * @return mixed|null
     */
    public function getLast()
    {
        $end = end( $this->arr );

        return $end
            ?: null;
    }

    /**
     * @return mixed|null
     */
    public function getFirstAndRemove()
    {
        $this->rewind();

        return array_shift( $this->arr );
    }

    public function rewind(): void
    {
        $this->offset = 0;
    }

    /**
     * @return mixed|null
     */
    public function getLastAndRemove()
    {
        $this->rewind();

        return array_pop( $this->arr );
    }

    /**
     * @return mixed|null
     */
    public function getNextAndRemove()
    {
        $offset = $this->offset;
        $next   = $this->getNext();
        if ( $next !== null ) {
            unset( $this->arr[ $offset ] );

            return $next;
        }

        return null;
    }

    /**
     * @return mixed|null
     */
    public function getNext()
    {
        if ( isset( $this->arr[ $this->offset ] ) ) {
            return $this->arr[ $this->offset++ ];
        }

        return null;
    }
}
