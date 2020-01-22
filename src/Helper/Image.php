<?php

namespace Chukdo\Helper;

/**
 * Classe Image
 * Manipulation d'images.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Image
{
    /**
     * Constructeur privé, empeche l'intanciation de la classe statique.
     */
    private function __construct()
    {
    }

    /**
     * @param string $file
     *
     * @return array
     */
    public static function loadImageFromFile( string $file ): array
    {
        $string = file_get_contents( $file );

        if ( $string === false ) {
            throw new ImageException( sprintf( 'Can\'t load image from file [%s]', $file ) );
        }

        return self::loadImageFromString( $string );
    }

    /**
     * Retourne les proprietes d'une images (largeur, hauteur, mime-type, image sous forme de resource).
     *
     * @param string $string
     *
     * @return array
     */
    public static function loadImageFromString( string $string ): array
    {
        $image = imagecreatefromstring( $string );

        if ( $image === false ) {
            throw new ImageException( 'Can\'t load image from string' );
        }

        $f = finfo_open();

        if ( $f === false ) {
            throw new ImageException( 'Can\'t create finfo resource' );
        }

        $mimeType = finfo_buffer( $f, $string, FILEINFO_MIME_TYPE );

        return [
            'w' => imagesx( $image ),
            'h' => imagesy( $image ),
            't' => $mimeType,
            'i' => $image,
        ];
    }

    /**
     * @param string $base64
     *
     * @return array
     */
    public static function loadImageFromBase64( string $base64 ): array
    {
        $pattern  = '/^data:image\/[a-z]{3,4};base64,/';
        $toDecode = preg_replace( $pattern, '', $base64 );

        if ( $toDecode === null ) {
            throw new ImageException( 'Can\'t decode image pattern' );
        }

        return self::loadImageFromString( base64_decode( $toDecode ) );
    }

    /**
     * @param array $image [w, h, t, i] (self::loadImage*)
     * @param int   $quality
     *
     * @return string
     */
    public static function convertToJpg( array $image, int $quality ): string
    {
        return self::convert( $image, IMAGETYPE_JPEG, $quality );
    }

    /**
     * Converti une image dans un autre format.
     *
     * @param array    $image   [w, h, t, i] (self::loadImage*)
     * @param int      $format  type d'image (IMAGETYPE_GIF | IMAGETYPE_JPEG | IMAGETYPE_PNG)
     * @param int|null $quality (0 à 100)
     *
     * @return string|null
     */
    public static function convert( array $image, int $format, int $quality = null ): string
    {
        $w   = $image[ 'w' ];
        $h   = $image[ 'h' ];
        $src = $image[ 'i' ];
        $dst = imagecreatetruecolor( $w, $h );

        if ( $src && $dst ) {
            imagecopy( $dst, $src, 0, 0, 0, 0, $w, $h );
            $r = self::getImage( $dst, $format, $quality ?? 92 );
            imagedestroy( $src );
            imagedestroy( $dst );

            return $r;
        }

        throw new ImageException( sprintf( 'Can\'t convert image to format [%s] missing src or dst param', $format ) );
    }

    /**
     * Retourne le flux d'une nouvelle image (que l'on peut sauver dans un fichier).
     *
     * @param          $image  (imagecreatetruecolor)
     * @param int      $format type d'image (IMAGETYPE_GIF | IMAGETYPE_JPEG | IMAGETYPE_PNG)
     * @param int|null $quality
     *
     * @return string
     */
    public static function getImage( $image, int $format, int $quality = null ): string
    {
        ob_start();
        switch ( $format ) {
            case 'image/gif':
                imagegif( $image );
                break;
            case 'image/jpeg':
                imagejpeg( $image, null, $quality ?? 85 );
                break;
            case 'image/png':
                $quality = (int) ( 9 - abs( floor( ( ( $quality ?? 85 ) - 1 ) / 10 ) ) );
                imagealphablending( $image, false );
                imagesavealpha( $image, true );
                imagepng( $image, null, $quality ?? 85 );
                break;
        }

        return (string) ob_get_clean();
    }

    /**
     * @param array $image [w, h, t, i] (self::loadImage*)
     * @param int   $quality
     *
     * @return string
     */
    public static function convertToPng( array $image, $quality ): string
    {
        return self::convert( $image, IMAGETYPE_PNG, $quality );
    }

    /**
     * @param array $image [w, h, t, i] (self::loadImage*)
     *
     * @return string
     */
    public static function convertToGif( array $image ): string
    {
        return self::convert( $image, IMAGETYPE_GIF );
    }

    /**
     * Resize une image (proportionnel).
     *
     * @param array    $image [w, h, t, i] (self::loadImage*)
     * @param int      $dw    largeur
     * @param int|null $dh    hauteur
     *
     * @return string
     */
    public static function resize( array $image, int $dw = 0, int $dh = null ): string
    {
        $sw = $image[ 'w' ];
        $sh = $image[ 'h' ];
        $h  = 0;
        $w  = 0;

        if ( $dw > 0 && $dh > 0 ) {
            $rw = $sw / $dw;
            $rh = $sh / $dh;
            $r  = max( $rw, $rh );
            $w  = $sw / $r;
            $h  = $sh / $r;
        }
        elseif ( $dw > 0 ) {
            $w = $dw;
            $h = $w * $sh / $sw;
        }
        elseif ( $dh > 0 ) {
            $h = $dh;
            $w = $h * $sw / $sh;
        }

        /** Image source trop petite pour etre redimensionner */
        $whd = $dw > 0 && $dh > 0 && $dw >= $sw && $dh >= $sh;
        $wd  = $dw > 0 && $dw >= $sw;
        $hd  = $dh > 0 && $dh >= $sh;

        if ( $whd || $wd || $hd ) {
            return self::getImage( $image[ 'i' ], $image[ 't' ] );
        }

        if ( $w > 0 && $h > 0 ) {
            return self::resampleImage( $image, 0, 0, (int) $w, (int) $h );
        }

        throw new ImageException( sprintf( 'Can\'t resize image to [%s, %s]', $dw, $dh ) );
    }

    /**
     * Retaille une image.
     *
     * @param array    $image [w, h, t, i]
     * @param int      $sx    point de depart x
     * @param int      $sy    point de depart y
     * @param int      $dw    destination largeur
     * @param int      $dh    destination hauteur
     * @param int      $sw    source largeur
     * @param int|null $sh    source hauteur
     *
     * @return string
     */
    public static function resampleImage( array $image, int $sx, int $sy, int $dw, int $dh, int $sw = 0, int $sh = null ): string
    {
        $type = $image[ 't' ] ?? false;
        $src  = $image[ 'i' ] ?? false;
        $dst  = imagecreatetruecolor( $dw, $dh );

        if ( $dst === false ) {
            throw new ImageException( 'Can\'t resample image dst missing' );
        }

        if ( $src === false ) {
            throw new ImageException( 'Can\'t resample image src missing' );
        }

        $sw = $sw > 0
            ? $sw
            : $image[ 'w' ];
        $sh = $sh > 0
            ? $sh
            : $image[ 'h' ];
        $dx = 0;
        $dy = 0;

        if ( $type === 'image/png' ) {
            imagesavealpha( $dst, true );
            $alpha = imagecolorallocatealpha( $dst, 255, 255, 255, 127 );
            imagefill( $dst, 0, 0, $alpha );
        }

        imagecopyresampled( $dst, $src, $dx, $dy, $sx, $sy, $dw, $dh, $sw, $sh );
        $r = self::getImage( $dst, $type, 92 );
        imagedestroy( $src );
        imagedestroy( $dst );

        return $r;
    }

    /**
     * Crop une image.
     *
     * @param array $image [w, h, t, i]
     * @param int   $dx    point de depart x
     * @param int   $dy    point de depart y
     * @param int   $dw    largeur
     * @param int   $dh    hauteur
     *
     * @return string
     */
    public static function crop( array $image, int $dx, int $dy, int $dw, int $dh ): string
    {
        $sw = $image[ 'w' ];
        $sh = $image[ 'h' ];

        /** Taille finale > taille initiale */
        if ( $dx + $dw > $sw || $dy + $dh > $sh ) {
            throw new ImageException( 'Can\'t crop image final size is greater than original size' );
        }

        return self::resampleImage( $image, $dx, $dy, $dw, $dh, $dw, $dh );
    }
}
