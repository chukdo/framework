<?php

namespace Chukdo\Storage;

use Chukdo\Helper\Str;
use Chukdo\Helper\Arr;

/**
 * Gestion des fichiers.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Storage
{
    /**
     * @param string $directory
     * @param int    $visibility
     *
     * @return bool
     */
    public function makeDirectory( string $directory, int $visibility = 0777 ): bool
    {
        return mkdir( $directory, $visibility, true );
    }

    /**
     * @param string $directory
     *
     * @return bool
     */
    public function deleteDirectory( string $directory ): bool
    {
        $dir = opendir( $directory );

        if ( $dir === false ) {
            throw new StorageException( sprintf( 'Can\'t open dir [%s]', $directory ) );
        }

        while ( ( $file = readdir( $dir ) ) !== false ) {
            if ( $this->isFile( $file ) ) {
                $full = $directory . '/' . $file;
                if ( is_dir( $full ) ) {
                    $this->deleteDirectory( $full );
                }
                else {
                    $this->delete( $full );
                }
            }
        }

        closedir( $dir );

        return rmdir( $directory );
    }

    protected function isFile( $file ): bool
    {
        return ( $file !== '.' ) && ( $file !== '..' );
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public function delete( string $file ): bool
    {
        return unlink( $file );
    }

    /**
     * @param string $directory
     *
     * @return array
     */
    public function directories( string $directory ): array
    {
        $list = [];
        $dir  = opendir( $directory );

        if ( $dir === false ) {
            throw new StorageException( sprintf( 'Can\'t open dir [%s]', $directory ) );
        }

        while ( ( $file = readdir( $dir ) ) !== false ) {
            if ( $this->isFile( $file ) ) {
                $full = $directory . '/' . $file;
                if ( is_dir( $full ) ) {
                    $list[] = $full;
                }
            }
        }
        closedir( $dir );

        return $list;
    }

    /**
     * Recursive.
     *
     * @param string $directory
     *
     * @return array
     */
    public function allDirectories( string $directory ): array
    {
        $list = [];
        $dir  = opendir( $directory );

        if ( $dir === false ) {
            throw new StorageException( sprintf( 'Can\'t open dir [%s]', $directory ) );
        }

        while ( ( $file = readdir( $dir ) ) !== false ) {
            if ( $this->isFile( $file ) ) {
                $full = $directory . '/' . $file;
                if ( is_dir( $full ) ) {
                    $list = Arr::push( $list, $this->allDirectories( $full ) );
                }
            }
        }

        closedir( $dir );

        return $list;
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public function exists( string $file ): bool
    {
        return file_exists( $file );
    }

    /**
     * @param string $file
     *
     * @return int
     */
    public function size( string $file ): int
    {
        return (int) filesize( $file );
    }

    /**
     * @param string $oldFile
     * @param string $newFile
     *
     * @return bool
     */
    public function copy( string $oldFile, string $newFile ): bool
    {
        return $this->put( $newFile, $this->get( $oldFile ) );
    }

    /**
     * @param string $file
     * @param string $content
     *
     * @return bool
     */
    public function put( string $file, string $content ): bool
    {
        return (bool) file_put_contents( $file, $content );
    }

    /**
     * @param string $file
     *
     * @return string
     */
    public function get( string $file ): string
    {
        return (string) file_get_contents( $file );
    }

    /**
     * @param string $oldFile
     * @param string $newFile
     *
     * @return bool
     */
    public function move( string $oldFile, string $newFile ): bool
    {
        $r = $this->put( $newFile, $this->get( $oldFile ) );
        $this->delete( $oldFile );

        return $r;
    }

    /**
     * @param string      $directory
     * @param string|null $match
     *
     * @return array
     */
    public function files( string $directory, string $match = null ): array
    {
        $list = [];
        $dir  = opendir( $directory );

        if ( $dir === false ) {
            throw new StorageException( sprintf( 'Can\'t open dir [%s]', $directory ) );
        }

        while ( ( $file = readdir( $dir ) ) !== false ) {
            if ( $this->isFile( $file ) ) {
                $full = $directory . '/' . $file;

                if ( !is_dir( $full ) && ( $match === null || Str::matchOne( $match, $full ) ) ) {
                    $list[] = $full;
                }
            }
        }

        closedir( $dir );

        return $list;
    }

    /**
     * @param string      $directory
     * @param string|null $match
     *
     * @return array
     */
    public function allFiles( string $directory, string $match = null ): array
    {
        $list = [];
        $dir  = opendir( $directory );

        if ( $dir === false ) {
            throw new StorageException( sprintf( 'Can\'t open dir [%s]', $directory ) );
        }

        while ( ( $file = readdir( $dir ) ) !== false ) {
            if ( $this->isFile( $file ) ) {
                $full = $directory . '/' . $file;

                if ( !is_dir( $full ) && ( $match === null || Str::matchOne( $match, $full ) ) ) {
                    $list = Arr::push( $list, $this->allFiles( $full, $match ) );
                }
            }
        }

        closedir( $dir );

        return $list;
    }
}
