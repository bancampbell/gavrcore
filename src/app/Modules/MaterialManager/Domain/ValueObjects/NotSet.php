<?php

namespace App\Modules\MaterialManager\Domain\ValueObjects;

final class NotSet
{
    private static ?self $instance = null;

    private function __construct() {}

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }
}
