<?php
/**
 * Created by PhpStorm.
 * User: janespen
 * Date: 24.01.18
 * Time: 10:20
 */

namespace Cerpus\AuthCore;


interface AuthCoreIntegration {
    public function session() : SessionInterface;
}