<?php

declare(strict_types=1);

namespace Tiendanube\Clients;

use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;
use Psr\Log\LogLevel;
use Stripe\RequestTelemetry;
use Stripe\Stripe;
use Tiendanube\Context;

class Http
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_DELETE = 'DELETE';

    public const METHOD_PATCH = 'PATCH';

    public const DATA_TYPE_JSON = 'application/json';
    private const RETRIABLE_STATUS_CODES = [429, 500];
    private const DEPRECATION_ALERT_SECONDS = 60;

    private const DOMAIN = 'api.tiendanube.com';

    /** @var string */
    private $storeId;
    /** @var string */
    private $basePath;

    private static $requestTelemetry;

    public function __construct(string $storeId)
    {
        $apiVersion = Context::$apiVersion;
        $this->storeId = $storeId;
        $this->basePath = "/{$apiVersion}/{$storeId}";
    }

    /**
     * Makes a GET request to this client's domain.
     *
     * @param string   $path    The URL path to request
     * @param array    $headers Any extra headers to send along with the request
     * @param array    $query   Parameters on a query to be added to the URL
     * @param int|null $tries   How many times to attempt the request
     *
     * @return HttpResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Tiendanube\Exception\UninitializedContextException
     */
    public function get(string $path, array $headers = [], array $query = [], ?int $tries = null): HttpResponse
    {
        return $this->request($path, self::METHOD_GET, null, $headers, $query, $tries);
    }

    /**
     * Makes a POST request to this client's domain.
     *
     * @param string       $path     The URL path to request
     * @param string|array $body     The body of the request
     * @param array        $headers  Any extra headers to send along with the request
     * @param array        $query    Parameters on a query to be added to the URL
     * @param int|null     $tries    How many times to attempt the request
     * @param string       $dataType The data type to expect in the response
     *
     * @return HttpResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Tiendanube\Exception\UninitializedContextException
     */
    public function post(
        string $path,
        $body,
        array $headers = [],
        array $query = [],
        ?int $tries = null,
        string $dataType = self::DATA_TYPE_JSON
    ): HttpResponse {
        return $this->request($path, self::METHOD_POST, $body, $headers, $query, $tries, $dataType);
    }

    /**
     * Makes a PUT request to this client's domain.
     *
     * @param string       $path     The URL path to request
     * @param string|array $body     The body of the request
     * @param array        $headers  Any extra headers to send along with the request
     * @param array        $query    Parameters on a query to be added to the URL
     * @param int|null     $tries    How many times to attempt the request
     * @param string       $dataType The data type to expect in the response
     *
     * @return HttpResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Tiendanube\Exception\UninitializedContextException
     */
    public function put(
        string $path,
        $body,
        array $headers = [],
        array $query = [],
        ?int $tries = null,
        string $dataType = self::DATA_TYPE_JSON
    ): HttpResponse {
        return $this->request($path, self::METHOD_PUT, $body, $headers, $query, $tries, $dataType);
    }

    /**
     * Makes a DELETE request to this client's domain.
     *
     * @param string   $path    The URL path to request
     * @param array    $headers Any extra headers to send along with the request
     * @param array    $query   Parameters on a query to be added to the URL
     * @param int|null $tries   How many times to attempt the request
     *
     * @return HttpResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Tiendanube\Exception\UninitializedContextException
     */
    public function delete(string $path, array $headers = [], array $query = [], ?int $tries = null): HttpResponse
    {
        return $this->request(
            $path,
            self::METHOD_DELETE,
            null,
            $headers,
            $query,
            $tries,
        );
    }

    /**
     * Makes a PATCH request to this client's domain.
     *
     * @param string   $path    The URL path to request
     * @param array    $headers Any extra headers to send along with the request
     * @param array    $query   Parameters on a query to be added to the URL
     * @param int|null $tries   How many times to attempt the request
     *
     * @return HttpResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Tiendanube\Exception\UninitializedContextException
     */
    public function patch(string $path, array $headers = [], array $query = [], ?int $tries = null): HttpResponse
    {
        return $this->request(
            $path,
            self::METHOD_PATCH,
            null,
            $headers,
            $query,
            $tries,
        );
    }

    /**
     * Internally handles the logic for making requests.
     *
     * @param string            $path     The path to query
     * @param string            $method   The method to use
     * @param string|array|null $body     The request body to send
     * @param array             $headers  Any extra headers to send along with the request
     * @param array             $query    Parameters on a query to be added to the URL
     * @param int|null          $tries    How many times to attempt the request
     * @param string            $dataType The data type of the request
     *
     * @return HttpResponse
     * @throws \Psr\Http\Client\ClientExceptionInterface
     * @throws \Tiendanube\Exception\UninitializedContextException
     */
    protected function request(
        string $path,
        string $method,
        $body = null,
        array $headers = [],
        array $query = [],
        ?int $tries = null,
        string $dataType = self::DATA_TYPE_JSON
    ) {
        $maxTries = $tries ?? 1;

        $version = Context::VERSION;
        $userAgentParts = ["Tiendanube Admin API Library for PHP v$version"];

        if (Context::$userAgentPrefix) {
            array_unshift($userAgentParts, Context::$userAgentPrefix);
        }

        if (isset($headers[HttpHeaders::USER_AGENT])) {
            array_unshift($userAgentParts, $headers[HttpHeaders::USER_AGENT]);
            unset($headers[HttpHeaders::USER_AGENT]);
        }

        if (Context::$enableTelemetry && ! is_null(self::$requestTelemetry)) {
            $headers[HttpHeaders::X_TIENDANUBE_CLIENT_TELEMETRY] = self::telemetryJson(self::$requestTelemetry);
        }

        $client = Context::$httpClientFactory->client();

        $query = preg_replace("/%5B[0-9]+%5D/", "%5B%5D", http_build_query($query));

        /*
         * This is done to check OAuth URI as it uses a different domain.
         * FALSE means we are hitting the API as usual
         * TRUE means someone sent a request with a full URI
         */
        if (filter_var($path, FILTER_VALIDATE_URL) === false) {
            $url = (new Uri())
                ->withHost(self::DOMAIN)
                ->withPath($this->getRequestPath($path));
        } else {
            $url = (new Uri($path));
        }
        $url = $url->withScheme('https')
            ->withQuery($query);

        $request = new Request($method, $url, $headers);
        $request = $request->withHeader(HttpHeaders::USER_AGENT, implode(' | ', $userAgentParts));

        if ($body) {
            if (is_string($body)) {
                $bodyString = $body;
            } else {
                $bodyString = json_encode($body);
            }

            $stream = Utils::streamFor($bodyString);
            $request = $request
                ->withBody($stream)
                ->withHeader(HttpHeaders::CONTENT_TYPE, $dataType)
                ->withHeader(HttpHeaders::CONTENT_LENGTH, mb_strlen($bodyString));
        }

        $currentTries = 0;
        do {
            $currentTries++;

            $requestStartMs = $this->currentTimeMillis();

            $response = HttpResponse::fromResponse($client->sendRequest($request));

            $responseHeaders = $response->getHeaders();

            if (
                $response->hasHeader(HttpHeaders::X_REQUEST_ID)
                && '' !== $response->getHeaderLine(HttpHeaders::X_REQUEST_ID)
            ) {
                self::$requestTelemetry = new RequestTelemetry(
                    $responseHeaders[HttpHeaders::X_REQUEST_ID],
                    $this->currentTimeMillis() - $requestStartMs
                );
            }

            if (in_array($response->getStatusCode(), self::RETRIABLE_STATUS_CODES)) {
                $retryAfter = $response->hasHeader(HttpHeaders::RETRY_AFTER)
                    ? $response->getHeaderLine(HttpHeaders::RETRY_AFTER)
                    : Context::$initialNetworkRetryDelay;

                usleep((int)($retryAfter * 1000000));
            } else {
                break;
            }
        } while ($currentTries < $maxTries);


        if ($response->hasHeader(HttpHeaders::X_TIENDANUBE_API_DEPRECATED_REASON)) {
            $this->logApiDeprecation(
                $url->__toString(),
                $response->getHeaderLine(HttpHeaders::X_TIENDANUBE_API_DEPRECATED_REASON)
            );
        }

        return $response;
    }

    protected function getRequestPath(string $path): string
    {
        if (strpos($path, '/') !== 0) {
            $path = "/$path";
        }

        return $this->basePath . $path;
    }

    /**
     * Logs an API deprecation for the given URL to the app's logged, if one was given.
     *
     * @param string $url    The URL that used a deprecated resource
     * @param string $reason The deprecation reason
     * @throws \Tiendanube\Exception\UninitializedContextException
     */
    private function logApiDeprecation(string $url, string $reason): void
    {
        $warningFilePath = $this->getApiDeprecationTimestampFilePath();

        $lastWarning = null;
        if (file_exists($warningFilePath)) {
            $lastWarning = (int)(file_get_contents($warningFilePath));
        }

        if (time() - $lastWarning < self::DEPRECATION_ALERT_SECONDS) {
            return;
        }

        file_put_contents($warningFilePath, time());

        $e = new Exception();
        $stackTrace = str_replace("\n", "\n    ", $e->getTraceAsString());

        // For some reason, code coverage doesn't like the heredoc string, but there's no branching here so if the lines
        // above are hit, so is this.
        // @codeCoverageIgnoreStart
        Context::log(
            <<<NOTICE
            API Deprecation notice:
                URL: $url
                Reason: $reason
            Stack trace:
                $stackTrace
            NOTICE,
            LogLevel::WARNING,
        );
        // @codeCoverageIgnoreEnd
    }

    /**
     * Fetches the path to the file holding the timestamp of the last API deprecation warning we logged.
     *
     * @codeCoverageIgnore This is mocked in tests so we don't use real files
     */
    public function getApiDeprecationTimestampFilePath(): string
    {
        return dirname(__DIR__) . '/.last_api_deprecation_warning';
    }

    /**
     * @static
     *
     * Resets any stateful telemetry data
     */
    public static function resetTelemetry()
    {
        self::$requestTelemetry = null;
    }

    /**
     * Creates a telemetry json blob for use in 'X-Stripe-Client-Telemetry' headers.
     *
     * @static
     *
     * @param RequestTelemetry $requestTelemetry
     *
     * @return string
     */
    private static function telemetryJson($requestTelemetry)
    {
        $payload = [
            'last_request_metrics' => [
                'request_id' => $requestTelemetry->requestId,
                'request_duration_ms' => $requestTelemetry->requestDuration,
            ],
        ];

        $result = \json_encode($payload);
        if (false !== $result) {
            return $result;
        }
        Context::log('Serializing telemetry payload failed!', LogLevel::ERROR);

        return '{}';
    }

    /**
     * Returns UNIX timestamp in milliseconds.
     *
     * @return int current time in millis
     */
    private function currentTimeMillis()
    {
        return (int) \round(\microtime(true) * 1000);
    }
}
