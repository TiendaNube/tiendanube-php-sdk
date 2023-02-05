<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class Location extends Base
{
    protected array $address;
    protected bool $allows_pickup;
    protected string $created_at;
    protected int $id;

    protected bool $is_default;
    protected ?string $name;
    protected int $priority;
    protected string $updated_at;

    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => [], "path" => "locations"],
        ["http_method" => "post", "operation" => "post", "ids" => [], "path" => "locations"],
        ["http_method" => "get", "operation" => "get", "ids" => ["id"], "path" => "locations/<id>"],
        ["http_method" => "put", "operation" => "put", "ids" => ["id"], "path" => "locations/<id>"],
        ["http_method" => "delete", "operation" => "delete", "ids" => ["id"], "path" => "locations/<id>"],
        ["http_method" => "get", "operation" => "get", "ids" => ["id"], "path" => "locations/<id>/inventory_levels"],
    ];

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return Location|null
     */
    public static function find(
        Session $session,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?Location {
        $result = parent::baseFind(
            $session,
            array_merge(["id" => $id], $urlIds),
            $params,
        );
        return !empty($result) ? $result[0] : null;
    }

    /**
     * @param Session $session
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     * @return Location[]
     */
    public static function all(
        Session $session,
        array $urlIds = [],
        array $params = []
    ): array {
        return parent::baseFind(
            $session,
            [],
            $params,
        );
    }

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params
     *
     * @return array|null
     */
    public static function delete(
        Session $session,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?array {
        $response = parent::request(
            "delete",
            "delete",
            $session,
            array_merge(["id" => $id], $urlIds),
            $params,
        );

        return $response->getDecodedBody();
    }
}
