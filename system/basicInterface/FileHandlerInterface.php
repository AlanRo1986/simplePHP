<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/26 0026
 * Time: 14:39
 */

namespace App\System\BasicInterface;


interface FileHandlerInterface
{

    /**
     * @param string $path
     * @return bool
     */
    public function exists(string $path):bool ;

    /**
     * @param string $path
     * @param bool $lock
     * @return mixed
     */
    public function get(string $path,bool $lock = false);

    /**
     * @param string $path
     * @param string $content
     * @param bool $lock
     * @return mixed
     */
    public function put(string $path, string $content, bool $lock = false);

    /**
     * @param string $path
     * @param string $content
     * @return mixed
     */
    public function prepend(string $path, string $content);

    /**
     * @param string $path
     * @param string $content
     * @return mixed
     */
    public function append(string $path, string $content);

    /**
     * @param string $path
     * @param null $mode
     * @return mixed
     */
    public function chmod(string $path, $mode = null);

    /**
     * @param $paths
     * @return mixed
     */
    public function delete($paths);

    /**
     * @param string $path
     * @param string $target
     * @return mixed
     */
    public function move(string $path,string $target);

    /**
     * @param string $path
     * @param string $target
     * @return mixed
     */
    public function copy(string $path,string $target);

    /**
     * @param string $path
     * @return mixed
     */
    public function name(string $path);

    /**
     * @param string $path
     * @return mixed
     */
    public function basename(string $path);

    /**
     * @param string $path
     * @return mixed
     */
    public function dirname(string $path);

    /**
     * @param string $path
     * @return mixed
     */
    public function extension(string $path);

    /**
     * @param string $path
     * @return mixed
     */
    public function type(string $path);

    /**
     * @param string $path
     * @return mixed
     */
    public function mimeType(string $path);

    /**
     * @param string $path
     * @return mixed
     */
    public function size(string $path);

    /**
     * @param string $path
     * @return mixed
     */
    public function lastModified(string $path);

    /**
     * @param string $path
     * @return mixed
     */
    public function isDirectory(string $path);

    /**
     * @param string $path
     * @return mixed
     */
    public function isReadable(string $path);

    /**
     * @param string $path
     * @return mixed
     */
    public function isWritable(string $path);

    /**
     * @param string $path
     * @return mixed
     */
    public function isFile(string $path);

    /**
     * @param string $pattern
     * @param int $flags
     * @return mixed
     */
    public function glob(string $pattern,int $flags = 0);

    /**
     * @param string $directory
     * @return mixed
     */
    public function files(string $directory);

    /**
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @param bool $force
     * @return mixed
     */
    public function makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false);

    /**
     * @param string $from
     * @param string $to
     * @param bool $overwrite
     * @return mixed
     */
    public function moveDirectory(string $from, string $to,bool $overwrite = false);

    /**
     * @param string $from
     * @param string $to
     * @param null $options
     * @return mixed
     */
    public function copyDirectory(string $from, string $to, $options = null);

    /**
     * @param string $directory
     * @param bool $preserve
     * @return mixed
     */
    public function deleteDirectory(string $directory, $preserve = false);

    /**
     * @param string $directory
     * @return mixed
     */
    public function cleanDirectory(string $directory);

}