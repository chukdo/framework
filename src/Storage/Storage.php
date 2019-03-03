<?php namespace Chukdo\Storage;

use Chukdo\Helper\Str;

/**
 * Gestion des fichiers
 *
 * @package     Storage
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Storage
{
    /**
     * @param string $directory
     * @param int $visibility
     * @return bool
     */
    public function makeDirectory(string $directory, int $visibility = 0777): bool
    {
        return mkdir($directory, $visibility, true);
    }

    /**
     * @param string $directory
     * @return bool
     */
    public function deleteDirectory(string $directory): bool
    {
        $dir = opendir($directory);

        while (($file = readdir($dir)) !== false) {
            if (($file != '.') && ($file != '..')) {
                $full = $directory . '/' . $file;

                if (is_dir($full)) {
                    $this->deleteDirectory($full);

                } else {
                    $this->delete($full);
                }
            }
        }

        closedir($dir);

        return rmdir($directory);
    }

    /**
     * @param string $directory
     * @return array
     */
    public function directories(string $directory): array
    {
        $list   = [];
        $dir    = opendir($directory);

        while (($file = readdir($dir)) !== false) {
            if (($file != '.') && ($file != '..')) {
                $full = $directory . '/' . $file;

                if (is_dir($full)) {
                    $list[] = $full;
                }
            }
        }

        closedir($dir);

        return $list;
    }

    /**
     * Recursive
     *
     * @param string $directory
     * @return array
     */
    public function allDirectories(string $directory): array
    {
        $list   = [];
        $dir    = opendir($directory);

        while (($file = readdir($dir)) !== false) {
            if (($file != '.') && ($file != '..')) {
                $full = $directory . '/' . $file;

                if (is_dir($full)) {
                    $list = array_merge($list, $this->allDirectories($full));
                }
            }
        }

        closedir($dir);

        return $list;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * @param string $path
     * @return int
     */
    public function size(string $path): int
    {
        return filesize($path);
    }

    /**
     * @param string $path
     * @param string $content
     * @return bool
     */
    public function put(string $path, string $content): bool
    {
        return (bool) file_put_contents($path, $content);
    }

    /**
     * @param string $path
     * @return string
     */
    public function get(string $path): string
    {
        return file_get_contents($path);
    }

    /**
     * @param string $oldPath
     * @param string $newPath
     * @return bool
     */
    public function copy(string $oldPath, string $newPath): bool
    {
        return $this->put($newPath, $this->get($oldPath));
    }

    /**
     * @param string $oldPath
     * @param string $newPath
     * @return bool
     */
    public function move(string $oldPath, string $newPath): bool
    {
        $r  = $this->put($newPath, $this->get($oldPath));

        $this->delete($oldPath);

        return $r;
    }

    /**
     * @param string $file
     * @return bool
     */
    public function delete(string $file): bool
    {
        return unlink($file);
    }

    /**
     * @param string $directory
     * @param string|null $match
     * @return array
     */
    public function files(string $directory, string $match = null): array
    {
        $list   = [];
        $dir    = opendir($directory);

        while (($file = readdir($dir)) !== false) {
            if (($file != '.') && ($file != '..')) {
                $full = $directory . '/' . $file;

                if (!is_dir($full) && Str::match($match, $full)) {
                    $list[] = $full;
                }
            }
        }

        closedir($dir);

        return $list;
    }

    /**
     * @param string $directory
     * @param string|null $match
     * @return array
     */
    public function allFiles(string $directory, string $match = null): array
    {
        $list   = [];
        $dir    = opendir($directory);

        while (($file = readdir($dir)) !== false) {
            if (($file != '.') && ($file != '..')) {
                $full = $directory . '/' . $file;

                if (!is_dir($full) && Str::match($match, $full)) {
                    $list = array_merge($list, $this->allFiles($full, $match));
                }
            }
        }

        closedir($dir);

        return $list;
    }
}