<?php

namespace Chukdo\Contracts\Storage;

/**
 * Interface de stream wrapper.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface StreamWrapper
{
    /**
     * @return array|null
     */
    public function stream_stat(): ?array;

    /**
     * @param int $offset
     * @param int $whence
     * @return bool
     */
    public function stream_seek( int $offset, int $whence ): bool;

    /**
     * @return bool
     */
    public function dir_rewinddir(): bool;

    /**
     * @return string|false
     */
    public function dir_readdir();

    /**
     * @return int
     */
    public function stream_tell(): int;

    /**
     * @param string $path
     * @param int    $option
     * @param        $value
     * @return bool
     */
    public function stream_metadata( string $path, int $option, $value ): bool;

    public function stream_close(): void;

    /**
     * @param string $path
     * @param int    $mode
     * @param int    $options
     * @return bool
     */
    public function mkdir( string $path, int $mode, int $options ): bool;

    /**
     * @param string $path
     * @param int    $options
     * @return bool
     */
    public function rmdir( string $path, int $options ): bool;

    /**
     * @return bool
     */
    public function stream_flush(): bool;

    /**
     * @param int $count
     * @return string|null
     */
    public function stream_read( int $count ): ?string;

    /**
     * @param int $new_size
     * @return bool
     */
    public function stream_truncate( int $new_size ): bool;

    /**
     * @param int $cast_as
     * @return resource|false
     */
    public function stream_cast( int $cast_as );

    /**
     * @param string      $path
     * @param string      $mode
     * @param int         $options
     * @param string|null $opened_path
     * @return bool
     */
    public function stream_open( string $path, string $mode, int $options, ?string &$opened_path ): bool;

    /**
     * @param string $path
     * @param int    $options
     * @return bool
     */
    public function dir_opendir( string $path, int $options ): bool;

    /**
     * @return bool
     */
    public function stream_eof(): bool;

    /**
     * @param string $data
     * @return int
     */
    public function stream_write( string $data ): int;

    /**
     * @param string $path
     * @param int    $flags
     * @return array|null
     */
    public function url_stat( string $path, int $flags ): ?array;

    /**
     * @param string $path
     * @return bool
     */
    public function unlink( string $path ): bool;

    /**
     * @param int $option
     * @param int $arg1
     * @param int $arg2
     * @return bool
     */
    public function stream_set_option( int $option, int $arg1, int $arg2 ): bool;

    /**
     * @param string $path_from
     * @param string $path_to
     * @return bool
     */
    public function rename( string $path_from, string $path_to ): bool;

    /**
     * @param int $operation
     * @return bool
     */
    public function stream_lock( int $operation ): bool;

    /**
     * @return bool
     */
    public function dir_closedir(): bool;
}
