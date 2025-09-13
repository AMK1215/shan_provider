<?php

namespace App\Enums;

enum UserType: int
{
    case Owner = 10;
    case Agent = 20;
    case SubAgent = 30;
    case Player = 40;
    case SystemWallet = 50;

    public static function usernameLength(UserType $type): int
    {
        return match ($type) {
            self::Owner => 1,
            self::Agent => 2,
            self::SubAgent => 3,
            self::Player => 4,
            self::SystemWallet => 5,
        };
    }

    public static function childUserType(UserType $type): UserType
    {
        return match ($type) {
            self::Owner => self::Agent,
            self::Agent => self::SubAgent,
            self::SubAgent => self::Player,
            self::Player, self::SystemWallet => self::Player,
        };
    }

    public static function canHaveChild(UserType $parent, UserType $child): bool
    {
        return match ($parent) {
            self::Owner => $child === self::Agent,
            self::Agent => $child === self::SubAgent || $child === self::Player,
            self::SubAgent => $child === self::Player,
            self::Player, self::SystemWallet => false,
        };
    }
}
