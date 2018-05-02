<?php

namespace Cerpus\AuthCore;

use GuzzleHttp\Client;

class AuthorizeRequest {
    private $config;

    private $toSignup = false;
    private $username = null;
    private $emailId = null;
    private $emailCode = null;
    private $language = null;
    private $singleSignoutEndpoint = null;
    private $requirements = 'v1';
    private $nonInteractive = false;
    private $useidp = null;

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
            'response_type' => 'code',
            'scope' => 'read profile',
            'state' => $state,
            'requirements' => $this->requirements,
        ];
        if ($abortUrl !== null) {
            $params['abort'] = $abortUrl;
        }
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
        if ($this->nonInteractive) {
            $params['type'] = 'non_interactive';
        }
        if ($this->useidp) {
            $params['useidp'] = $this->useidp;
        }

        $authParams = http_build_query($params);

        return $authServer . '/oauth/authorize?' . $authParams;
    }

    /**
     * @param $redirectUrl
     * @param $abortUrl
     * @param $state
     * @return \Cerpus\AuthCore\AuthorizeData
     */
    public function executeApi($redirectUrl, $abortUrl, $state) {
        $authServer = $this->config->getUrl();
        $authUser = $this->config->getClientId();

        $params = [
            'clientId' => $authUser,
            'redirectUri' => $redirectUrl,
            'state' => $state,
        ];
        if ($abortUrl !== null) {
            $params['abortUri'] = $abortUrl;
        }
        if ($this->language !== null) {
            $params['language'] = $this->language;
        }
        if ($this->emailId) {
            $params['emailId'] = $this->emailId;
        }
        if ($this->emailCode) {
            $params['emailCode'] = $this->emailCode;
        }
        if ($this->nonInteractive) {
            $params['authorizeType'] = 'non_interactive';
        }

        $uri =  $authServer . '/v1/login/authorize';

        $options = [
            "form_params" => $params
        ];

        $response = $this->httpClient()->post($uri, $options);

        if ($response->getStatusCode() == 200) {
            $responseBody = $response->getBody()->getContents();
            $authorizeData = json_decode($responseBody, false);

            $object = new AuthorizeData();
            $object->termsAndConditions = $authorizeData->termsAndConditions;
            $object->theme = $authorizeData->theme;
            $object->redirectLogin = $authorizeData->redirectLogin;
            $object->authorizeUri = $authorizeData->authorizeUri;
            $object->codeAuthorizeUri = $authorizeData->codeAuthorizeUri;
            $object->tokenAuthorizeUri = $authorizeData->tokenAuthorizeUri;
            $object->redirectUri = $authorizeData->redirectUri;
            $object->idpInfo = array_map(function ($idpDataIn) {
                $idpData = new AuthorizeDataIdpInfo();
                $idpData->id = $idpDataIn->id;
                $idpData->name = $idpDataIn->name;
                $idpData->uri = $idpDataIn->uri;
                $idpData->popupTokenUri = $idpDataIn->popupTokenUri;
                return $idpData;
            }, $authorizeData->idpInfo);
            $object->emailJwt = $authorizeData->emailJwt;
            $object->email = $authorizeData->email;
            $object->clientId = $authorizeData->clientId;
            $object->abortUri = $authorizeData->abortUri;
            $object->language = $authorizeData->language;
            $object->languageAlpha2 = $authorizeData->languageAlpha2;
            $object->logoutUri = $authorizeData->logoutUri;
            $object->authorizeType = $authorizeData->authorizeType;
            $object->username = $authorizeData->username;
            return $object;
        }

        return null;
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

    /**
     * @param null $type
     *
     * @return AuthorizeRequest
     */
    public function setNonInteractive(bool $nonInteractive) {
        $this->nonInteractive = $nonInteractive;
        return $this;
    }

    /**
     * @param null $useidp
     *
     * @return AuthorizeRequest
     */
    public function setUseidp($useidp) {
        $this->useidp = $useidp;
        return $this;
    }

    protected function httpClient(): Client {
        return new Client();
    }
}