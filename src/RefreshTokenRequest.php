<?php

namespace Cerpus\AuthCore;


class RefreshTokenRequest extends TokenRequest {
    private $refreshToken;

    public function __construct(AuthServiceConfig $config, $refreshToken) {
        parent::__construct($config, "refresh_token");
        $this->refreshToken = $refreshToken;
    }

    public function getParams() {
        $params = parent::getParams();
        $params['refresh_token'] = $this->refreshToken;
        return $params;
    }

    public function getMethod() {
        return 'POST';
    }

    public function getBodyParams() {
        return $this->getParams();
    }
}