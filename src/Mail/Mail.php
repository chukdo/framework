<?php

namespace Chukdo\Mail;

/**
 * Mail.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Mail
{
    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $files = [];

    /**
     * @var array
     */
    protected $inlineImages = [];

    /**
     * @var string
     */
    protected $from;

    /**
     * @var string
     */
    protected $replyto;

    /**
     * @var array
     */
    protected $to = [];

    /**
     * @var string
     */
    protected $charset = 'UTF-8';

    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $textEnc = '8bit';

    /**
     * @var string
     */
    protected $html;

    /**
     * @var string
     */
    protected $htmlEnc = 'base64';

    /**
     * @var string
     */
    protected $mboundary;

    /**
     * @var string
     */
    protected $aboundary;

    /**
     * @var string
     */
    protected $rboundary;

    /**
     * @param object
     */
    protected $transport;

    /**
     * @var array
     */
    protected $log = [];

    /**
     * @param string
     */
    protected $pixel;

    /**
     * Constructeur
     * Defini les boundary
     *
     * @param object $transport trasnport mail ex. smtp
     *
     * @return void
     */
    public function __construct( $transport )
    {
        $this->transport = $transport;
        $this->mboundary = uniqid( '', true );
        $this->aboundary = uniqid( '', true );
        $this->rboundary = uniqid( '', true );
    }

    /**
     * Retourne le transport de message
     *
     * @return object $transport trasnport mail ex. smtp
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Ajoute un mail en copie
     *
     * @param string $mail mail
     * @param string $name nom associé au mail
     *
     * @return object $this
     */
    public function addCc( $mail, $name = false )
    {
        $this->to[] = $mail;
        $mail       = $this->qMail( $mail, $name );
        $header     = isset( $this->headers[ 'cc' ] )
            ? $this->headers[ 'cc' ] . ", $mail"
            : $mail;

        $this->addHeader( 'Cc', $header );

        return $this;
    }

    /**
     * Qualifie un entete mail
     *
     * @param string $mail mail
     * @param string $name nom associé
     *
     * @return string message
     */
    public function qMail( $mail, $name = false )
    {
        return $name
            ? "\"$name\" <$mail>"
            : $mail;
    }

    /**
     * Ajoute un entete personalisé
     *
     * @param string $name  nom de l'entete
     * @param string $value valeur associée
     *
     * @return object $this
     */
    public function addHeader( $name, $value )
    {
        $this->headers[ strtolower( $name ) ] = $value;

        return $this;
    }

    /**
     * Ajoute un mail en copie caché
     *
     * @param string $mail mail
     * @param string $name nom associé au mail
     *
     * @return object $this
     */
    public function addBcc( $mail, $name = false )
    {
        if ( in_array( $mail, $this->to ) ) {
            return $this;
        }

        $this->to[] = $mail;
        $mail       = $this->qMail( $mail, $name );
        $header     = isset( $this->headers[ 'bcc' ] )
            ? $this->headers[ 'bcc' ] . ", $mail"
            : $mail;

        $this->addHeader( 'Bcc', $header );

        return $this;
    }

    /**
     * Ajoute un fichier
     *
     * @param string $file       chemin du fichier
     * @param bool   $attachment attaché ou inline
     * @param string $name       nom du fichier si $attachment = false ou non du CID si $attachment = true
     * @param string $encoding   encodage du fichier 7bit, 8bit, base64, quoted-printable, binary
     *
     * @return object $this
     */
    public function addFile( $file, $name = false, $attachment = true, $encoding = 'base64' )
    {
        if ( file_exists( $file ) ) {
            if ( !$name ) {
                $name = basename( $file );
            }

            $content     = $this->encode( file_get_contents( $file ), $encoding );
            $disposition = $attachment
                ? "attachment; filename=\"$name\""
                : "inline;";
            $type        = 'application/octet-stream';

            $fi = finfo_open( FILEINFO_MIME_TYPE );

            if ( $ff = finfo_file( $fi, $file ) ) {
                $type = $ff;
            }

            finfo_close( $fi );


            $this->files[] = [ 'content'     => $content,
                               'type'        => $type,
                               'name'        => $name,
                               'disposition' => $disposition,
                               'encoding'    => $encoding ];
        }

        return $this;
    }

    /**
     * Encode une donnée
     *
     * @param string $data     donnée à encoder
     * @param string $encoding encodage du fichier 7bit, 8bit, base64, quoted-printable, binary
     *
     * @return string
     */
    protected function encode( $data, $encoding = '8bit' )
    {
        $encoding = str_replace( [ ' ',
                                   '_' ], '-', strtolower( $encoding ) );

        switch ( $encoding ) {
            case 'base64' :
                return trim( chunk_split( base64_encode( $data ) ) );
                break;
            case 'quoted-printable' :
                return trim( chunk_split( quoted_printable_encode( $data ) ) );
                break;
            case 'binary' :
            case '8bit' :
            case '7bit' :
            default:
                return $data;
                break;
        }
    }

    /**
     * Ajoute un mail en destinataire
     *
     * @param string $mail mail
     * @param string $name nom associé au mail
     *
     * @return object $this
     */
    public function addTo( $mail, $name = false )
    {
        $this->to[] = $mail;
        $mail       = $this->qMail( $mail, $name );
        $header     = isset( $this->headers[ 'to' ] )
            ? $this->headers[ 'to' ] . ", $mail"
            : $mail;

        $this->addHeader( 'to', $header );

        return $this;
    }

    /**
     * Defini le mail de l'envoyeur
     *
     * @param string $mail mail
     * @param string $name nom associé au mail
     *
     * @return object $this
     */
    public function setFrom( $mail, $name = false )
    {
        $this->from = $mail;
        $this->addHeader( 'from', $this->qMail( $mail, $name ) );

        return $this;
    }

    /**
     * Defini le mail de reponse
     *
     * @param string $mail mail
     * @param string $name nom associé au mail
     *
     * @return object $this
     */
    public function setReplyTo( $mail, $name = false )
    {
        $this->replyto = $mail;
        $this->addHeader( 'reply_to', $this->qMail( $mail, $name ) );

        return $this;
    }

    /**
     * Defini le sujet du mail
     *
     * @param string $subject
     *
     * @return object $this
     */
    public function setSubject( $subject )
    {
        $this->subject = $subject;
        $this->addHeader( 'subject', '=?utf-8?B?' . base64_encode( $subject ) . '?=' );

        return $this;
    }

    /**
     * Défini le charset pour les contenu HTML et Text
     *
     * @param string $charset
     *
     * @return object $this
     */
    public function setCharset( $charset )
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Envoi le mail
     *
     * @return bool true si l'operation reussi false sinon
     */
    public function sendMail()
    {
        if ( $this->log ) {
            $data = array_merge( $this->log[ 'data' ], [ 'subject'  => $this->subject,
                                                         'text'     => $this->text,
                                                         'mailfrom' => $this->from,
                                                         'mailto'   => $this->to,
                                                         'replyto'  => $this->replyto ] );

            $this->addHeader( 'x-modelo-client', app::gc( 'db/mongo/database' ) );
            $this->addHeader( 'x-modelo-message-id', (string)$this->log[ 'storage' ]->saveLog( $data ) );
        }

        return $this->transport->sendMail( $this->from, $this->to, $this->message(), $this->headers() );
    }

    /**
     * Genere le corps du mail à envoyer
     *
     * @return string message
     */
    public function message()
    {
        if ( count( $this->files ) ) {
            $message = '';

            if ( $this->text && $this->html ) {
                $message .= $this->openMixedBoundary() . $this->messageAlternative();

            } else {
                if ( $this->html ) {
                    $message .= $this->openMixedBoundary() . $this->messageHtml();

                } else {
                    if ( $this->text ) {
                        $message .= $this->openMixedBoundary() . $this->messageText();
                    }
                }
            }

            foreach ( $this->files as $file ) {
                $message .= $this->openMixedBoundary() . $this->messageFile( $file );
            }

            $message .= $this->closeMixedBoundary();

            return $message;

        } else {
            if ( $this->text && $this->html ) {
                return $this->messageAlternative( false );

            } else {
                if ( $this->html ) {
                    return $this->messageText( false );

                } else {
                    if ( $this->text ) {
                        return $this->messageText( false );
                    }
                }
            }
        }
    }

    public function openMixedBoundary()
    {
        return "\r\n--$this->mboundary\r\n";
    }

    public function messageAlternative( $mime = true )
    {
        $message = '';

        if ( $mime ) {
            $message .= $this->mime( 'alternative' );
        }

        $message .= $this->openAlternativeBoundary() . $this->messageText() . $this->openAlternativeBoundary() . $this->messageHtml() . $this->closeAlternativeBoundary();

        return $message;
    }

    /**
     * Retourne un mime-type
     *
     * @param string $type
     * @param string $encoding encodage du fichier 7bit, 8bit, base64, quoted-printable, binary
     *
     * @return string
     */
    protected function mime( $type, $encoding = false )
    {
        $encoding = str_replace( [ ' ',
                                   '_' ], '-', strtolower( $encoding ) );
        $mime     = [ 'mixed'       => "Content-Type: multipart/mixed; boundary=$this->mboundary" . $this->newLine(),
                      'alternative' => "Content-Type: multipart/alternative; boundary=$this->aboundary" . $this->newLine(),
                      'related'     => "Content-Type: multipart/related; boundary=$this->rboundary" . $this->newLine(),
                      'text'        => "Content-Type: text/plain; charset=$this->charset" . $this->newLine() . "Content-Transfer-Encoding: $encoding" . $this->newLine(),
                      'html'        => "Content-Type: text/html; charset=$this->charset" . $this->newLine() . "Content-Transfer-Encoding: $encoding" . $this->newLine() ];

        return $mime[ $type ];
    }

    public function newLine()
    {
        return "\r\n";
    }

    public function openAlternativeBoundary()
    {
        return "\r\n--$this->aboundary\r\n";
    }

    public function messageText( $mime = true )
    {
        $message = '';

        if ( $mime ) {
            $message .= $this->mime( 'text', $this->textEnc );
        }

        $message .= $this->newLine() . $this->encode( $this->getText(), $this->textEnc ) . $this->newLine();

        return $message;
    }

    public function getText()
    {
        return $this->text;
    }

    /**
     * Ajoute du texte au corps du message (concatenation)
     *
     * @param string $text
     * @param string $encoding encodage du fichier 7bit, 8bit, base64, quoted-printable, binary
     *
     * @return object $this
     */
    public function setText( $text, $encoding = '8bit' )
    {
        $this->text    = $text;
        $this->textEnc = $encoding;

        return $this;
    }

    public function messageHtml( $mime = true )
    {
        $html    = '';
        $message = '';

        if ( $mime ) {
            $html .= $this->mime( 'html', $this->htmlEnc );
        }

        $html .= $this->newLine() . $this->encode( $this->getHtml(), $this->htmlEnc ) . $this->newLine();

        if ( count( $this->inlineImages ) ) {
            $message .= $this->mime( 'related', $this->htmlEnc ) . $this->openRelatedBoundary() . $html;

            foreach ( $this->inlineImages as $inlineImage ) {
                $message .= $this->openRelatedBoundary() . $this->messageFile( $inlineImage );
            }

            $message .= $this->closeRelatedBoundary();

        } else {
            $message .= $html;
        }

        return $message;
    }

    public function getHtml()
    {
        $html = $this->html;

        if ( $pixel = $this->pixel ) {
            $html = str_replace( '</body>', '<img src="' . $pixel . '" height="1" width="1" /></body>', $html );
        }

        return $html;
    }

    /**
     * Defini le code Html au corps du message
     *
     * @param string $text
     * @param string $encoding encodage du fichier 7bit, 8bit, base64, quoted-printable, binary
     *
     * @return object $this
     */
    public function setHtml( $html, $encoding = '8bit' )
    {
        /** Extraction des images en base64 */
        $images = helper_data::match( '/(data:image\/[^\'")]+)/i', $html, true );

        foreach ( $images as $image ) {
            list( $mime, $content ) = helper_data::match( '/data:([^;]+);base64,(.*)/', $image );
            $name = md5( $content );

            $html = str_replace( "data:$mime;base64,$content", 'cid:' . $name, $html );
            $this->inlineImage( $content, $name, $mime );
        }

        $this->html    = $html;
        $this->htmlEnc = $encoding;

        return $this;
    }

    /**
     * Ajoute un fichier en base64
     *
     * @param string $content contenu en base64
     * @param string $name    nom du fichier si $attachment = true ou nom du CID si $attachment = false
     * @param bool   $type    attaché ou inline
     *
     * @return object $this
     */
    public function inlineImage( $content, $name, $type )
    {
        $this->inlineImages[ $name ] = [ 'content'     => trim( chunk_split( $content ) ),
                                         'name'        => $name,
                                         'type'        => $type,
                                         'disposition' => 'inline;',
                                         'encoding'    => 'base64' ];

        return $this;
    }

    public function openRelatedBoundary()
    {
        return "\r\n--$this->rboundary\r\n";
    }

    public function messageFile( $file )
    {
        $content     = $file[ 'content' ];
        $name        = $file[ 'name' ];
        $encoding    = $file[ 'encoding' ];
        $disposition = $file[ 'disposition' ];
        $type        = $file[ 'type' ];

        return "Content-Type: $type; name=\"$name\"" . $this->newLine() . "Content-Transfer-Encoding: $encoding" . $this->newLine() . "Content-ID: <$name>" . $this->newLine() . "Content-Description: $name" . $this->newLine() . "Content-Disposition: $disposition" . $this->newLine() . $this->newLine() . $content . $this->newLine();
    }

    public function closeRelatedBoundary()
    {
        return "\r\n--$this->rboundary--\r\n";
    }

    public function closeAlternativeBoundary()
    {
        return "\r\n--$this->aboundary--\r\n";
    }

    public function closeMixedBoundary()
    {
        return "\r\n--$this->mboundary--\r\n";
    }

    /**
     * Genere les entetes du mail à envoyer
     *
     * @return string headers
     */
    public function headers()
    {
        $headers = "MIME-Version: 1.0" . $this->newLine();

        foreach ( $this->headers as $key => $value ) {
            $headers .= $this->qHeader( $key ) . ': ' . $value . $this->newLine();
        }

        if ( count( $this->files ) ) {
            $headers .= $this->mime( 'mixed' );

        } else {
            if ( $this->text && $this->html ) {
                $headers .= $this->mime( 'alternative' );

            } else {
                if ( $this->html ) {
                    $headers .= $this->mime( 'html', $this->htmlEnc );

                } else {
                    if ( $this->text ) {
                        $headers .= $this->mime( 'text', $this->textEnc );
                    }
                }
            }
        }

        return $headers . $this->newLine();
    }

    /**
     * Qualifie le nom d'un entete
     *
     * @param string $name
     *
     * @return string
     */
    public function qHeader( $name )
    {
        return str_replace( ' ', '-', ucwords( str_replace( [ '-',
                                                              '_' ], ' ', strtolower( $name ) ) ) );
    }

    /**
     * Informations à logger
     *
     * @param object $storage Object ayant une fonction saveLog
     * @param array  $data    Données à logger
     *
     * @return $this
     */
    public function log( $storage, $data )
    {
        $this->log = [ 'storage' => $storage,
                       'data'    => $data ];

        return $this;
    }

    /**
     * @param $url
     */
    public function pixel( $url )
    {
        $this->pixel = $url;
    }

    /**
     * Renvoi le mail à envoyer sous la forme d'une chaine de caractere
     *
     * @return string
     */
    public function __toString()
    {
        return $this->headers() . $this->message();
    }
}

?>