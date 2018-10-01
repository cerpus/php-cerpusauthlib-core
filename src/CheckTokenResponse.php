<?php

namespace Cerpus\AuthCore;


class CheckTokenResponse {

    /**
     * @return string
     */
    public function getGrantType(): string {
        return $this->grantType;
    }

    /**
     * @param string $grantType
     */
    public function setGrantType(string $grantType) {
        $this->grantType = $grantType;
    }

    /**
     * @return array
     */
    public function getScope(): array {
        return $this->scope;
    }

    /**
     * @param array $scope
     */
    public function setScope(array $scope) {
        $this->scope = $scope;
    }

    /**
     * @return bool
     */
    public function isActive(): bool {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active) {
        $this->active = $active;
    }

    /**
     * @return int
     */
    public function getExpiry(): int {
        return $this->expiry;
    }

    /**
     * @param int $expiry
     */
    public function setExpiry(int $expiry) {
        $this->expiry = $expiry;
    }

    /**
     * @return array
     */
    public function getAuthorities(): array {
        return $this->authorities;
    }

    /**
     * @param array $authorities
     */
    public function setAuthorities(array $authorities) {
        $this->authorities = $authorities;
    }

    /**
     * @return string
     */
    public function getClientId(): string {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId) {
        $this->clientId = $clientId;
    }
    /** @var string */
    private $grantType;
    /** @var array */
    private $scope;
    /** @var boolean */
    private $active;
    /** @var integer */
    private $expiry;
    /** @var array */
    private $authorities;
    /** @var string */
    private $clientId;
}