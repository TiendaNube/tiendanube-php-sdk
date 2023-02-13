<?php

declare(strict_types=1);

namespace Tiendanube\Clients;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

final class FakeResourceWithCustomPrefix extends Base
{
    protected int $id;
    protected ?string $attribute;
    protected ?array $fake_resource_with_custom_prefix;

    public static string $apiVersion = "v50";

    /** @var Base[] */
    protected static array $hasOne = [];

    /** @var Base[] */
    protected static array $hasMany = [];

    /** @var array[] */
    protected static array $paths = [
        [
            "http_method" => "get", "operation" => "get", "ids" => ["id"],
            "path" => "fake_resource_with_custom_prefix/<id>"
        ],
    ];

    protected static ?string $customPrefix = "/custom_prefix";

    public static function find(Session $session, int $id, array $params = []): ?FakeResourceWithCustomPrefix
    {
        $result = parent::baseFind($session, ["id" => $id], $params);
        return !empty($result) ? $result[0] : null;
    }
}
