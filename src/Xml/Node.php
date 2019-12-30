<?php

namespace Chukdo\Xml;

use Chukdo\Helper\Is;
use Chukdo\Helper\Arr;
use Chukdo\Json\Json;
use DOMCDATASection;
use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;
use Exception;
use IteratorAggregate;
use SimpleXMLElement;
use Throwable;
use Traversable;

/**
 * Classe XML NODE, etend les fonctionnalites XML de PHP7
 * pour la creation rapide de document XML compatible DOM.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Node implements IteratorAggregate
{
    /**
     * @var DOMElement
     */
    protected DOMElement $node;

    /**
     * @var DOMXPath|null
     */
    protected ?DOMXPath $xpath = null;

    /**
     * Node constructor.
     *
     * @param DOMElement $node
     */
    public function __construct( DOMElement $node )
    {
        $this->setElement( $node );
    }

    /**
     * @param DOMElement $node
     */
    protected function setElement( DOMElement $node ): void
    {
        $this->node = $node;
    }

    /**
     * @return Nodes|Traversable
     * @throws NodeException
     */
    public function getIterator()
    {
        return $this->childs();
    }

    /**
     * Retourne la liste des noeuds enfants.
     *
     * @param string $name
     *
     * @return Nodes
     */
    public function childs( string $name = '' ): Nodes
    {
        $nodes = new Nodes();
        foreach ( $this->elements( XML_ELEMENT_NODE ) as $child ) {
            if ( $name === '' || $name === $child->prefix || $name === $child->localName || $name === $child->nodeName ) {
                $nodes->append( new Node( $child ) );
            }
        }

        return $nodes;
    }

    /**
     * @return Nodes
     */
    protected function elements(): Nodes
    {
        $args  = func_get_args();
        $nodes = new Nodes();
        $count = count( $args );
        foreach ( $this->element()->childNodes as $child ) {
            if ( $count === 0 || Arr::in( $child->nodeType, $args ) ) {
                $nodes->append( $child );
            }
        }

        return $nodes;
    }

    /**
     * @return DOMElement
     */
    protected function element(): DOMElement
    {
        if ( $this->node instanceof DOMElement ) {
            return $this->node;
        }
        throw new NodeException( 'Xml node not defined' );
    }

    /**
     * @return String
     */
    public function name(): String
    {
        return $this->element()->localName;
    }

    /**
     * @param string $name
     *
     * @return Nodes
     */
    public function getNodesByTagName( string $name ): Nodes
    {
        $nodes = new Nodes();
        foreach ( $this->element()
                       ->getElementsByTagName( $name ) as $child ) {
            if ( $child->localName === $name || $name === '*' ) {
                $nodes->append( new Node( $child ) );
            }
        }

        return $nodes;
    }

    /**
     * @param string $name
     * @param int    $indice
     * @param bool   $create
     *
     * @return Node|null
     */
    public function get( string $name, int $indice = 0, bool $create = true ): ?Node
    {
        $index = 0;
        $node  = null;
        /** Recherche du noeud */
        foreach ( $this->elements( XML_ELEMENT_NODE ) as $child ) {
            if ( $child->localName === $name ) {
                if ( $index === $indice ) {
                    return new Node( $child );
                }
                ++$index;
            }
        }
        /** Creation d'un nouveau noeud en tenant compte de l'indice */
        if ( $create ) {
            $indice -= $index;
            if ( $indice > 0 ) {
                for ( $i = 0; $i <= $indice; ++$i ) {
                    $node = $this->set( $name );
                }
            }
            else {
                $node = $this->set( $name );
            }
        }

        return $node;
    }

    /**
     * @param string $name
     * @param string $value
     * @param string $uri
     *
     * @return Node
     */
    public function set( string $name, string $value = '', string $uri = 'urn:void' ): Node
    {
        $uri  = preg_match( '/:/', $name )
            ? $uri
            : '';
        $node = new Node( $this->appendNode( new DOMElement( trim( $name ), null, $uri ) ) );
        $node->setValue( $value, false );

        return $node;
    }

    /**
     * @param DOMNode $node
     *
     * @return DOMNode
     */
    protected function appendNode( DOMNode $node ): DOMNode
    {
        return $this->element()
                    ->appendChild( $node );
    }

    /**
     * @param string $value
     * @param bool   $append
     * @param bool   $raw
     *
     * @return $this
     */
    public function setValue( string $value, bool $append = true, bool $raw = false ): self
    {
        /** Suppression de contenu du noeud */
        if ( $append === false ) {
            $this->unsetValue();
        }
        /** Autodetection des CDATA */
        if ( $value !== '' ) {
            if ( $raw === true ) {
                $this->appendNode( new DOMText( $value ) );
            }
            else {
                $len1 = strlen( $value );
                $len2 = strlen( str_replace( [
                                                 '"',
                                                 '[',
                                                 ']',
                                                 '&',
                                                 '<',
                                                 '>',
                                             ], '', $value ) );
                $this->appendNode( $len1 !== $len2
                                       ? new DOMCDATASection( $value )
                                       : new DOMText( $value ) );
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function unsetValue(): string
    {
        $str = '';
        foreach ( $this->elements( XML_TEXT_NODE, XML_CDATA_SECTION_NODE ) as $child ) {
            $this->element()
                 ->removeChild( $child );
            $str .= $child->nodeValue;
        }

        return trim( $str );
    }

    /**
     * @param string $name
     * @param string $value
     * @param string $uri
     *
     * @return Node
     */
    public function after( string $name, string $value = '', string $uri = 'urn:void' ): Node
    {
        if ( ( $ref = $this->element()->nextSibling ) !== null ) {
            $uri    = preg_match( '/:/', $name )
                ? $uri
                : '';
            $new    = new DOMElement( $name, false, $uri );
            $parent = $this->parent()
                           ->element();
            $node   = new Node( $parent->insertBefore( $new, $ref ) );
            $node->setValue( $value );
        }
        else {
            $node = $this->set( $name, $value, $uri );
        }

        return $node;
    }

    /**
     * @return Node
     */
    public function parent(): Node
    {
        if ( ( $node = $this->element()->parentNode ) instanceof DOMElement ) {
            return new Node( $node );
        }
        throw new XmlException( 'Xml node has no parent' );
    }

    /**
     * @return string
     */
    public function values(): string
    {
        $str = '';
        foreach ( $this->element()->childNodes as $child ) {
            if ( $child->nodeType === XML_TEXT_NODE || $child->nodeType === XML_CDATA_SECTION_NODE ) {
                $str .= $child->nodeValue;
            }
            else {
                if ( $child->nodeType === XML_ELEMENT_NODE ) {
                    $node = new Node( $child );
                    $str  .= ' ' . $node->values();
                }
            }
        }
        $str = trim( $str );

        return $str;
    }

    /**
     * @return $this
     */
    public function unsetAttrs(): self
    {
        $attributes = $this->attrs();
        foreach ( $attributes as $name => $value ) {
            $this->unsetAttr( $name );
        }

        return $this;
    }

    /**
     * @return array
     */
    public function attrs(): array
    {
        $attrs = [];
        foreach ( $this->element()->attributes as $child ) {
            $attrs[ $child->nodeName ] = $child->nodeValue;
        }

        return $attrs;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function unsetAttr( string $name ): string
    {
        $attr = $this->attr( $name );
        $this->element()
             ->removeAttribute( $name );

        return $attr;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function attr( string $name ): string
    {
        return (string) $this->element()
                             ->getAttribute( $name );
    }

    /**
     * @return $this
     */
    public function unsetDataAttrs(): self
    {
        $attributes = $this->dataAttrs();
        foreach ( $attributes as $name => $value ) {
            $this->unsetAttr( $name );
        }

        return $this;
    }

    /**
     * @return Nodes
     */
    public function dataAttrs(): Nodes
    {
        $nodes = new Nodes();
        foreach ( $this->element()->attributes as $child ) {
            if ( strpos( $child->nodeName, 'data-' ) === 0 ) {
                $nodes->offsetSet( $child->nodeName, $child->nodeValue );
            }
        }

        return $nodes;
    }

    /**
     * @return String
     */
    public function comment(): String
    {
        $str = '';
        foreach ( $this->elements( XML_COMMENT_NODE ) as $child ) {
            $str .= $child->nodeValue;
        }

        return $str;
    }

    /**
     * @param string $comment
     *
     * @return $this
     */
    public function setComment( string $comment ): self
    {
        $this->unsetComment();
        $this->element()
             ->insertBefore( new DOMComment( $comment ), $this->element()->firstChild );

        return $this;
    }

    /**
     * @return string
     */
    public function unsetComment(): string
    {
        $str = '';
        foreach ( $this->elements( XML_COMMENT_NODE ) as $child ) {
            $this->element()
                 ->removeChild( $child );
            $str .= $child->nodeValue;
        }

        return $str;
    }

    /**
     * @param string $name
     * @param bool   $attr
     *
     * @return $this
     */
    public function rename( string $name, bool $attr = true ): self
    {
        $node   = $this->parent()
                       ->set( $name );
        $append = [];
        foreach ( $this->element()->childNodes as $child ) {
            $append[] = $child;
        }
        foreach ( $append as $child ) {
            $node->appendNode( $child );
        }
        if ( $attr ) {
            $node->setAttrs( $this->attrs() );
        }

        return $this->replace( $node );
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function setAttrs( array $attributes ): self
    {
        foreach ( $attributes as $name => $value ) {
            $this->setAttr( $name, $value );
        }

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function setAttr( string $name, string $value ): self
    {
        $this->element()
             ->setAttribute( $name, $value );

        return $this;
    }

    /**
     * @param Node $node
     *
     * @return $this
     */
    public function replace( Node $node ): self
    {
        $this->parent()
             ->element()
             ->replaceChild( $node->element(), $this->element() );
        $this->setElement( $node->element() );

        return $this;
    }

    /**
     * @return Node
     */
    public function clone(): Node
    {
        $clone  = $this->element()
                       ->cloneNode( true );
        $parent = $this->parent()
                       ->element();
        if ( $next = $this->next() ) {
            $node = $parent->insertBefore( $clone, $next->element() );
        }
        else {
            $node = $parent->appendChild( $clone );
        }

        return new Node( $node );
    }

    /**
     * @param string $name
     *
     * @return Node|null
     */
    public function next( $name = '' ): ?Node
    {
        $node  = $this->element();
        $names = explode( ' ', $name );
        while ( $node = $node->nextSibling ) {
            if ( $node->nodeType === XML_ELEMENT_NODE ) {
                if ( $name === '' || Arr::in( $node->nodeName, $names ) ) {
                    return new Node( $node );
                }
            }
        }

        return null;
    }

    /**
     * @return Node
     */
    public function unwrap(): Node
    {
        $ref    = $this->element();
        $parent = $this->parent()
                       ->element();
        $append = [];
        foreach ( $this->element()->childNodes as $child ) {
            $append[] = $child;
        }
        foreach ( $append as $child ) {
            $parent->insertBefore( $child, $ref );
        }
        $this->unset();

        return new Node( $parent );
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function unset( string $name = '' ): self
    {
        if ( $name === '' ) {
            $this->element()->parentNode->removeChild( $this->element() );
        }
        else {
            foreach ( $this->elements( XML_ELEMENT_NODE ) as $child ) {
                if ( $child->localName === $name ) {
                    $this->element()
                         ->removeChild( $child );
                }
            }
        }

        return $this;
    }

    /**
     * Wrap le noeud courant
     * (remonte les enfants au noeud parent et supprime le noeud courant).
     *
     * @param string $name
     * @param string $value
     * @param string $uri
     *
     * @return Node
     */
    public function wrap( string $name, string $value = '', string $uri = 'urn:void' ): Node
    {
        $node = $this->before( $name, $value, $uri );
        $node->import( $this, true );

        return $node;
    }

    /**
     * @param string $name
     * @param string $value
     * @param string $uri
     *
     * @return Node
     */
    public function before( string $name, string $value = '', string $uri = 'urn:void' ): Node
    {
        $uri    = preg_match( '/:/', $name )
            ? $uri
            : '';
        $new    = new DOMElement( $name, false, $uri );
        $ref    = $this->element();
        $parent = $this->parent()
                       ->element();
        $node   = new Node( $parent->insertBefore( $new, $ref ) );
        $node->setValue( $value );

        return $node;
    }

    /**
     * @param      $import
     * @param bool $parent importe le noeud lui même et pas seulement les enfants
     *
     * @param      $import
     * @param bool $parent
     *
     * @return $this
     */
    public function import( $import, bool $parent = false ): self
    {
        $node = false;
        /** import depuis un objet xml */
        if ( $import instanceof Node ) {
            $node = $import->element();
            /** import depuis un objet simplexml */
        }
        else {
            if ( $import instanceof SimpleXMLElement ) {
                $node = dom_import_simplexml( $import );
                /** import depuis un objet domxml */
            }
            else {
                if ( $import instanceof DOMNode ) {
                    $node = $import;
                    /** import depuis une chaine de caracteres */
                }
                else {
                    if ( is_string( $import ) ) {
                        if ( $import[ 0 ] !== '<' ) {
                            $import = '<xml>' . $import . '</xml>';
                        }
                        $xml  = Xml::loadFromString( $import );
                        $node = $xml->element();
                    }
                }
            }
        }
        /** importation d'un DOMElement */
        if ( $node instanceof DOMElement ) {
            if ( $parent === true ) {
                $this->appendNode( $this->doc()
                                        ->importNode( $node, true ) );
            }
            else {
                foreach ( $node->childNodes as $child ) {
                    $this->appendNode( $this->doc()
                                            ->importNode( $child, true ) );
                }
            }
            /** importation d'un DOMNode */
        }
        else {
            if ( $node instanceof DOMNode ) {
                $this->appendNode( $this->doc()
                                        ->importNode( $node, true ) );
                /** importation d'un tableau */
            }
            else {
                if ( Is::arr( $import ) ) {
                    foreach ( $import as $k => $v ) {
                        /* Index */
                        if ( is_int( $k ) ) {
                            $node = $this->set( 'item' );
                            $node->setAttr( 'oname', $k );
                            /** Noeud invalide */
                        }
                        else {
                            if ( !preg_match( '/^[a-z_](?:[a-z0-9_-]+)?$/iu', $k ) ) {
                                $node = $this->set( 'item' );
                                $node->setAttr( 'oname', $k );
                                /** Noeud valide */
                            }
                            else {
                                $node = $this->set( $k );
                            }
                        }
                        /** gestion de la recursivité */
                        if ( Is::traversable( $v ) ) {
                            $node->import( $v );
                        }
                        else {
                            $node->setValue( $v );
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @return DOMDocument
     */
    protected function doc(): DOMDocument
    {
        if ( $doc = $this->element()->ownerDocument ) {
            return $doc;
        }
        throw new NodeException( 'Xml document not defined' );
    }

    /**
     * Wrap à l'interieur du noeud courant
     * (remonte les enfants au noeud parent et supprime le noeud courant).
     *
     * @param string $name
     * @param string $value
     * @param string $uri
     *
     * @return Node
     */
    public function wrapIn( string $name, string $value = '', string $uri = 'urn:void' ): Node
    {
        $node  = $this->set( $name, $value, $uri );
        $nodes = $this->elements();
        $count = $nodes->count() - 1;
        foreach ( $nodes as $k => $child ) {
            if ( $k < $count ) {
                $node->appendNode( $child );
            }
        }

        return $node;
    }

    /**
     * @return Node|null
     */
    public function first(): ?Node
    {
        return $this->childs()
                    ->get( 0 );
    }

    /**
     * @return Node|null
     */
    public function last(): ?Node
    {
        $childs = (array) $this->childs();
        if ( count( $childs ) > 0 ) {
            return end( $childs );
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return Node|null
     */
    public function prev( string $name = '' ): ?Node
    {
        $node  = $this->element();
        $names = explode( ' ', $name );
        while ( $node = $node->previousSibling ) {
            if ( $node->nodeType === XML_ELEMENT_NODE ) {
                if ( $name === '' || Arr::in( $node->nodeName, $names ) ) {
                    return new Node( $node );
                }
            }
        }

        return null;
    }

    /**
     * @return bool
     */
    public function hasChild(): bool
    {
        try {
            return count( $this->elements( XML_ELEMENT_NODE ) ) > 0;
        }
        catch ( Exception $e ) {
            return false;
        }
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->elements( XML_ELEMENT_NODE )
                    ->count();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasAttr( string $name ): bool
    {
        return $this->element()
                    ->hasAttribute( $name );
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return bool
     */
    public function hasStyle( string $name, string $value = '' ): bool
    {
        if ( preg_match( '/' . $name . '\s?:\s?' . $value . '/i', $this->element()
                                                                       ->getAttribute( 'style' ) ) ) {
            return true;
        }

        return false;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return Node
     */
    public function setStyle( string $name, string $value ): Node
    {
        $this->unsetStyle( $name );
        $this->setAttr( 'style', trim( trim( $this->element()
                                                  ->getAttribute( 'style' ), ';' ) . ';' . $name . ':' . $value, ';' ) . ';' );

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function unsetStyle( string $name ): self
    {
        $this->setAttr( 'style', preg_replace( '/' . $name . ':.*?;/i', '', $this->element()
                                                                                 ->getAttribute( 'style' ) ) );

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasClass( string $name ): bool
    {
        $names = explode( ' ', $name );
        $class = explode( ' ', $this->element()
                                    ->getAttribute( 'class' ) );
        foreach ( $names as $nm ) {
            if ( Arr::in( $nm, $class ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setClass( string $name ): self
    {
        $this->unsetClass( $name );
        $this->setAttr( 'class', trim( $this->element()
                                            ->getAttribute( 'class' ) . ' ' . $name ) );

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function unsetClass( string $name ): self
    {
        $names = explode( ' ', $name );
        $class = explode( ' ', $this->element()
                                    ->getAttribute( 'class' ) );
        foreach ( $names as $nm ) {
            foreach ( $class as $k => $v ) {
                if ( $v === $nm ) {
                    unset( $class[ $k ] );
                }
            }
        }
        $this->setAttr( 'class', implode( ' ', $class ) );

        return $this;
    }

    /**
     * @param array  $classes
     * @param string $path
     *
     * @return $this
     */
    public function dropClass( array $classes, string $path = './*' ): self
    {
        $query = $path . "[contains(@class,'" . implode( "') or contains(@class,'", $classes ) . "')]";
        foreach ( $this->query( $query ) as $item ) {
            foreach ( $classes as $classe ) {
                if ( $item->hasClass( $classe ) ) {
                    $item->unsetClass( $classe );
                }
            }
        }

        return $this;
    }

    /**
     * @param string $query
     *
     * @return Nodes
     */
    public function query( string $query ): Nodes
    {
        if ( !( $this->xpath instanceof DOMXPath ) ) {
            $this->xpath = new DOMXPath( $this->doc() );
        }
        $nodes = new Nodes();
        /** Si le noeud n'est pas defini xPath ne fonctionne pas */
        if ( !is_object( $this->element() ) ) {
            return $nodes;
        }
        /** Enregistre automatiquement l'espace de nom par defaut s'il existe sous le NameSpace: dns */
        if ( ( $dns = $this->element()
                           ->lookupnamespaceURI( null ) ) !== null ) {
            $this->xpath->registerNamespace( 'dns', $dns );
        }
        /** Requete XPath */
        try {
            $nodesList = $this->xpath->query( $query, $this->element() );
            foreach ( $nodesList as $node ) {
                $nodes->append( new Node( $node->nodeType === XML_ELEMENT_NODE
                                              ? $node
                                              : $node->parentNode ) );
            }

            return $nodes;
        }
        catch ( Throwable $e ) {
            throw new NodeException( $e->getMessage(), $e->getCode(), $e );
        }
    }

    /**
     * @param bool $html
     * @param bool $core
     *
     * @return string
     */
    public function toXmlString( bool $html = false, bool $core = false ): string
    {
        if ( ( $doc = $this->doc() ) && !$core ) {
            return $html
                ? $doc->saveHTML( $this->element() )
                : $doc->saveXML( $this->element() );
        }
        $a = $c = '';
        $n = $this->element();
        foreach ( $n->attributes as $attribute ) {
            $a .= ' ' . $attribute->nodeName . '="' . $attribute->nodeValue . '"';
        }
        foreach ( $n->childNodes as $child ) {
            switch ( $child->nodeType ) {
                case XML_ELEMENT_NODE:
                    $node = new Node( $child );
                    $c    .= $node->toXmlString( $html, false );
                    break;
                case XML_TEXT_NODE:
                    $c .= $child->nodeValue;
                    break;
                case XML_CDATA_SECTION_NODE:
                    $c .= $html
                        ? $child->nodeValue
                        : '<![CDATA[' . $child->nodeValue . ']]>';
                    break;
                case XML_COMMENT_NODE:
                    $c .= '<!--' . $child->nodeValue . '-->';
                    break;
            }
        }
        if ( $core ) {
            return $c;
        }

        return $c === ''
            ? '<' . $n->nodeName . $a . '/>'
            : '<' . $n->nodeName . $a . '>' . $c . '</' . $n->nodeName . '>';
    }

    /**
     * @return string
     * @throws Exception
     */
    public function __toString(): string
    {
        return $this->value();
    }

    /**
     * @return string
     * @throws NodeException
     */
    public function value(): string
    {
        $str = '';
        foreach ( $this->element()->childNodes as $child ) {
            if ( $child->nodeType === XML_TEXT_NODE || $child->nodeType === XML_CDATA_SECTION_NODE ) {
                $str .= $child->nodeValue;
            }
        }
        $str = trim( $str );

        return $str;
    }

    /**
     * @return Node
     */
    public function toXml(): self
    {
        return $this;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function toJsonString(): string
    {
        return json_encode( $this->toArray(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT, 512 );
    }

    /**
     * @param string $default nom du noeud des iterations à remplacer
     * @param null   $value   objet xml_node de depart $this par défaut (parametre de recursivité)
     *
     * @return array
     * @throws NodeException
     */
    public function toArray( string $default = 'item', $value = null ): array
    {
        $value ??= $this;
        $array = [];
        foreach ( $value->childs() as $k => $child ) {
            $name  = $child->name() === $default
                ? $k
                : $child->name();
            $value = $child->childs()
                           ->count() === 0
                ? $child->value()
                : $this->toArray( $default, $child );
            if ( !Is::empty( $value ) ) {
                if ( isset( $array[ $name ] ) ) {
                    if ( is_array( $array[ $name ] ) ) {
                        if ( key( $array[ $name ] ) === 0 ) {
                            $array[ $name ][] = $value;
                        }
                        else {
                            $array[ $name ] = [
                                $array[ $name ],
                                $value,
                            ];
                        }
                    }
                    else {
                        $array[ $name ] = [
                            $array[ $name ],
                            $value,
                        ];
                    }
                }
                else {
                    $array[ $name ] = $value;
                }
            }
        }

        return $array;
    }

    /**
     * @param string $default
     *
     * @return Json
     */
    public function toJson( string $default = 'item' ): Json
    {
        return new Json( $this->toArray( $default ) );
    }

    /**
     * @param string $name
     *
     * @throws NodeException
     */
    public function __get( string $name )
    {
        throw new NodeException( sprintf( 'Method [%s] does not exist', $name ) );
    }

    /**
     * @param string $name
     * @param        $value
     *
     * @throws NodeException
     */
    public function __set( string $name, $value )
    {
        throw new NodeException( sprintf( 'Method [%s] does not exist', $name ) );
    }

    /**
     * @param string $name
     */
    public function __isset( string $name )
    {
        throw new NodeException( sprintf( 'Method [%s] does not exist', $name ) );
    }

    /**
     * @param string $name
     * @param array  $params
     *
     * @return mixed
     * @throws Exception
     */
    public function __call( string $name, $params = [] )
    {
        throw new NodeException( sprintf( 'Method [%s] does not exist', $name ) );
    }
}
