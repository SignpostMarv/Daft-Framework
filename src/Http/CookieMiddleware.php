<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftFramework\Http;

use SignpostMarv\DaftFramework\Framework;
use SignpostMarv\DaftRouter\DaftMiddleware;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CookieMiddleware implements DaftMiddleware
{
    public static function DaftRouterMiddlewareHandler(
        Request $request,
        ? Response $response
    ) : ? Response {
        if ( ! is_null($response)) {
            $framework = Framework::ObtainFrameworkForRequest($request);

            $config = $framework->ObtainConfig();

            if ( ! isset($config[self::class])) {
                return $response;
            }

            $config = $config[self::class];

            $configSecure = (bool) ($config['secure'] ?? null);
            $configHttpOnly = (bool) ($config['httpOnly'] ?? null);
            $configSameSite = is_string($config['sameSite'] ?? null) ? $config['sameSite'] : null;

            foreach ($response->headers->getCookies() as $cookie) {
                $updateSecure = $cookie->isSecure() !== $configSecure;
                $updateHttpOnly = $cookie->isHttpOnly() !== $configHttpOnly;
                $updateSameSite = $cookie->getSameSite() !== $configSameSite;

                if ($updateSecure || $updateHttpOnly || $updateSameSite) {
                    $response->headers->removeCookie(
                        $cookie->getName(),
                        $cookie->getPath(),
                        $cookie->getDomain()
                    );
                    $response->headers->setCookie(new Cookie(
                        $cookie->getName(),
                        $cookie->getValue(),
                        $cookie->getExpiresTime(),
                        $cookie->getPath(),
                        $cookie->getDomain(),
                        $configSecure,
                        $configHttpOnly,
                        $cookie->isRaw(),
                        $configSameSite
                    ));
                }
            }
        }

        return $response;
    }

    public static function DaftRouterRoutePrefixExceptions() : array
    {
        return [];
    }
}