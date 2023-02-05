<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class Webhook extends Base
{
    protected string $created_at;
    protected string $event;
    protected int $id;
    protected string $updated_at;
    protected string $url;

    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => [], "path" => "webhooks"],
        ["http_method" => "post", "operation" => "post", "ids" => [], "path" => "webhooks"],
        ["http_method" => "get", "operation" => "get", "ids" => ["id"], "path" => "webhooks/<id>"],
        ["http_method" => "put", "operation" => "put", "ids" => ["id"], "path" => "webhooks/<id>"],
        ["http_method" => "delete", "operation" => "delete", "ids" => ["id"], "path" => "webhooks/<id>"],
    ];

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return Webhook|null
     */
    public static function find(
        Session $session,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?Webhook {
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
     * @return Webhook[]
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
