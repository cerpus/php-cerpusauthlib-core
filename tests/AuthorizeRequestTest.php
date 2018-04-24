<?php

namespace Cerpus\AuthCore;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class MockedAuthorizeRequest extends AuthorizeRequest {
    private $httpClient;

    public function __construct(Client $httpClient, \Cerpus\AuthCore\AuthServiceConfig $config) {
        parent::__construct($config);
        $this->httpClient = $httpClient;
    }

    protected function httpClient(): Client {
        return $this->httpClient;
    }
}

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
        $this->assertEquals('http://authtest.local/oauth/authorize?client_id=clientid&redirect_uri=http%3A%2F%2Ftestapp.local%2Freturn&response_type=code&scope=read+profile&state=teststate&requirements=v1&abort=http%3A%2Ftestapp.local&language=nb&signout=http%3A%2F%2Ftestapp.local%2Fslo&type=signup&username=username&email_id=email-id&email_code=email-code', $url);
    }
    public function testAuthorizeUrlUseidp() {
        $config = (new AuthServiceConfig())
            ->setUrl('http://authtest.local')
            ->setClientId('clientid');
        $authorizeRequest = (new AuthorizeRequest($config))
            ->setLanguage("nb")
            ->setSingleSignoutEndpoint("http://testapp.local/slo")
            ->setUseidp('google');
        $url = $authorizeRequest->getAuthorizeUrl("http://testapp.local/return", "http:/testapp.local", "teststate");
        $this->assertEquals('http://authtest.local/oauth/authorize?client_id=clientid&redirect_uri=http%3A%2F%2Ftestapp.local%2Freturn&response_type=code&scope=read+profile&state=teststate&requirements=v1&abort=http%3A%2Ftestapp.local&language=nb&signout=http%3A%2F%2Ftestapp.local%2Fslo&useidp=google', $url);
    }
    public function testAuthorizeUrlNonInteractive() {
        $config = (new AuthServiceConfig())
            ->setUrl('http://authtest.local')
            ->setClientId('clientid');
        $authorizeRequest = (new AuthorizeRequest($config))
            ->setSingleSignoutEndpoint("http://testapp.local/slo")
            ->setNonInteractive(true);
        $url = $authorizeRequest->getAuthorizeUrl("http://testapp.local/return", null, "teststate");
        $this->assertEquals('http://authtest.local/oauth/authorize?client_id=clientid&redirect_uri=http%3A%2F%2Ftestapp.local%2Freturn&response_type=code&scope=read+profile&state=teststate&requirements=v1&signout=http%3A%2F%2Ftestapp.local%2Fslo&type=non_interactive', $url);
    }

    /**
     * @throws \Exception
     */
    public function testAuthorizeApi() {
        $json = "{" .
            "\"termsAndConditions\":\"terms-and-conditions\"," .
            "\"theme\":\"theme\"," .
            "\"redirectLogin\":\"redirect-login\"," .
            "\"authorizeUri\":\"authorize-uri\"," .
            "\"codeAuthorizeUri\":\"code-authorize-uri\"," .
            "\"tokenAuthorizeUri\":\"token-authorize-uri\"," .
            "\"redirectUri\":\"redirect-uri\"," .
            "\"idpInfo\":[{" .
                "\"id\":\"idp-id\"," .
                "\"name\":\"idp-name\"," .
                "\"uri\":\"idp-uri\"," .
                "\"popupTokenUri\":\"popup-token-uri\"" .
            "}]," .
            "\"emailJwt\":\"email-jwt\"," .
            "\"email\":\"email\"," .
            "\"clientId\":\"client-id\"," .
            "\"abortUri\":\"abort-uri\"," .
            "\"language\":\"language\"," .
            "\"languageAlpha2\":\"language-alpha2\"," .
            "\"logoutUri\":\"logout-uri\"," .
            "\"authorizeType\":\"authorize-type\"," .
            "\"username\":\"username\"" .
        "}";
        $mock = new MockHandler([
            new Response(200, ['Content-Type', 'application/json'], $json),
            new RequestException("Error Communicating with Server", new Request('GET', 'test'))
        ]);

        $container = [];
        $history = Middleware::history($container);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        $client = new Client(['handler' => $stack]);

        $config = (new AuthServiceConfig())
            ->setUrl('http://authtest.local')
            ->setClientId('client-id');
        $authorizeRequest = (new MockedAuthorizeRequest($client, $config))
            ->setEmailId('email-id')
            ->setEmailCode('email-code')
            ->setLanguage('language')
            ->setUsername('username');

        $authorizeData = $authorizeRequest->executeApi('redirect-uri', 'abort-uri', 'state');

        $this->assertEquals("terms-and-conditions", $authorizeData->termsAndConditions);
        $this->assertEquals("theme", $authorizeData->theme);
        $this->assertEquals("redirect-login", $authorizeData->redirectLogin);
        $this->assertEquals("authorize-uri", $authorizeData->authorizeUri);
        $this->assertEquals("code-authorize-uri", $authorizeData->codeAuthorizeUri);
        $this->assertEquals("token-authorize-uri", $authorizeData->tokenAuthorizeUri);
        $this->assertEquals("redirect-uri", $authorizeData->redirectUri);
        $this->assertEquals("email-jwt", $authorizeData->emailJwt);
        $this->assertEquals("email", $authorizeData->email);
        $this->assertEquals("client-id", $authorizeData->clientId);
        $this->assertEquals("abort-uri", $authorizeData->abortUri);
        $this->assertEquals("language", $authorizeData->language);
        $this->assertEquals("language-alpha2", $authorizeData->languageAlpha2);
        $this->assertEquals("logout-uri", $authorizeData->logoutUri);
        $this->assertEquals("authorize-type", $authorizeData->authorizeType);
        $this->assertEquals("username", $authorizeData->username);

        $this->assertEquals(1, count($authorizeData->idpInfo));
        /** @var \Cerpus\AuthCore\AuthorizeDataIdpInfo */
        $idpInfo = $authorizeData->idpInfo[0];
        if (!($idpInfo instanceof AuthorizeDataIdpInfo)) {
            throw new \Exception("Incorrect data type, required AuthorizeDataIdpInfo");
        }
        $this->assertEquals("idp-id", $idpInfo->id);
        $this->assertEquals("idp-name", $idpInfo->name);
        $this->assertEquals("idp-uri", $idpInfo->uri);
        $this->assertEquals("popup-token-uri", $idpInfo->popupTokenUri);
    }
}