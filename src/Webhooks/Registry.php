<?php

declare(strict_types=1);

namespace Tiendanube\Webhooks;

use Exception;
use Tiendanube\Auth\Session;
use Tiendanube\Clients\HttpHeaders;
use Tiendanube\Context;
use Tiendanube\Exception\InvalidWebhookException;
use Tiendanube\Exception\MissingWebhookHandlerException;
use Tiendanube\Exception\WebhookRegistrationException;
use Tiendanube\Rest\Adminv1\Webhook;

/**
 * Handles registering and processing webhook calls.
 */
final class Registry
{
    public const DELIVERY_METHOD_HTTP = 'http';

    /** @var Handler[] */
    private static $REGISTRY = [];

    /**
     * Sets the handler for the given event. If a handler was previously set for the same event, it will be overridden.
     *
     * @param string  $event   The event to subscribe to. May be a string or a value from the Events class
     * @param Handler $handler The handler for this event
     */
    public static function addHandler(string $event, Handler $handler): void
    {
        self::$REGISTRY[$event] = $handler;
    }

    /**
     * Fetches the handler for the given event. Returns null if no handler was registered.
     *
     * @param string $event The event to check
     *
     * @return Handler|null
     */
    public static function getHandler(string $event): ?Handler
    {
        return self::$REGISTRY[$event] ?? null;
    }

    /**
     * Registers a new webhook for this app with Tiendanube/Nuvemshop.
     *
     * @param string        $path           The URL path for the callback.
     * @param string        $event          The event to subscribe to. May be a string or a value from the Events class
     * @param string        $storeId        The store id to use for requests
     * @param string        $accessToken    The access token to use for requests
     *
     * @return \Tiendanube\Webhooks\RegisterResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Tiendanube\Exception\InvalidArgumentException
     * @throws \Tiendanube\Exception\UninitializedContextException
     * @throws \Tiendanube\Exception\WebhookRegistrationException
     */
    public static function register(
        string $path,
        string $event,
        string $storeId,
        string $accessToken
    ): RegisterResponse {
        $callbackAddress = 'https://' . Context::$hostName . '/' . ltrim($path, '/');
        ;

        $session = new Session($storeId, $accessToken);

        list($webhookId, $mustRegister) = self::isWebhookRegistrationNeeded(
            $session,
            $event,
            $callbackAddress
        );

        $registered = true;
        $body = null;
        if ($mustRegister) {
            $body = self::sendRegisterRequest(
                $session,
                $event,
                $callbackAddress,
                $webhookId
            );
            $registered = ! is_empty($body);
        }

        return new RegisterResponse($registered, $body);
    }

    /**
     * Processes a triggered webhook, calling the appropriate handler.
     *
     * @param array  $rawHeaders The raw HTTP headers for the request
     * @param string $rawBody    The raw body of the HTTP request
     *
     * @return ProcessResponse
     *
     * @throws \Tiendanube\Exception\InvalidWebhookException
     * @throws \Tiendanube\Exception\MissingWebhookHandlerException
     */
    public static function process(array $rawHeaders, string $rawBody): ProcessResponse
    {
        if (empty($rawBody)) {
            throw new InvalidWebhookException("No body was received when processing webhook");
        }

        $headers = self::parseProcessHeaders($rawHeaders);

        $hmac = $headers->get(HttpHeaders::X_TIENDANUBE_HMAC);

        self::validateProcessHmac($rawBody, $hmac);

        $body = json_decode($rawBody, true);

        $store_id = $body['store_id'];
        $event = $body['event'];
        $handler = self::getHandler($event);
        if (!$handler) {
            throw new MissingWebhookHandlerException("No handler was registered for event '$event'");
        }

        try {
            $handler->handle($event, $store_id, $body);
            $response = new ProcessResponse(true);
        } catch (Exception $error) {
            $response = new ProcessResponse(false, $error->getMessage());
        }

        return $response;
    }

    /**
     * Checks if Tiendanube/Nuvemshop already has a callback set for this webhook via API, and checks if we need to
     * update our subscription if one exists.
     *
     * @param \Tiendanube\Auth\Session         $session
     * @param string                           $event
     * @param string                           $callbackAddress
     *
     * @return array
     *
     * @throws \Tiendanube\Exception\HttpRequestException
     * @throws \Tiendanube\Exception\MissingArgumentException
     * @throws \Tiendanube\Exception\WebhookRegistrationException
     */
    private static function isWebhookRegistrationNeeded(
        Session $session,
        string $event,
        string $callbackAddress
    ): array {
        try {
            $webhooks = Webhook::all($session, [], [
                'url' => $callbackAddress,
                'event' => $event,
                'page' => 1,
                'per_page' => 1,
            ]);
        } catch (\Tiendanube\Exception\RestResourceRequestException $e) {
            throw new WebhookRegistrationException('Failed to check if webhook was already registered');
        }

        $webhookId = null;
        $mustRegister = true;
        if (! empty($webhooks)) {
            $webhookId = $webhooks[0]->id;
            $mustRegister = false;
        }

        return [$webhookId, $mustRegister];
    }

    /**
     * Creates or updates a webhook subscription in Tiendanube/Nuvemshop.
     *
     * @param \Tiendanube\Auth\Session         $session
     * @param string                           $event
     * @param string                           $callbackAddress
     * @param string|null                      $webhookId
     *
     * @return array
     *
     * @throws \Tiendanube\Exception\HttpRequestException
     * @throws \Tiendanube\Exception\MissingArgumentException
     * @throws \Tiendanube\Exception\WebhookRegistrationException
     */
    private static function sendRegisterRequest(
        Session $session,
        string $event,
        string $callbackAddress,
        ?string $webhookId
    ): array {
        try {
            $webhook = new Webhook($session, [
               'url' => $callbackAddress,
               'event' => $event,
            ]);
            $webhook->save(true);
        } catch (\Tiendanube\Exception\RestResourceRequestException $e) {
            return [];
        }

        return $webhook->toArray();
    }

    /**
     * Checks if all the necessary headers are given for this to be a valid webhook, returning the parsed headers.
     *
     * @param array $rawHeaders The raw HTTP headers from the request
     *
     * @return HttpHeaders The parsed headers
     *
     * @throws \Tiendanube\Exception\InvalidWebhookException
     */
    private static function parseProcessHeaders(array $rawHeaders): HttpHeaders
    {
        $headers = new HttpHeaders($rawHeaders);

        $missingHeaders = $headers->diff(
            [HttpHeaders::X_TIENDANUBE_HMAC],
            false,
        );

        if (!empty($missingHeaders)) {
            $missingHeaders = implode(', ', $missingHeaders);
            throw new InvalidWebhookException(
                "Missing one or more of the required HTTP headers to process webhooks: [$missingHeaders]"
            );
        }

        return $headers;
    }

    /**
     * Checks if the given HMAC hash is valid.
     *
     * @param string $rawBody The HTTP request body
     * @param string $hmac    The HMAC from the HTTP headers
     *
     * @throws \Tiendanube\Exception\InvalidWebhookException
     */
    private static function validateProcessHmac(string $rawBody, string $hmac): void
    {
        if ($hmac !== base64_encode(hash_hmac('sha256', $rawBody, Context::$apiSecretKey, true))) {
            throw new InvalidWebhookException("Could not validate webhook HMAC");
        }
    }
}
