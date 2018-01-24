<?php
/**
 * Created by PhpStorm.
 * User: janespen
 * Date: 24.01.18
 * Time: 11:35
 */

namespace Cerpus\AuthCore\utils;


use Cerpus\AuthCore\SessionInterface;

class SessionInterfaceInMemoryImpl implements SessionInterface {
    private $storage = [];

    public function put($key, $value) {
        $this->storage[$key] = $value;
    }

    public function remove($key) {
        unset($this->storage[$key]);
    }

    public function exists($key): bool {
        return isset($this->storage[$key]);
    }

    public function get($key) {
        return $this->exists($key) ? $this->storage[$key] : null;
    }
}