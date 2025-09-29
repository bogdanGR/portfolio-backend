<?php

namespace App\Enums;

enum TechnologyCategory : string
{
    case FRONTEND = 'Frontend';
    case BACKEND = 'Backend';
    case TOOLS = 'Tools';

    public static function all()
    {
        return [
            self::FRONTEND,
            self::BACKEND,
            self::TOOLS,
        ];
    }
}
