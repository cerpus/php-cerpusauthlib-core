<?php

namespace Cerpus\AuthCore\utils;

class SessionWrapper implements \Cerpus\AuthCore\SessionInterface {
    private $session;

    public function __construct($session) {
        $this->session = $session;
    }

    public function put($key, $value) {
        $this->session->put($key, $value);
    }

    public function remove($key) {
        $this->session->remove($key);
    }

    public function exists($key): bool {
        return $this->session->exists($key);
    }

    public function get($key) {
        return $this->session->get($key);
    }

    public function putAll($associativeArray) {
        foreach ($associativeArray as $key => $value) {
            $this->put($key, $value);
        }
    }
}