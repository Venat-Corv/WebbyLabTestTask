<?php
declare(strict_types=1);

namespace App\Service;

use App\Enum\CSRFRequestMethodsEnum;

class Middleware {
    public static function handle($request, $method, $csrf, $next) {
        $isValidCSRF = true;
        if($request === '/logout') {
            $isValidCSRF = false;
        }

        if(CSRFRequestMethodsEnum::isNeedCSRF($method) && isset($_SESSION['csrf'])) {
            if ($_SESSION['csrf']['death_time'] === time()) {
                unset($_SESSION['csrf']);
                session_unset();
            } elseif ($_SESSION['csrf']['key'] !== $csrf) {
                $isValidCSRF = false;
            }
        } elseif (empty($_SESSION) && $request !== '/sign/in' && $request !== '/sign/up') {
            $isValidCSRF = false;
        }
        $request = self::modifyRequest($request, $isValidCSRF);

        $response = $next($request);

        return $response;
    }

    private static function modifyRequest(string $request, bool $isValidCSRF) {
        return ($isValidCSRF) ? current(explode('?', $request)): "/access-denied";
    }
}