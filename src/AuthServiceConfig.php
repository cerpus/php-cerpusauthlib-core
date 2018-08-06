<?php

namespace Cerpus\AuthCore;

class AuthServiceConfig {
    private $url;
    private $clientId;
    private $secret;

    /**
     * @param mixed $url
     *
     * @return AuthServiceConfig
     */
    public function setUrl($url) {
        $this->url = $url;
        return $this;
}

    /**
     * @param mixed $clientId
     *
     * @return AuthServiceConfig
     */
    public function setClientId($clientId) {
        $this->clientId = $clientId;
        return $this;
}

    /**
     * @param mixed $secret
     *
     * @return AuthServiceConfig
     */
    public function setSecret($secret) {
        $this->secret = $secret;
        return $this;
}

    /**
     * @return mixed
     */
    public function getUrl() {
        /*
         * Consistently return without the trailing slash
         */
        $url = $this->url;
        while (substr($url, -1) == "/") {
            $url = substr($url, 0, -1);
        }
        return $url;
    }

    /**
     * @return mixed
     */
    public function getClientId() {
        return $this->clientId;
    }

    /**
     * @return mixed
     */
    public function getSecret() {
        return $this->secret;
    }
}