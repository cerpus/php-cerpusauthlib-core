<?php

namespace Cerpus\AuthCore;


class ClientCredentialsTokenRequest extends TokenRequest {
    public function __construct(AuthServiceConfig $config) {
        parent::__construct($config, 'client_credentials');
    }

    public function getMethod() {
        return 'POST';
    }

    public function getBodyParams() {
        return $this->getParams();
    }
}