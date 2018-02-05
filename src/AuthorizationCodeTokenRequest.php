<?php

namespace Cerpus\AuthCore;


class AuthorizationCodeTokenRequest extends TokenRequest {
    private $redirectUri;
    private $code;

    public function __construct(AuthServiceConfig $config, $redirectUri, $code) {
        parent::__construct($config, "authorization_code");
        $this->redirectUri = $redirectUri;
        $this->code = $code;
    }
    public function getParams() {
        $params = parent::getParams();
        if ($this->redirectUri !== null) {
            $params['redirect_uri'] = $this->redirectUri;
        }
        $params['code'] = $this->code;
        return $params;
    }

    public function getMethod() {
        return 'GET';
    }

    public function getUrl() {
        $url = parent::getUrl();
        $params = $this->getParams();
        $query = http_build_query($params);
        return $url.'?'.$query;
    }

    public function getBodyParams() {
        return null;
    }
}