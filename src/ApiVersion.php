<?php

declare(strict_types=1);

namespace Tiendanube;

class ApiVersion
{
    /** @var string */
    public const V1 = "v1";
    /** @var string */
    public const LATEST = self::V1;

    private static $ALL_VERSIONS = [
        self::V1,
    ];

    public static function isValid(string $version): bool
    {
        return in_array($version, self::$ALL_VERSIONS);
    }
}
