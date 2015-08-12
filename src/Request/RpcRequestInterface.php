<?php

namespace Moriony\RpcServer\Request;

use Symfony\Component\HttpFoundation\Request;

interface RpcRequestInterface
{
    /** @return Request */
    public function getHttpRequest();
    public function get($name, $default = null);
    public function getMethod();
    public function getParams();
    public function setExtraData($name, $value);
    public function getExtraData($name);
    public function hasExtraData($name);
}