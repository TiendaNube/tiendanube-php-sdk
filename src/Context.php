<?php

declare(strict_types=1);

namespace Tiendanube;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Tiendanube\Auth\Scopes;
use Tiendanube\Auth\SessionStorage;
use Tiendanube\Clients\HttpClientFactory;
use Tiendanube\Exception\MissingArgumentException;
use Tiendanube\Exception\InvalidArgumentException;
use Tiendanube\Exception\UninitializedContextException;

class Context
{
    /** @var string The Tiendanube/Nuvemshop API key to be used for requests */
    public static $apiKey = null;
    /** @var string The Tiendanube/Nuvemshop Secret API key to be used for requests  */
    public static $apiSecretKey = null;
    /** @var string */
    public static $hostName = null;
    /** @var string */
    public static $userAgentPrefix = null;
    /** @var Scopes App access scopes */
    public static $scopes = [];
    /** @var string */
    public static $hostScheme = null;
    /** @var string */
    public static $apiVersion = null;
    /** @var LoggerInterface|null */
    public static $logger = null;

    /** @var float Maximum delay between retries, in seconds */
    public static $maxNetworkRetryDelay = 2.0;

    /** @var float Maximum delay between retries, in seconds, that will be respected from the Stripe API */
    public static $maxRetryAfter = 60.0;

    /** @var float Initial delay between retries, in seconds */
    public static $initialNetworkRetryDelay = 0.5;

    /** @var HttpClientFactory */
    public static $httpClientFactory;

    /** @var bool Whether client telemetry is enabled. Defaults to true. */
    public static $enableTelemetry = true;

    /** @var bool */
    private static $isInitialized = false;

    public const VERSION = '2.0.0';

    /**
     * Initializes Context object
     *
     * @param string               $apiKey                          App API key
     * @param string               $apiSecretKey                    App API secret
     * @param string|array         $scopes                          App scopes
     * @param string               $hostName                        App host name e.g. www.google.ca. May include scheme
     * @param string               $apiVersion                      App API key, defaults to unstable
     * @param string               $userAgentPrefix                 Prefix for user agent header sent with a request
     * @param LoggerInterface|null $logger                          App logger, so the library can add its own logs to
     *                                                              it
     *
     * @throws \Tiendanube\Exception\MissingArgumentException
     */
    public static function initialize(
        string $apiKey,
        string $apiSecretKey,
        string $hostName,
        string $userAgentPrefix,
        $scopes = [],
        string $apiVersion = \Tiendanube\ApiVersion::LATEST,
        LoggerInterface $logger = null
    ): void {
        $authScopes = new Scopes($scopes);

        // ensure required values given
        $requiredValues = [
            'apiKey' => $apiKey,
            'apiSecretKey' => $apiSecretKey,
            'hostName' => $hostName,
            'userAgentPrefix' => $userAgentPrefix,
        ];
        $missing = array();
        foreach ($requiredValues as $key => $value) {
            if (!strlen($value)) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            $missing = implode(', ', $missing);
            throw new MissingArgumentException(
                "Cannot initialize Tiendanube/Nuvemshop API Library. Missing values for: $missing"
            );
        }

        if (!\Tiendanube\ApiVersion::isValid($apiVersion)) {
            throw new InvalidArgumentException("Invalid API version: $apiVersion");
        }

        if (!preg_match("/http(s)?:\/\//", $hostName)) {
            $hostName = "https://$hostName";
        }

        $parsedUrl = parse_url($hostName);
        if (!is_array($parsedUrl)) {
            throw new InvalidArgumentException("Invalid host: $hostName");
        }

        $host = $parsedUrl["host"] . (array_key_exists("port", $parsedUrl) ? ":{$parsedUrl["port"]}" : "");

        self::$apiKey = $apiKey;
        self::$apiSecretKey = $apiSecretKey;
        self::$scopes = $authScopes;
        self::$hostName = $host;
        self::$hostScheme = $parsedUrl["scheme"];
        self::$httpClientFactory = new HttpClientFactory();
        self::$apiVersion = $apiVersion;
        self::$userAgentPrefix = $userAgentPrefix;
        self::$logger = $logger;

        self::$isInitialized = true;
    }

    /**
     * Throws exception if initialize() has not been called
     *
     * @throws \Tiendanube\Exception\UninitializedContextException
     */
    public static function throwIfUninitialized(): void
    {
        if (!self::$isInitialized) {
            throw new UninitializedContextException(
                'Context has not been properly initialized. ' .
                    'Please call the .initialize() method to set up your app context object.'
            );
        }
    }

    /**
     * Logs a message using the defined callback. If none is set, the message is ignored.
     *
     * @param string $message The message to log
     * @param string $level   One of the \Psr\Log\LogLevel::* consts, defaults to INFO
     *
     * @throws \Tiendanube\Exception\UninitializedContextException
     */
    public static function log(string $message, string $level = LogLevel::INFO): void
    {
        self::throwIfUninitialized();

        if (!self::$logger) {
            return;
        }

        self::$logger->log($level, $message);
    }

    /**
     * @param bool $enableTelemetry Enables client telemetry.
     *
     * Client telemetry enables timing and request metrics to be sent back to Tiendanube/Nuvemshop as an HTTP Header
     * with the current request. This enables Tiendanube/Nuvemshop to do latency and metrics analysis
     * without adding extra overhead (such as extra network calls) on the client.
     */
    public static function setEnableTelemetry(bool $enableTelemetry): void
    {
        self::$enableTelemetry = $enableTelemetry;
    }
}
