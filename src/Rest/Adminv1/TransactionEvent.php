<?php

declare(strict_types=1);

namespace Tiendanube\Rest\Adminv1;

use Tiendanube\Auth\Session;
use Tiendanube\Rest\Base;

class TransactionEvent extends Base
{
    protected array $amount;
    protected string $created_at;
    protected string $expires_at;
    protected string $happened_at;
    protected ?string $failure_code;
    protected ?array $info;
    protected int $id;
    protected array $status;
    protected string $type;
    protected int $transaction_id;

    public static string $apiVersion = "v1";
    protected static array $hasOne = [];
    protected static array $hasMany = [];
    protected static array $paths = [
        ["http_method" => "post", "operation" => "post", "ids" => ["order_id", "transaction_id"],
            "path" => "orders/<order_id>/transactions/<transaction_id>/events"],
    ];
}
