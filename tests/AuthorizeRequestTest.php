<?php

namespace Cerpus\AuthCore;


class AuthorizeRequestTest extends \PHPUnit_Framework_TestCase {
    public function testAuthorizeUrl() {
        $config = (new AuthServiceConfig())
            ->setUrl('http://authtest.local')
            ->setClientId('clientid');
        $authorizeRequest = (new AuthorizeRequest($config))
            ->setEmailId("email-id")
            ->setEmailCode("email-code")
            ->setLanguage("nb")
            ->setSingleSignoutEndpoint("http://testapp.local/slo")
            ->setToSignup(true)
            ->setUsername("username");
        $url = $authorizeRequest->getAuthorizeUrl("http://testapp.local/return", "http:/testapp.local", "teststate");
        $this->assertEquals('http://authtest.local/oauth/authorize?client_id=clientid&redirect_uri=http%3A%2F%2Ftestapp.local%2Freturn&abort=http%3A%2Ftestapp.local&response_type=code&scope=read+profile&state=teststate&requirements=v1&language=nb&signout=http%3A%2F%2Ftestapp.local%2Fslo&type=signup&username=username&email_id=email-id&email_code=email-code', $url);
    }
}