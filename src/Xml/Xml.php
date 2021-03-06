<?php

namespace Chukdo\Xml;

use DOMDocument;
use DOMElement;
use Throwable;

/**
 * Classe XML DOCUMENT, etend les fonctionnalites XML de PHP5
 * pour la creation rapide de document XML compatible DOM.
 *
 * @version   1.0.0
 * @copyright licence GPL, Copyright (C) 2008 Domingo
 * @since     15/01/2009
 * @author    Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Xml extends Node
{
    /**
     * @var DOMDocument
     */
    protected DOMDocument $xml;

    /**
     * @var string|null
     */
    private ?string $buffer;

    /**
     * Xml constructor.
     *
     * @param string      $name
     * @param string|null $uri
     */
    public function __construct( string $name = 'xml', string $uri = null )
    {
        $this->xml                     = new DOMDocument( '1.0', 'UTF-8' );
        $this->xml->formatOutput       = true;
        $this->xml->preserveWhiteSpace = false;
        parent::__construct( $this->root( $name, $uri ) );
    }

    /**
     * @param string      $name
     * @param string|null $uri
     *
     * @return DOMElement
     */
    protected function root( string $name = 'xml', string $uri = null ): DOMElement
    {
        $node = $uri !== null
            ? $this->xml->createElementNS( $uri, $name )
            : $this->xml->createElement( $name );

        $elem = $this->xml->appendChild( $node );

        if ( $elem instanceof DOMElement ) {
            return $elem;
        }

        throw new XmlException( 'Xml intialization failed' );
    }

    /**
     * @param string $file
     * @param bool   $html
     *
     * @return Xml
     * @throws XmlException
     */
    public static function loadFromFile( string $file, bool $html = false ): Xml
    {
        try {
            $xml = new Xml();
            $html === false
                ? $xml->doc()
                      ->load( $file )
                : $xml->doc()
                      ->loadHTMLFile( $file );

            if ( $xml->doc()->documentElement instanceof DOMElement ) {
                $xml->setElement( $xml->doc()->documentElement );
            }

            return $xml;
        }
        catch ( Throwable $e ) {
            throw new XmlException( $e->getMessage(), $e->getCode(), $e );
        }
    }

    /**
     * @return DOMDocument
     */
    public function doc(): DOMDocument
    {
        return $this->xml;
    }

    /**
     * @param string $name
     * @param string $value
     * @param string $uri
     *
     * @return Node
     * @throws NodeException
     */
    public function wrap( string $name, string $value = '', string $uri = 'urn:void' ): Node
    {
        $childs = $this->childs();
        $node   = $this->set( $name, $value, $uri );
        foreach ( $childs as $child ) {
            $node->appendNode( $child->element() );
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->doc()
                    ->saveXML()
            ?: '';
    }

    /**
     * @param string $file
     * @param bool   $html
     *
     * @return bool
     */
    public function saveToFile( string $file, bool $html = false ): bool
    {
        $dir = dirname( $file );
        if ( !mkdir( $dir, 0777, true ) && !is_dir( $dir ) ) {
            return false;
        }

        return $html
            ? (bool) $this->doc()
                          ->saveHTMLFile( $file )
            : (bool) $this->doc()
                          ->save( $file );
    }

    /**
     * @return array
     */
    public function __sleep(): array
    {
        $this->buffer = $this->saveToString();

        return [ 'buffer' ];
    }

    /**
     * @param bool $html
     *
     * @return string|null
     */
    public function saveToString( bool $html = false ): ?string
    {
        if ( $html ) {
            return $this->doc()
                        ->saveHTML()
                ?: null;
        }

        return $this->doc()
                    ->saveXML()
            ?: null;
    }

    /**
     * @throws NodeException
     * @throws XmlException
     */
    public function __wakeup(): void
    {
        $xml        = self::loadFromString( (string) $this->buffer );
        $this->xml  = $xml->doc();
        $this->node = $xml->element();
    }

    /**
     * @param string $string
     * @param bool   $html
     *
     * @return Xml
     * @throws XmlException
     */
    public static function loadFromString( string $string, bool $html = false ): Xml
    {
        try {
            $xml = new Xml();
            $html
                ? $xml->doc()
                      ->loadHTML( '<?xml encoding="UTF-8">' . $string, LIBXML_COMPACT )
                : $xml->doc()
                      ->loadXML( $string );

            if ( $xml->doc()->documentElement instanceof DOMElement ) {
                $xml->setElement( $xml->doc()->documentElement );
            }

            return $xml;
        }
        catch ( Throwable $e ) {
            throw new XmlException( $e->getMessage(), $e->getCode(), $e );
        }
    }
}
