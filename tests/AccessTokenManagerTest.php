<?php

namespace Cerpus\AuthCore;


use Cerpus\AuthCore\utils\AuthCoreIntegrationInMemoryImpl;

class AccessTokenManagerTest extends \PHPUnit_Framework_TestCase {
    public function testSimpleGetAccessTokenNull() {
        $integration = new AuthCoreIntegrationInMemoryImpl();
        $config = new AuthServiceConfig();
        $tokenManager = new AccessTokenManager($config, $integration);
        $this->assertNull($tokenManager->getAccessToken());
    }
    public function testSimpleGetAccessToken() {
        $integration = new AuthCoreIntegrationInMemoryImpl();
        $config = new AuthServiceConfig();
        $tokenManager = new AccessTokenManager($config, $integration);
        $integration->session()->put("CERPUS_AUTH_ACCESS_TOKEN", "accesstoken");
        $this->assertEquals("accesstoken", $tokenManager->getAccessToken());
    }
    public function testSetOnlyAccessToken() {
        $integration = new AuthCoreIntegrationInMemoryImpl();
        $session = $integration->session();
        $config = new AuthServiceConfig();
        $tokenManager = new AccessTokenManager($config, $integration);
        $tokenManager->setAccessToken("accesstoken");
        $this->assertEquals("accesstoken", $session->get("CERPUS_AUTH_ACCESS_TOKEN"));
        $this->assertNull($session->get("CERPUS_AUTH_REFRESH_TOKEN"));
        $this->assertNull($session->get("CERPUS_AUTH_EXPIRES_AT"));
        $this->assertNull($session->get("CERPUS_AUTH_SCOPE"));
    }
    public function testSetAccessTokenWithRefresh() {
        $integration = new AuthCoreIntegrationInMemoryImpl();
        $session = $integration->session();
        $config = new AuthServiceConfig();
        $tokenManager = new AccessTokenManager($config, $integration);
        $tokenManager->setAccessToken("accesstoken", "refreshtoken", 2000000000, "read openid");
        $this->assertEquals("accesstoken", $session->get("CERPUS_AUTH_ACCESS_TOKEN"));
        $this->assertEquals("refreshtoken", $session->get("CERPUS_AUTH_REFRESH_TOKEN"));
        $this->assertEquals(2000000000, $session->get("CERPUS_AUTH_EXPIRES_AT"));
        $this->assertEquals("read openid", $session->get("CERPUS_AUTH_SCOPE"));
    }
}