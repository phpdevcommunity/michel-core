<?php

namespace PhpDevCommunity\Michel\Core\Helper;

use Psr\Http\Message\ServerRequestInterface;

final class IpHelper
{
    public static function getIpFromRequest(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        if (array_key_exists('HTTP_CLIENT_IP', $serverParams)) {
            return $serverParams['HTTP_CLIENT_IP'];
        }

        if (array_key_exists('HTTP_X_FORWARDED_FOR', $serverParams)) {
            return $serverParams['HTTP_X_FORWARDED_FOR'];
        }

        if (array_key_exists('REMOTE_ADDR', $serverParams)) {
            return $serverParams['REMOTE_ADDR'];
        }

        return '127.0.0.1';
    }

}
