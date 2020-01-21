<?php

namespace Chukdo\Mail;

use Chukdo\Contracts\Mail\Transport as TransportInterface;
use Chukdo\Helper\Str;

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
    protected array $headers = [];

    /**
     * @var array
     */
    protected array $files = [];

    /**
     * @var array
     */
    protected array $inlineImages = [];

    /**
     * @var string
     */
    protected string $from;

    /**
     * @var string
     */
    protected string $replyto;

    /**
     * @var array
     */
    protected array $to = [];

    /**
     * @var string
     */
    protected string $charset = 'UTF-8';

    /**
     * @var string
     */
    protected string $subject = '';

    /**
     * @var string
     */
    protected string $text;

    /**
     * @var string
     */
    protected string $textEnc = '8bit';

    /**
     * @var string
     */
    protected string $html;

    /**
     * @var string
     */
    protected string $htmlEnc = 'base64';

    /**
     * @var string
     */
    protected string $mboundary;

    /**
     * @var string
     */
    protected string $aboundary;

    /**
     * @var string
     */
    protected string $rboundary;

    /**
     * @param TransportInterface
     */
    protected TransportInterface $transport;

    /**
     * @param string
     */
    protected string $pixel;

    /**
     * Mail constructor.
     *
     * @param TransportInterface $transport
     */
    public function __construct( TransportInterface $transport )
    {
        $this->transport = $transport;
        $this->mboundary = uniqid( '', true );
        $this->aboundary = uniqid( '', true );
        $this->rboundary = uniqid( '', true );
    }

    /**
     * @return TransportInterface
     */
    public function transport(): TransportInterface
    {
        return $this->transport;
    }

    /**
     * @param string      $mail
     * @param string|null $name
     *
     * @return $this
     */
    public function addCc( string $mail, string $name = null ): self
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
     * @param string      $mail
     * @param string|null $name
     *
     * @return string
     */
    public function qMail( string $mail, string $name = null ): string
    {
        return $name
            ? "\"$name\" <$mail>"
            : $mail;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function addHeader( string $name, string $value ): self
    {
        $this->headers[ strtolower( $name ) ] = $value;

        return $this;
    }

    /**
     * @param string      $mail
     * @param string|null $name
     *
     * @return $this
     */
    public function addBcc( string $mail, string $name = null ): self
    {
        if ( in_array( $mail, $this->to, true ) ) {
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
     * @param string      $file
     * @param string|null $name
     * @param bool        $attachment
     * @param string      $encoding
     *
     * @return $this
     */
    public function addFile( string $file, string $name = null, bool $attachment = true, string $encoding = 'base64' ): self
    {
        if ( !file_exists( $file ) ) {
            throw new MailException( sprintf( 'File [%s] no exists', $file ) );
        }

        if ( !( $get = file_get_contents( $file ) ) ) {
            throw new MailException( sprintf( 'Can\'t read file [%s] no exists', $file ) );
        }

        if ( !$name ) {
            $name = basename( $file );
        }

        $content     = $this->encode( $get, $encoding );
        $disposition = $attachment
            ? "attachment; filename=\"$name\""
            : 'inline;';
        $type        = 'application/octet-stream';

        $fi = finfo_open( FILEINFO_MIME_TYPE );

        if ( $fi === false ) {
            throw new MailException( sprintf( 'Can\'t read file info [%s]', $file ) );
        }

        if ( $ff = finfo_file( $fi, $file ) ) {
            $type = $ff;
        }

        finfo_close( $fi );


        $this->files[] = [
            'content'     => $content,
            'type'        => $type,
            'name'        => $name,
            'disposition' => $disposition,
            'encoding'    => $encoding,
        ];

        return $this;
    }

    /**
     * @param string $data
     * @param string $encoding
     *
     * @return string
     */
    protected function encode( string $data, string $encoding = '8bit' ): string
    {
        $encoding = str_replace( [
                                     ' ',
                                     '_',
                                 ], '-', strtolower( $encoding ) );

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
     * @param string      $mail
     * @param string|null $name
     *
     * @return $this
     */
    public function addTo( string $mail, string $name = null ): self
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
     * @param string      $mail
     * @param string|null $name
     *
     * @return $this
     */
    public function setFrom( string $mail, string $name = null ): self
    {
        $this->from = $mail;
        $this->addHeader( 'from', $this->qMail( $mail, $name ) );

        return $this;
    }

    /**
     * @param string      $mail
     * @param string|null $name
     *
     * @return $this
     */
    public function setReplyTo( string $mail, string $name = null ): self
    {
        $this->replyto = $mail;
        $this->addHeader( 'reply_to', $this->qMail( $mail, $name ) );

        return $this;
    }

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject( string $subject ): self
    {
        $this->subject = $subject;
        $this->addHeader( 'subject', '=?utf-8?B?' . base64_encode( $subject ) . '?=' );

        return $this;
    }

    /**
     * @param string $charset
     *
     * @return $this
     */
    public function setCharset( string $charset ): self
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * @return bool
     */
    public function sendMail(): bool
    {
        $message = $this->message();

        if ( $message === null ) {
            throw new MailException( sprintf( 'No message to mail at [%s]', reset( $this->to ) ) );
        }

        return $this->transport->sendMail( $this->from, $this->to, $message, $this->headers() );
    }

    /**
     * @return string|null
     */
    public function message(): ?string
    {
        if ( count( $this->files ) ) {
            $message = '';

            if ( $this->text && $this->html ) {
                $message .= $this->openMixedBoundary() . $this->messageAlternative();

            }
            elseif ( $this->html ) {
                $message .= $this->openMixedBoundary() . $this->messageHtml();

            }
            elseif ( $this->text ) {
                $message .= $this->openMixedBoundary() . $this->messageText();
            }

            foreach ( $this->files as $file ) {
                $message .= $this->openMixedBoundary() . $this->messageFile( $file );
            }

            $message .= $this->closeMixedBoundary();

            return $message;

        }

        if ( $this->text && $this->html ) {
            return $this->messageAlternative( false );

        }

        if ( $this->html ) {
            return $this->messageText( false );

        }

        if ( $this->text ) {
            return $this->messageText( false );
        }

        return null;
    }

    /**
     * @return string
     */
    public function openMixedBoundary(): string
    {
        return "\r\n--$this->mboundary\r\n";
    }

    /**
     * @param bool $mime
     *
     * @return string
     */
    public function messageAlternative( bool $mime = true ): string
    {
        $message = '';

        if ( $mime ) {
            $message .= $this->mime( 'alternative' );
        }

        $message .= $this->openAlternativeBoundary() . $this->messageText() . $this->openAlternativeBoundary() . $this->messageHtml() . $this->closeAlternativeBoundary();

        return $message;
    }

    /**
     * @param string $type
     * @param string $encoding
     *
     * @return string|null
     */
    protected function mime( string $type, string $encoding = '8bit' ): ?string
    {
        $encoding = str_replace( [
                                     ' ',
                                     '_',
                                 ], '-', strtolower( $encoding ) );
        $mime     = [
            'mixed'       => "Content-Type: multipart/mixed; boundary=$this->mboundary" . $this->newLine(),
            'alternative' => "Content-Type: multipart/alternative; boundary=$this->aboundary" . $this->newLine(),
            'related'     => "Content-Type: multipart/related; boundary=$this->rboundary" . $this->newLine(),
            'text'        => "Content-Type: text/plain; charset=$this->charset" . $this->newLine() . "Content-Transfer-Encoding: $encoding" . $this->newLine(),
            'html'        => "Content-Type: text/html; charset=$this->charset" . $this->newLine() . "Content-Transfer-Encoding: $encoding" . $this->newLine(),
        ];

        return $mime[ $type ] ?? null;
    }

    /**
     * @return string
     */
    public function newLine(): string
    {
        return "\r\n";
    }

    /**
     * @return string
     */
    public function openAlternativeBoundary(): string
    {
        return "\r\n--$this->aboundary\r\n";
    }

    /**
     * @param bool $mime
     *
     * @return string
     */
    public function messageText( bool $mime = true ): string
    {
        $message = '';

        if ( $mime ) {
            $message .= $this->mime( 'text', $this->textEnc );
        }

        $message .= $this->newLine() . $this->encode( $this->getText(), $this->textEnc ) . $this->newLine();

        return $message;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @param string $encoding
     *
     * @return $this
     */
    public function setText( string $text, string $encoding = '8bit' ): self
    {
        $this->text    = $text;
        $this->textEnc = $encoding;

        return $this;
    }

    /**
     * @param bool $mime
     *
     * @return string
     */
    public function messageHtml( bool $mime = true ): string
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

        }
        else {
            $message .= $html;
        }

        return $message;
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        $html = $this->html;

        if ( $pixel = $this->pixel ) {
            $html = str_replace( '</body>', '<img alt="pix" src="' . $pixel . '" height="1" width="1" /></body>', $html );
        }

        return $html;
    }

    /**
     * @param string $html
     * @param string $encoding
     *
     * @return $this
     */
    public function setHtml( string $html, string $encoding = '8bit' ): self
    {
        /** Extraction des images en base64 */
        $images = Str::matchAll( '/(data:image\/[^\'")]+)/i', $html );

        foreach ( $images as $image ) {
            [
                $mime,
                $content,
            ] = Str::match( '/data:([^;]+);base64,(.*)/', $image );
            $name = md5( $content );

            $html = str_replace( "data:$mime;base64,$content", 'cid:' . $name, $html );
            $this->inlineImage( $content, $name, $mime );
        }

        $this->html    = $html;
        $this->htmlEnc = $encoding;

        return $this;
    }

    /**
     * @return string
     */
    public function openRelatedBoundary(): string
    {
        return "\r\n--$this->rboundary\r\n";
    }

    /**
     * @param $file
     *
     * @return string
     */
    public function messageFile( $file ): string
    {
        $content     = $file[ 'content' ];
        $name        = $file[ 'name' ];
        $encoding    = $file[ 'encoding' ];
        $disposition = $file[ 'disposition' ];
        $type        = $file[ 'type' ];

        return "Content-Type: $type; name=\"$name\"" . $this->newLine() . "Content-Transfer-Encoding: $encoding" . $this->newLine() . "Content-ID: <$name>" . $this->newLine() . "Content-Description: $name" . $this->newLine() . "Content-Disposition: $disposition" . $this->newLine() . $this->newLine() . $content . $this->newLine();
    }

    /**
     * @return string
     */
    public function closeRelatedBoundary(): string
    {
        return "\r\n--$this->rboundary--\r\n";
    }

    /**
     * @return string
     */
    public function closeAlternativeBoundary(): string
    {
        return "\r\n--$this->aboundary--\r\n";
    }

    /**
     * @return string
     */
    public function closeMixedBoundary(): string
    {
        return "\r\n--$this->mboundary--\r\n";
    }

    /**
     * @return string
     */
    public function headers(): string
    {
        $headers = 'MIME-Version: 1.0' . $this->newLine();

        foreach ( $this->headers as $key => $value ) {
            $headers .= $this->qHeader( $key ) . ': ' . $value . $this->newLine();
        }

        if ( count( $this->files ) ) {
            $headers .= $this->mime( 'mixed' );

        }
        elseif ( $this->text && $this->html ) {
            $headers .= $this->mime( 'alternative' );

        }
        elseif ( $this->html ) {
            $headers .= $this->mime( 'html', $this->htmlEnc );

        }
        elseif ( $this->text ) {
            $headers .= $this->mime( 'text', $this->textEnc );
        }

        return $headers . $this->newLine();
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function qHeader( string $name ): string
    {
        return str_replace( ' ', '-', ucwords( str_replace( [
                                                                '-',
                                                                '_',
                                                            ], ' ', strtolower( $name ) ) ) );
    }

    /**
     * @param string $content
     * @param string $name
     * @param bool   $type
     *
     * @return $this
     */
    public function inlineImage( string $content, string $name, bool $type ): self
    {
        $this->inlineImages[ $name ] = [
            'content'     => trim( chunk_split( $content ) ),
            'name'        => $name,
            'type'        => $type,
            'disposition' => 'inline;',
            'encoding'    => 'base64',
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function log(): array
    {
        return [
            'subject'  => $this->subject,
            'text'     => $this->text,
            'mailfrom' => $this->from,
            'mailto'   => $this->to,
            'replyto'  => $this->replyto,
        ];
    }

    /**
     * @param $url
     *
     * @return $this
     */
    public function pixel( $url ): self
    {
        $this->pixel = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->headers() . $this->message();
    }
}

?>