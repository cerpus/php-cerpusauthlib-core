<?php

namespace Cerpus\AuthCore;


interface AuthenticationHandler {
    /**
     * Called before the token is put into the TokenManager. Return to proceed with adding the token to the TokenManager and then calling afterTokenAvailability.
     *
     * @param \Cerpus\AuthCore\TokenResponse $tokenResponse
     * @param \Cerpus\AuthCore\IdentityResponse $identityResponse
     *
     * @return bool Return true to proceed with login.
     */
    public function beforeTokenAvailability(Oauth2Flow $flow, TokenResponse $tokenResponse, IdentityResponse $identityResponse): bool;

    /**
     * Called after putting the token into the TokenManager
     *
     * @param \Cerpus\AuthCore\Oauth2Flow $flow
     * @param \Cerpus\AuthCore\IdentityResponse $identityResponse
     */
    public function afterTokenAvailability(Oauth2Flow $flow, IdentityResponse $identityResponse);

    /**
     * Authorization failed.
     *
     * @param \Cerpus\AuthCore\Oauth2Flow $flow
     * @param mixed|null $error
     *
     * @return mixed
     */
    public function failed(Oauth2Flow $flow, $error=null);
}