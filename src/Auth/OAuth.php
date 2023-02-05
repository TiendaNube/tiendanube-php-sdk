<?php

namespace Tiendanube\Auth;

use Tiendanube\Auth\Session;
use Tiendanube\Exception\InvalidOAuthException;
use Tiendanube\Exception\SessionStorageException;
use Tiendanube\Exception\HttpRequestException;
use Tiendanube\Clients\Http;
use Tiendanube\Clients\HttpResponse;
use Tiendanube\Context;

/**
 * Provides a simple way to authenticate your app for the API of Tiendanube/Nuvemshop.
 * See https://tiendanube.github.io/api-documentation/authentication for details.
 */
class OAuth
{
    public const ACCESS_TOKEN_POST_PATH = 'https://www.tiendanube.com/apps/authorize/token';

    /**
     * Return the url to login to you app in the www.nuvemshop.com.br domain.
     *
     * @return string
     */
    public static function loginUrlBrazil()
    {
        return self::loginUrl('www.nuvemshop.com.br');
    }

    /**
     * Return the url to login to you app in the www.tiendanube.com domain.
     *
     * @return string
     */
    public static function loginUrlSpLATAM()
    {
        return self::loginUrl('www.tiendanube.com');
    }

    /**
     * @param string $domain
     * @return string
     */
    private static function loginUrl($domain)
    {
        $client_id = Context::$apiKey;
        return "https://{$domain}/apps/{$client_id}/authorize";
    }

    /**
     * Performs the OAuth callback steps, checking the returned parameters and fetching the access token, preparing the
     * session for further usage. If successful, the updated session is returned.
     *
     * @param array         $query             The HTTP request URL query values.
     *
     * @return Session
     * @throws \Tiendanube\Exception\InvalidOAuthException
     * @throws \Tiendanube\Exception\UninitializedContextException
     */
    public static function callback(array $query): Session
    {
        Context::throwIfUninitialized();

        if (!self::isCallbackQueryValid($query)) {
            throw new InvalidOAuthException('Invalid OAuth callback.');
        }

        $response = self::fetchAccessToken($query);

        return new Session($response->getStoreId(), $response->getAccessToken(), $response->getScope());
    }

    /**
     * Checks whether the given query parameters are from a valid callback request.
     *
     * @param array   $query   The URL query parameters
     *
     * @return bool
     */
    private static function isCallbackQueryValid(array $query): bool
    {
        $state = $query['state'] ?? '';
        $code = $query['code'] ?? '';

        return (
            ($code) &&
            ($state)
        );
    }

    /**
     * Obtain a permanent access token from an authorization code.
     *
     * @param string $code Authorization code retrieved from the redirect URI.
     * @throws OAuthException
     */
    private static function fetchAccessToken($code)
    {
        $post = [
            'client_id' => Context::$apiKey,
            'client_secret' => Context::$apiSecretKey,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];

        $client = new Http('dummy_store_id');
        $response = self::requestAccessToken($client, $post);
        if ($response->getStatusCode() !== 200) {
            throw new HttpRequestException("Failed to get access token: {$response->getDecodedBody()}");
        }

        $body = $response->getDecodedBody();

        if (isset($body['error'])) {
            throw new InvalidOAuthException("[{$body['error']}] {$body['error_description']}");
        }

        return self::buildAccessTokenResponse($body);
    }

    /**
     * Builds an offline access token response object
     *
     * @param array $body The HTTP response body
     */
    private static function buildAccessTokenResponse(array $body): AccessTokenResponse
    {
        return new AccessTokenResponse($body['user_id'], $body['access_token'], $body['scope']);
    }

    /**
     * Fires the actual request for the access token. This was isolated so it can be stubbed in unit tests.
     *
     * @param Http  $client
     * @param array $post The POST payload
     *
     * @return \Tiendanube\Clients\HttpResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Tiendanube\Exception\UninitializedContextException
     * @codeCoverageIgnore
     */
    public static function requestAccessToken(Http $client, array $post): HttpResponse
    {
        return $client->post(self::ACCESS_TOKEN_POST_PATH, $post);
    }
}
