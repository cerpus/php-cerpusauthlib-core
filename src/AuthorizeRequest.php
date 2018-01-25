<?php

namespace Cerpus\AuthCore;

class AuthorizeRequest {
    private $config;

    private $toSignup = false;
    private $username = null;
    private $emailId = null;
    private $emailCode = null;
    private $language = null;
    private $singleSignoutEndpoint = null;
    private $requirements = 'v1';

    public function __construct(AuthServiceConfig $config) {
        $this->config = $config;
    }

    /**
     * @param bool $toSignup
     *
     * @return AuthorizeRequest
     */
    public function setToSignup(bool $toSignup): AuthorizeRequest {
        $this->toSignup = $toSignup;
        return $this;
    }

    /**
     * @param null $username
     *
     * @return AuthorizeRequest
     */
    public function setUsername($username) {
        $this->username = $username;
        return $this;
    }

    /**
     * @param null $emailId
     *
     * @return AuthorizeRequest
     */
    public function setEmailId($emailId) {
        $this->emailId = $emailId;
        return $this;
    }

    /**
     * @param null $emailCode
     *
     * @return AuthorizeRequest
     */
    public function setEmailCode($emailCode) {
        $this->emailCode = $emailCode;
        return $this;
    }

    public function getAuthorizeUrl($redirectUrl, $abortUrl, $state) {
        $authServer = $this->config->getUrl();
        $authUser = $this->config->getClientId();

        $params = [
            'client_id' => $authUser,
            'redirect_uri' => $redirectUrl,
            'abort' => $abortUrl,
            'response_type' => 'code',
            'scope' => 'read profile',
            'state' => $state,
            'requirements' => $this->requirements,
        ];
        if ($this->language !== null) {
            $params['language'] = $this->language;
        }
        if ($this->singleSignoutEndpoint !== null) {
            $params['signout'] = $this->singleSignoutEndpoint;
        }
        if($this->toSignup){
            $params['type'] = 'signup';
        }
        if ($this->username) {
            $params['username'] = $this->username;
        }
        if ($this->emailId) {
            $params['email_id'] = $this->emailId;
        }
        if ($this->emailCode) {
            $params['email_code'] = $this->emailCode;
        }

        $authParams = http_build_query($params);

        return $authServer . '/oauth/authorize?' . $authParams;
    }

    /**
     * @param null $language
     *
     * @return AuthorizeRequest
     */
    public function setLanguage($language) {
        $this->language = $language;
        return $this;
    }

    /**
     * @param null $singleSignoutEndpoint
     *
     * @return AuthorizeRequest
     */
    public function setSingleSignoutEndpoint($singleSignoutEndpoint) {
        $this->singleSignoutEndpoint = $singleSignoutEndpoint;
        return $this;
    }

    /**
     * @param string $requirements
     *
     * @return AuthorizeRequest
     */
    public function setRequirements(string $requirements): AuthorizeRequest {
        $this->requirements = $requirements;
        return $this;
    }
}