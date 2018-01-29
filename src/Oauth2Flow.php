<?php

namespace Cerpus\AuthCore;


class Oauth2Flow {
    private $config;
    private $integration;
    private $session;

    private $stateId;
    private $code;

    private $state = null;
    private $toSignup = false;
    private $username = null;
    private $emailId = null;
    private $emailCode = null;
    private $language = null;
    private $singleSignoutEndpoint = null;
    private $requirements = 'v1';
    private $nonInteractive = false;
    private $useidp = null;
    private $successUrl = null;
    private $failureUrl = null;

    protected function __construct(AuthServiceConfig $config, AuthCoreIntegration $integration, $returnParams=null) {
        $this->config = $config;
        $this->integration = $integration;
        $this->session = $integration->session();

        if ($returnParams !== null) {
            if (!isset($returnParams['state'])) {
                throw new \Exception("Bad response from oauth2 authorize, missing state");
            }
            if (isset($returnParams['code'])) {
                $this->code = $returnParams['code'];
            } else {
                $this->code = null;
            }
            if (isset($returnParams['error'])) {
                $this->errors = $returnParams['error'];
            } else {
                $this->error = null;
            }
            $this->stateId = $returnParams['state'];

            $stateData = $this->session->get('state-'.$this->stateId);

            $this->state = static::stateField($stateData, 'state', null);
            $this->toSignup = static::stateField($stateData, 'toSignup', false);
            $this->username = static::stateField($stateData, 'username', null);
            $this->emailId = static::stateField($stateData, 'emailId', null);
            $this->emailCode = static::stateField($stateData, 'emailCode', null);
            $this->language = static::stateField($stateData, 'language', null);
            $this->singleSignoutEndpoint = static::stateField($stateData, 'singleSignoutEndpoint', null);
            $this->requirements = static::stateField($stateData, 'requirements', null);
            $this->nonInteractive = static::stateField($stateData, 'nonInteractive', false);
            $this->useidp = static::stateField($stateData, 'useidp', null);
            $this->successUrl = static::stateField($stateData, 'successUrl', null);
            $this->failureUrl = static::stateField($stateData, 'failureUrl', null);
        } else {
            $this->stateId = sha1(mt_rand().mt_rand());
        }
    }

    /**
     * @param mixed|null $state
     *
     * @return Oauth2Flow
     */
    public function setState(mixed $state): Oauth2Flow {
        $this->state = $state;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getState(): mixed {
        return $this->state;
    }

    /**
     * @param bool|mixed $toSignup
     *
     * @return Oauth2Flow
     */
    public function setToSignup($toSignup) {
        $this->toSignup = $toSignup;
        return $this;
    }

    /**
     * @return bool|mixed
     */
    public function getToSignup() {
        return $this->toSignup;
    }

    /**
     * @param mixed|null $username
     *
     * @return Oauth2Flow
     */
    public function setUsername(mixed $username): Oauth2Flow {
        $this->username = $username;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getUsername(): mixed {
        return $this->username;
    }

    /**
     * @param mixed|null $emailId
     *
     * @return Oauth2Flow
     */
    public function setEmailId(mixed $emailId): Oauth2Flow {
        $this->emailId = $emailId;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getEmailId(): mixed {
        return $this->emailId;
    }

    /**
     * @param mixed|null $language
     *
     * @return Oauth2Flow
     */
    public function setLanguage(mixed $language): Oauth2Flow {
        $this->language = $language;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getLanguage(): mixed {
        return $this->language;
    }

    /**
     * @param mixed|null $singleSignoutEndpoint
     *
     * @return Oauth2Flow
     */
    public function setSingleSignoutEndpoint(mixed $singleSignoutEndpoint): Oauth2Flow {
        $this->singleSignoutEndpoint = $singleSignoutEndpoint;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getSingleSignoutEndpoint(): mixed {
        return $this->singleSignoutEndpoint;
    }

    /**
     * @param mixed|string $requirements
     *
     * @return Oauth2Flow
     */
    public function setRequirements($requirements) {
        $this->requirements = $requirements;
        return $this;
    }

    /**
     * @return mixed|string
     */
    public function getRequirements() {
        return $this->requirements;
    }

    /**
     * @param bool|mixed $nonInteractive
     *
     * @return Oauth2Flow
     */
    public function setNonInteractive($nonInteractive) {
        $this->nonInteractive = $nonInteractive;
        return $this;
    }

    /**
     * @return bool|mixed
     */
    public function getNonInteractive() {
        return $this->nonInteractive;
    }

    /**
     * @param mixed|null $useidp
     *
     * @return Oauth2Flow
     */
    public function setUseidp(mixed $useidp): Oauth2Flow {
        $this->useidp = $useidp;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getUseidp(): mixed {
        return $this->useidp;
    }

    /**
     * @param null $successUrl
     *
     * @return Oauth2Flow
     */
    public function setSuccessUrl($successUrl) {
        $this->successUrl = $successUrl;
        return $this;
    }

    /**
     * @return null
     */
    public function getSuccessUrl() {
        return $this->successUrl;
    }

    /**
     * @param null $failureUrl
     *
     * @return Oauth2Flow
     */
    public function setFailureUrl($failureUrl) {
        $this->failureUrl = $failureUrl;
        return $this;
    }

    /**
     * @return null
     */
    public function getFailureUrl() {
        return $this->failureUrl;
    }

    protected function stateArray() {
        return [
            'state' => $this->state,
            'toSignup' => $this->toSignup,
            'username' => $this->username,
            'emailId' => $this->emailId,
            'emailCode' => $this->emailCode,
            'language' => $this->language,
            'singleSignoutEndpoint' => $this->singleSignoutEndpoint,
            'requirements' => $this->requirements,
            'nonInteractive' => $this->nonInteractive,
            'useidp' => $this->useidp,
            'successUrl' => $this->successUrl,
            'failureUrl' => $this->failureUrl
        ];
    }

    private static function stateField($stateData, $name, $default) {
        return $stateData && isset($stateData[$name]) ? $stateData[$name] : $default;
    }

    public static function startFlow(AuthServiceConfig $config, AuthCoreIntegration $integration) {
        return new Oauth2Flow($config, $integration);
    }

    public static function returnEndpoint(AuthServiceConfig $config, AuthCoreIntegration $integration, array $params) {
        return new Oauth2Flow($config, $integration, $params);
    }

    public function authorizeUrl($returnUrl) {
        $authorizeRequest = (new AuthorizeRequest($this->config))
            ->setToSignup($this->toSignup)
            ->setNonInteractive($this->nonInteractive)
            ->setUseidp($this->useidp)
            ->setLanguage($this->language)
            ->setEmailCode($this->emailCode)
            ->setEmailId($this->emailId)
            ->setUsername($this->username)
            ->setSingleSignoutEndpoint($this->singleSignoutEndpoint)
            ->setRequirements($this->requirements);
        $this->session->put('state-'.$this->stateId, $this->stateArray());
        return $authorizeRequest->getAuthorizeUrl($returnUrl, $this->failureUrl, $this->stateId);
    }

    public function handle(AuthenticationHandler $handler) {
        if ($this->code) {
            try {
                $tokenResponse = $this->authService->authenticate($this->code);
            } catch (\Exception $e) {
                return $handler->failed($this);
            }
            if ($tokenResponse && $tokenResponse->access_token) {
                $identity = (new IdentityRequest($this->config, $tokenResponse))
                    ->execute();
                if ($identity !== null) {
                    $result = $handler->beforeTokenAvailability($this, $tokenResponse, $identity);
                    if ($result) {
                        $accessToken = $tokenResponse->access_token;
                        $refreshToken = isset($tokenResponse->refresh_token) && $tokenResponse->refresh_token ? $tokenResponse->refresh_token : null;
                        $expiresIn = isset($tokenResponse->expires_in) && $tokenResponse->expires_in ? $tokenResponse->expires_in : null;
                        $scope = isset($tokenResponse->scope) && $tokenResponse->scope ? $tokenResponse->scope : null;
                        if ($expiresIn) {
                            $expiresAt = time() + $expiresIn - 10;
                        } else {
                            $expiresAt = null;
                        }
                        (new AccessTokenManager($this->config, $this->integration))->setAccessToken($accessToken, $refreshToken, $expiresAt, $scope);

                        return $handler->afterTokenAvailability($this, $identity);
                    } else {
                        return $handler->failed($this);
                    }
                } else {
                    return $handler->failed($this);
                }
            } else {
                return $handler->failed($this);
            }
        } else {
            return $handler->failed($this);
        }
    }
}