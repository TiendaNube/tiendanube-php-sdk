<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class PaymentProvider extends Base
{
    protected int $app_id;
    protected ?string $checkout_js_url;
    protected ?array $checkout_payment_options;
    protected ?string $configuration_url;
    protected ?string $description;
    protected bool $enabled;
    protected ?array $features;
    protected int $id;
    protected array $name;
    protected ?string $public_name;
    protected bool $published;
    protected ?array $logo_urls;
    protected ?array $rates;
    protected ?string $rates_url;
    protected int $store_id;
    protected ?string $support_url;
    protected ?array $supported_currencies;
    protected ?array $supported_payment_methods;

    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [];
    protected static array $paths = [
        ["http_method" => "get", "operation" => "get", "ids" => [], "path" => "payment_providers"],
        ["http_method" => "post", "operation" => "post", "ids" => [], "path" => "payment_providers"],
        ["http_method" => "get", "operation" => "get", "ids" => ["id"], "path" => "payment_providers/<id>"],
        ["http_method" => "put", "operation" => "put", "ids" => ["id"], "path" => "payment_providers/<id>"],
        ["http_method" => "delete", "operation" => "delete", "ids" => ["id"], "path" => "payment_providers/<id>"],
    ];

    /**
     * @param Session $session
     * @param int|string $id
     * @param array $urlIds
     * @param mixed[] $params Allowed indexes:
     *     fields
     *
     * @return PaymentProvider|null
     */
    public static function find(
        Session $session,
        $id,
        array $urlIds = [],
        array $params = []
    ): ?PaymentProvider {
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
     *     q,
     *     handle,
     *     category_id,
     *     published,
     *     free_shipping,
     *     max_stock,
     *     min_stock,
     *     has_promotional_price,
     *     has_weight,
     *     has_all_dimensions,
     *     has_weight_and_all_dimensions,
     *     created_at_max,
     *     created_at_min,
     *     updated_at_min,
     *     updated_at_max,
     *     sort_by,
     *     page,
     *     per_page,
     *     fields,
     *
     * @return PaymentProvider[]
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
