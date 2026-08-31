<?php

namespace App\Modules\MenuManager\Application\DTO;

final class Optional
{
    private static ?self $instance = null;

    private function __construct() {}

    public static function value(): self
    {
        return self::$instance ??= new self();
    }
}
