<?php
/**
 * Created by PhpStorm.
 * User: janespen
 * Date: 24.01.18
 * Time: 11:39
 */

namespace Cerpus\AuthCore\utils;


use Cerpus\AuthCore\AuthCoreIntegration;
use Cerpus\AuthCore\SessionInterface;

class AuthCoreIntegrationInMemoryImpl implements AuthCoreIntegration {
    private $session = null;

    public function session(): SessionInterface {
        if ($this->session === null) {
            $this->session = new SessionInterfaceInMemoryImpl();
        }
        return $this->session;
    }
}