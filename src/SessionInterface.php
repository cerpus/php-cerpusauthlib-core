<?php
/**
 * Created by PhpStorm.
 * User: janespen
 * Date: 24.01.18
 * Time: 10:23
 */

namespace Cerpus\AuthCore;


interface SessionInterface {
    public function put($key, $value);
    public function remove($key);
    public function exists($key): bool;
    public function get($key);
}