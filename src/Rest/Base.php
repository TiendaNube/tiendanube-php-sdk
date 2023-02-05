<?php

declare(strict_types=1);

namespace Tiendanube\Rest;

use Tiendanube\Auth\Session;
use Tiendanube\Clients\Rest;
use Tiendanube\Clients\RestResponse;
use ReflectionClass;
use Tiendanube\Context;
use Tiendanube\Exception\RestResourceException;
use Tiendanube\Exception\RestResourceRequestException;

abstract class Base
{
    public static string $apiVersion;
    public static ?array $nextPageQuery = null;
    public static ?array $prevPageQuery = null;

    /** @var Base[] */
    protected static array $hasOne = [];

    /** @var Base[] */
    protected static array $hasMany = [];

    /** @var array[] */
    protected static array $paths = [];

    protected static string $primaryKey = "id";
    protected static ?string $customPrefix = null;

    /** @var string[] */
    protected static array $readOnlyAttributes = [];

    private array $originalState;
    private array $setProps;
    protected Session $session;

    public function __construct(Session $session, array $fromData = null)
    {
        if (Context::$apiVersion !== static::$apiVersion) {
            $contextVersion = Context::$apiVersion;
            $thisVersion = static::$apiVersion;
            throw new RestResourceException(
                "Current Context::\$apiVersion '$contextVersion' does not match resource version '$thisVersion'",
            );
        }

        $this->originalState = [];
        $this->setProps = [];
        $this->session = $session;

        if (!empty($fromData)) {
            self::setInstanceData($this, $fromData);
        }
    }

    public function save($updateObject = false): void
    {
        $data = self::dataDiff($this->toArray(true), $this->originalState);

        $method = !empty($data[static::$primaryKey]) ? "put" : "post";

        $response = self::request($method, $method, $this->session, [], [], $data, $this);

        if ($updateObject) {
            $body = $response->getDecodedBody();

            self::createInstance($body, $this->session, $this);
        }
    }

    public function saveAndUpdate(): void
    {
        $this->save(true);
    }

    public function __get(string $name)
    {
        return array_key_exists($name, $this->setProps) ? $this->setProps[$name] : null;
    }

    public function __set(string $name, $value): void
    {
        $this->setProperty($name, $value);
    }

    public static function getNextPageInfo()
    {
        return static::$nextPageQuery;
    }

    public static function getPreviousPageInfo()
    {
        return static::$prevPageQuery;
    }

    public function toArray($saving = false): array
    {
        $data = [];

        foreach ($this->getProperties() as $prop) {
            if ($saving && in_array($prop, static::$readOnlyAttributes)) {
                continue;
            }

            $includeProp = !empty($this->$prop) || array_key_exists($prop, $this->setProps);
            if (self::isHasManyAttribute($prop)) {
                if ($includeProp) {
                    $data[$prop] = [];
                    /** @var self $assoc */
                    foreach ($this->$prop as $assoc) {
                        array_push($data[$prop], $this->subAttributeToArray($assoc, $saving));
                    }
                }
            } elseif (self::isHasOneAttribute($prop)) {
                if ($includeProp) {
                    $data[$prop] = $this->subAttributeToArray($this->$prop, $saving);
                }
            } elseif ($includeProp) {
                $data[$prop] = $this->$prop;
            }
        }

        return $data;
    }

    protected static function getJsonBodyName(): string
    {
        $className = preg_replace("/^([A-z_0-9]+\\\)*([A-z_]+)/", "$2", static::class);
        return strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", $className));
    }

    protected static function getJsonResponseBodyName(): string
    {
        $className = preg_replace("/^([A-z_0-9]+\\\)*([A-z_]+)/", "$2", static::class);
        return strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", $className));
    }

    /**
     * @param string[]|int[] $ids
     *
     * @return static[]
     */
    protected static function baseFind(Session $session, array $ids = [], array $params = []): array
    {
        $response = self::request("get", "get", $session, $ids, $params);

        static::$nextPageQuery = static::$prevPageQuery = null;
        $pageInfo = $response->getPageInfo();
        if ($pageInfo) {
            static::$nextPageQuery = $pageInfo->hasNextPage() ? $pageInfo->getNextPageQuery() : null;
            static::$prevPageQuery = $pageInfo->hasPreviousPage() ? $pageInfo->getPreviousPageQuery() : null;
        }

        return static::createInstancesFromResponse($response, $session);
    }

    /**
     * @param static $entity
     */
    protected static function request(
        string $httpMethod,
        string $operation,
        Session $session,
        array $ids = [],
        array $params = [],
        array $body = [],
        self $entity = null
    ): RestResponse {
        $path = static::getPath($httpMethod, $operation, $ids, $entity);

        $client = new Rest($session->getStoreId(), $session->getAccessToken());

        $params = array_filter($params);
        switch ($httpMethod) {
            case "get":
                $response = $client->get($path, [], $params);
                break;
            case "post":
                $response = $client->post($path, $body, [], $params);
                break;
            case "put":
                $response = $client->put($path, $body, [], $params);
                break;
            case "patch":
                $response = $client->patch($path, [], $params);
                break;
            case "delete":
                $response = $client->delete($path, [], $params);
                break;
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode >= 300) {
            $message = "REST request failed";

            $body = $response->getDecodedBody();
            if (!empty($body["errors"])) {
                $bodyErrors = json_encode($body["errors"]);
                $message .= ": {$bodyErrors}";
            }

            throw new RestResourceRequestException($message, $statusCode);
        }

        return $response;
    }

    /**
     * @param string[]|int[] $ids
     */
    private static function getPath(
        string $httpMethod,
        string $operation,
        array $ids,
        self $entity = null
    ): ?string {
        $match = null;

        $maxIds = -1;
        foreach (static::$paths as $path) {
            if ($httpMethod !== $path["http_method"] || $operation !== $path["operation"]) {
                continue;
            }

            $urlIds = $ids;
            foreach ($path["ids"] as $id) {
                if ((!array_key_exists($id, $ids) || $ids[$id] === null) && $entity && $entity->$id) {
                    $urlIds[$id] = $entity->$id;
                }
            }
            $urlIds = array_filter($urlIds);

            if (!empty(array_diff($path["ids"], array_keys($urlIds))) || count($path["ids"]) <= $maxIds) {
                continue;
            }

            $maxIds = count($path["ids"]);
            $match = preg_replace_callback(
                '/(<([^>]+)>)/',
                function ($matches) use ($urlIds) {
                    return $urlIds[$matches[2]];
                },
                $path["path"]
            );
        }

        if (empty($match)) {
            throw new RestResourceException("Could not find a path for request");
        }

        if (static::$customPrefix) {
            $match = preg_replace("/^\/?/", "", static::$customPrefix) . "/$match";
        }
        return $match;
    }

    /**
     * @return static[]
     */
    private static function createInstancesFromResponse(RestResponse $response, Session $session): array
    {
        $objects = [];

        $body = $response->getDecodedBody();

        $className = static::getJsonResponseBodyName();

        if (!empty($body)) {
            if (array_key_exists(0, $body)) {
                foreach ($body as $entry) {
                    array_push($objects, self::createInstance($entry, $session));
                }
            } else {
                array_push($objects, self::createInstance($body, $session));
            }
        }

        return $objects;
    }

    /**
     * @return static
     */
    private static function createInstance(array $data, Session $session, &$instance = null)
    {
        $instance = $instance ?: new static($session);

        if (!empty($data)) {
            self::setInstanceData($instance, $data);
        }

        return $instance;
    }

    private static function isHasManyAttribute(string $property): bool
    {
        return array_key_exists($property, static::$hasMany);
    }

    private static function isHasOneAttribute(string $property): bool
    {
        return array_key_exists($property, static::$hasOne);
    }

    private static function setInstanceData(self &$instance, array $data): void
    {
        $instance->originalState = [];

        foreach ($data as $prop => $value) {
            if (self::isHasManyAttribute($prop)) {
                $attrList = [];
                if (!empty($value)) {
                    foreach ($value as $elementData) {
                        array_push(
                            $attrList,
                            static::$hasMany[$prop]::createInstance($elementData, $instance->session)
                        );
                    }
                }

                $instance->setProperty($prop, $attrList);
            } elseif (self::isHasOneAttribute($prop)) {
                if (!empty($value)) {
                    $instance->setProperty(
                        $prop,
                        static::$hasOne[$prop]::createInstance($value, $instance->session)
                    );
                }
            } else {
                $instance->setProperty($prop, $value);
                $instance->originalState[$prop] = $value;
            }
        }
    }

    private static function dataDiff(array $data1, array $data2): array
    {
        $diff = array();

        foreach ($data1 as $key1 => $value1) {
            if (array_key_exists($key1, $data2)) {
                if (is_array($value1)) {
                    $recursiveDiff = self::dataDiff($value1, $data2[$key1]);
                    if (count($recursiveDiff)) {
                        $diff[$key1] = $recursiveDiff;
                    }
                } else {
                    if ($value1 != $data2[$key1]) {
                        $diff[$key1] = $value1;
                    }
                }
            } else {
                $diff[$key1] = $value1;
            }
        }
        return $diff;
    }

    private function setProperty(string $name, $value): void
    {
        $this->$name = $value;
        $this->setProps[$name] = $value;
    }

    private function getProperties(): array
    {
        $reflection = new ReflectionClass(static::class);
        $docBlock = $reflection->getDocComment();
        $lines = explode("\n", (string)$docBlock);

        $props = [];
        foreach ($lines as $line) {
            preg_match("/[\s\*]+@property\s+[^\s]+\s+\\$(.*)/", $line, $matches);
            if (empty($matches)) {
                continue;
            }

            $props[] = $matches[1];
        }

        return array_unique(array_merge($props, array_keys($this->setProps)));
    }

    /**
     * @param array|null|Base $attribute
     * @return array|null
     */
    private function subAttributeToArray($attribute, bool $saving)
    {
        if (is_array($attribute)) {
            $subAttribute = static::createInstance($attribute, $this->session);
            $retVal = $subAttribute->toArray($saving);
        } elseif (empty($attribute)) {
            $retVal = $attribute;
        } else {
            $retVal = $attribute->toArray($saving);
        }

        return $retVal;
    }
}
