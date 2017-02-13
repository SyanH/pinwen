<?php
/**
 * Created by PhpStorm.
 * User: Syan
 * Date: 2017/2/3
 * Time: 11:24
 */

namespace app\helpers;


class FileCache
{
    private $cachePath = '/tmp/cache/';

    private $prefix = 'app';

    public function __construct($cachePath = null, $prefix = null)
    {
        if (null !== $cachePath) $this->cachePath = rtrim($cachePath, '/');
        if (null !== $prefix) $this->prefix = $prefix;
    }

    public function setCachePath($cachePath)
    {
        $this->cachePath = rtrim($cachePath, '/');
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    public function set($key, $value, $duration = -1)
    {
        $expire = ($duration==-1) ? -1 : (time() + (is_string($duration) ? strtotime($duration) : $duration));
        $safeVar = [
            'expire' => $expire,
            'value' => serialize($value)
        ];
        file_put_contents($this->getFilePath($key) , serialize($safeVar), LOCK_EX);
    }

    public function get($key, $default = null)
    {
        $value = @file_get_contents($this->getFilePath($key));
        if ($value) {
            $time = time();
            $value  = unserialize($value);
            if (($value['expire'] < $time) && $value['expire'] != -1) {
                $this->delete($key);
                return $default;
            }
            return unserialize($value['value']);
        }
        return $default;
    }

    public function delete($key)
    {
        $file = $this->getFilePath($key);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    public function clear(){
        $iterator = new \RecursiveDirectoryIterator($this->cachePath);
        foreach($iterator as $file) {
            if ($file->isFile() && substr($file, -6) == ".cache") {
                @unlink($this->cachePath . '/' . $file->getFilename());
            }
        }
    }

    protected function getFilePath($key)
    {
        return $this->cachePath . '/' . $this->prefix . '-' . md5($this->prefix . $key) . ".cache";
    }

}