<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class Category extends Base
{
    protected string $created_at;
    protected ?array $description;
    protected ?string $google_shopping_category;
    protected array $handle;
    protected int $id;
    protected int $parent;
    protected array $name;
    protected array $seo_description;
    protected array $seo_title;
    protected array $subcategories;
    protected string $updated_at;

    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => [], "path" => "categories"],
        ["http_method" => "post", "operation" => "post", "ids" => [], "path" => "categories"],
        ["http_method" => "get", "operation" => "get", "ids" => ["id"], "path" => "categories/<id>"],
        ["http_method" => "put", "operation" => "put", "ids" => ["id"], "path" => "categoeries/<id>"],
        ["http_method" => "delete", "operation" => "delete", "ids" => ["id"], "path" => "categoeries/<id>"],
    ];

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return Category|null
     */
    public static function find(
        Session $session,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?Category {
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
     *     language,
     *     since_id,
     *     handle,
     *     parent_id,
     *     created_at_max,
     *     created_at_min,
     *     updated_at_min,
     *     updated_at_max,
     *     page,
     *     per_page,
     *     fields,
     *
     * @return Category[]
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
