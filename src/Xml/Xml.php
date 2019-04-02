<?php

namespace Chukdo\Xml;

use DOMDocument;
use Throwable;

/**
 * Classe XML DOCUMENT, etend les fonctionnalites XML de PHP5
 * pour la creation rapide de document XML compatible DOM.
 *
 * @version   1.0.0
 *
 * @copyright licence GPL, Copyright (C) 2008 Domingo
 *
 * @since     15/01/2009
 *
 * @author    Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Xml extends Node
{
    /**
     * Document XML.
     *
     * @param object DOMDocument
     */
    protected $xml;

    /**
     * Buffer de linearisation.
     *
     * @param string
     */
    private $buffer;

    /**
     * Xml constructor.
     *
     * @param string $name
     * @param string $uri
     */
    public function __construct( string $name = 'xml', string $uri = '' ) {
        $this->xml                     = new DOMDocument('1.0', 'UTF-8');
        $this->xml->formatOutput       = false;
        $this->xml->preserveWhiteSpace = false;

        parent::__construct($this->xml->appendChild($uri !== ''
            ? $this->xml->createElementNS($uri,
                $name)
            : $this->xml->createElement($name)));
    }

    /**
     * @return DOMDocument
     */
    public function doc() {
        return $this->xml;
    }

    /**
     * @param string $name
     * @param string $value
     * @param string $uri
     *
     * @return Node
     *
     * @throws NodeException
     */
    public function wrap( string $name, string $value = '', string $uri = 'urn:void' ): Node {
        $childs = $this->childs();
        $node   = $this->set($name,
            $value,
            $uri);

        foreach( $childs as $child ) {
            $node->appendNode($child->element());
        }

        return $this;
    }

    /**
     * @param string $file
     * @param bool   $html
     *
     * @return Xml
     *
     * @throws XmlException
     */
    public static function loadFromFile( string $file, bool $html = false ): Xml {
        try {
            $xml = new Xml();
            $html === false
                ? $xml->doc()
                ->load($file)
                : $xml->doc()
                ->loadHTMLFile($file);
            $xml->setElement($xml->doc()->documentElement);

            return $xml;
        } catch( Throwable $e ) {
            throw new XmlException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $string
     * @param bool   $html
     *
     * @return Xml
     *
     * @throws XmlException
     */
    public static function loadFromString( string $string, bool $html = false ): Xml {
        try {
            $xml = new Xml();
            $html
                ? $xml->doc()
                ->loadHTML('<?xml encoding="UTF-8">' . $string,
                    LIBXML_COMPACT)
                : $xml->doc()
                ->loadXML($string);
            $xml->setElement($xml->doc()->documentElement);

            return $xml;
        } catch( Exception $e ) {
            throw new XmlException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param string $file
     * @param bool   $html
     *
     * @return bool
     */
    public function saveToFile( string $file, bool $html = false ): bool {
        $dir = dirname($file);

        if( !is_dir($dir) ) {
            if( !mkdir($dir,
                0777,
                true) ) {
                return false;
            }
        }

        return $html
            ? $this->doc()
                ->saveHTMLFile($file)
            : $this->doc()
                ->save($file);
    }

    /**
     * @param bool $html
     *
     * @return string
     */
    public function saveToString( bool $html = false ): string {
        return $html
            ? $this->doc()
                ->saveHTML()
            : $this->doc()
                ->saveXML();
    }

    /**
     * @return string
     */
    public function __toString(): string {
        return $this->doc()
            ->saveXML();
    }

    /**
     * @return array
     */
    public function __sleep(): array {
        $this->buffer = $this->saveToString();

        return [ 'buffer' ];
    }

    /**
     * @throws NodeException
     * @throws XmlException
     */
    public function __wakeup(): void {
        $xml          = xml::loadFromString($this->buffer);
        $this->xml    = $xml->doc();
        $this->__node = $xml->element();
    }
}
