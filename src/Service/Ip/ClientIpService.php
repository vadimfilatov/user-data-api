<?php

declare(strict_types=1);

namespace App\Service\Ip;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ClientIpService
{
    public function getClientIp(Request $request): string
    {
        $ip = $request->getClientIp() ?? (string) $request->server->get('REMOTE_ADDR', '');

        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw new BadRequestHttpException('Unable to get client IP address.');
        }

        return $ip;
    }
}
