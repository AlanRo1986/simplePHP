<?php
/**
 * Created by PhpStorm.
 * User: Alan 341455770@qq.com
 * Date: 2017/05/23 0023
 * Time: 16:53
 */

namespace App\System\Handler;


use App\System\BasicInterface\FileHandlerInterface;
use Exception;
use FilesystemIterator;

class FileHandler implements FileHandlerInterface
{
    public function __construct()
    {

    }

    /**
     * @param string $path
     * @return bool
     */
    public function exists(string $path):bool
    {
        // TODO: Implement exists() method.

        return file_exists($path);
    }

    /**
     * @param string $path
     * @param bool $lock
     * @return mixed
     * @throws Exception
     */
    public function get(string $path, bool $lock = false)
    {
        // TODO: Implement get() method.

        if ($this->isFile($path)) {
            return $lock ? $this->sharedGet($path) : file_get_contents($path);
        }

        throw new Exception("File does not exist at path {$path}");

    }

    /**
     * Get the returned value of a file.
     *
     * @param  string $path
     * @return mixed
     * @throws Exception
     */
    public function getRequire(string $path)
    {
        if ($this->isFile($path)) {
            return require $path;
        }
        throw new Exception("File does not exist at path {$path}");
    }

    /**
     * Require the given file once.
     * @param string $path
     * @return mixed
     * @throws Exception
     */
    public function getRequireOnce(string $path)
    {
        if ($this->isFile($path)) {
            return require_once $path;
        }
        throw new Exception("File does not exist at path {$path}");
    }

    /**
     * @param string $path
     * @param string $content
     * @param bool $lock
     * @return mixed
     */
    public function put(string $path, string $content, bool $lock = false)
    {
        // TODO: Implement put() method.
        return file_put_contents($path, $content, $lock ? LOCK_EX : 0);
    }

    /**
     * @param string $path
     * @param string $content
     * @return mixed
     */
    public function prepend(string $path, string $content)
    {
        // TODO: Implement prepend() method.
        if ($this->exists($path)) {
            return $this->put($path, $content.$this->get($path));
        }

        return $this->put($path, $content);
    }

    /**
     * @param string $path
     * @param string $content
     * @return mixed
     */
    public function append(string $path, string $content)
    {
        // TODO: Implement append() method.
        return file_put_contents($path, $content, FILE_APPEND);
    }

    /**
     * @param string $path
     * @param null $mode
     * @return mixed
     */
    public function chmod(string $path, $mode = null)
    {
        // TODO: Implement chmod() method.

        if ($mode) {
            return chmod($path, $mode);
        }

        return substr(sprintf('%o', fileperms($path)), -4);
    }

    /**
     * @param string|array $paths
     * @return mixed
     */
    public function delete($paths)
    {
        // TODO: Implement delete() method.
        $paths = is_array($paths) ? $paths : func_get_args();

        $success = true;

        foreach ($paths as $path) {
            try {
                if (! @unlink($path)) {
                    $success = false;
                }
            } catch (Exception $e) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @param string $path
     * @param string $target
     * @return mixed
     */
    public function move(string $path, string $target):bool
    {
        // TODO: Implement move() method.
        return rename($path, $target);
    }

    /**
     * @param string $path
     * @param string $target
     * @return mixed
     */
    public function copy(string $path, string $target):bool
    {
        // TODO: Implement copy() method.
        return copy($path, $target);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function name(string $path)
    {
        // TODO: Implement name() method.
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function basename(string $path)
    {
        // TODO: Implement basename() method.
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function dirname(string $path)
    {
        // TODO: Implement dirname() method.
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function extension(string $path)
    {
        // TODO: Implement extension() method.
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function type(string $path)
    {
        // TODO: Implement type() method.
        return filetype($path);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function mimeType(string $path)
    {
        // TODO: Implement mimeType() method.
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function size(string $path)
    {
        // TODO: Implement size() method.
        return filesize($path);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function lastModified(string $path)
    {
        // TODO: Implement lastModified() method.
        return filemtime($path);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function isDirectory(string $path)
    {
        // TODO: Implement isDirectory() method.
        return is_dir($path);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function isReadable(string $path)
    {
        // TODO: Implement isReadable() method.
        return is_readable($path);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function isWritable(string $path)
    {
        // TODO: Implement isWritable() method.
        return is_writable($path);
    }

    /**
     * @param string $path
     * @return mixed
     */
    public function isFile(string $path)
    {
        // TODO: Implement isFile() method.
        return is_file($path);
    }

    /**
     * @param string $pattern
     * @param int $flags
     * @return mixed
     */
    public function glob(string $pattern, int $flags = 0)
    {
        // TODO: Implement glob() method.
        return glob($pattern, $flags);
    }

    /**
     * @param string $directory
     * @return mixed
     */
    public function files(string $directory)
    {
        // TODO: Implement files() method.
        $glob = glob($directory.'/*');

        if ($glob === false) {
            return [];
        }

        // To get the appropriate files, we'll simply glob the directory and filter
        // out any "files" that are not truly files so we do not end up with any
        // directories in our list, but only true files within the directory.
        return array_filter($glob, function ($file) {
            return filetype($file) == 'file';
        });
    }

    /**
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @param bool $force
     * @return mixed
     */
    public function makeDirectory(string $path, int $mode = 0755, bool $recursive = false, bool $force = false)
    {
        // TODO: Implement makeDirectory() method.
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }
        return mkdir($path, $mode, $recursive);
    }

    /**
     * @param string $from
     * @param string $to
     * @param bool $overwrite
     * @return mixed
     */
    public function moveDirectory(string $from, string $to, bool $overwrite = false)
    {
        // TODO: Implement moveDirectory() method.
        if ($overwrite && $this->isDirectory($to)) {
            if (! $this->deleteDirectory($to)) {
                return false;
            }
        }
        return @rename($from, $to) === true;
    }

    /**
     * @param string $from
     * @param string $to
     * @param null $options
     * @return mixed
     */
    public function copyDirectory(string $from, string $to, $options = null)
    {
        // TODO: Implement copyDirectory() method.
        if (! $this->isDirectory($from)) {
            return false;
        }

        $options = $options ?: FilesystemIterator::SKIP_DOTS;

        // If the destination directory does not actually exist, we will go ahead and
        // create it recursively, which just gets the destination prepared to copy
        // the files over. Once we make the directory we'll proceed the copying.
        if (! $this->isDirectory($to)) {
            $this->makeDirectory($to, 0777, true);
        }

        $items = new FilesystemIterator($from, $options);

        foreach ($items as $item) {
            // As we spin through items, we will check to see if the current file is actually
            // a directory or a file. When it is actually a directory we will need to call
            // back into this function recursively to keep copying these nested folders.
            $target = $to.'/'.$item->getBasename();

            if ($item->isDir()) {
                $path = $item->getPathname();

                if (! $this->copyDirectory($path, $target, $options)) {
                    return false;
                }
            }

            // If the current items is just a regular file, we will just copy this to the new
            // location and keep looping. If for some reason the copy fails we'll bail out
            // and return false, so the developer is aware that the copy process failed.
            else {
                if (! $this->copy($item->getPathname(), $target)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param string $directory
     * @param bool $preserve
     * @return mixed
     */
    public function deleteDirectory(string $directory, $preserve = false)
    {
        // TODO: Implement deleteDirectory() method.
        if (! $this->isDirectory($directory)) {
            return false;
        }

        $items = new FilesystemIterator($directory);

        foreach ($items as $item) {
            // If the item is a directory, we can just recurse into the function and
            // delete that sub-directory otherwise we'll just delete the file and
            // keep iterating through each file until the directory is cleaned.
            if ($item->isDir() && ! $item->isLink()) {
                $this->deleteDirectory($item->getPathname());
            }

            // If the item is just a file, we can go ahead and delete it since we're
            // just looping through and waxing all of the files in this directory
            // and calling directories recursively, so we delete the real path.
            else {
                $this->delete($item->getPathname());
            }
        }

        if (! $preserve) {
            @rmdir($directory);
        }

        return true;
    }

    /**
     * @param string $directory
     * @return mixed
     */
    public function cleanDirectory(string $directory)
    {
        // TODO: Implement cleanDirectory() method.
        return $this->deleteDirectory($directory, true);
    }

    /**
     * Get contents of a file with shared access.
     *
     * @param  string  $path
     * @return string
     */
    protected function sharedGet(string $path):string
    {
        $contents = '';
        $handle = fopen($path, 'rb');
        if ($handle) {
            try {
                if (flock($handle, LOCK_SH)) {
                    clearstatcache(true, $path);
                    $contents = fread($handle, $this->size($path) ?: 1);
                    flock($handle, LOCK_UN);
                }
            } finally {
                fclose($handle);
            }
        }

        return $contents;
    }


}