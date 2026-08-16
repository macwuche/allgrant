<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingContent extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    private static array $requestCache = [];

    // Fetch all content for a locale in one query, cache by type in memory
    public static function getByType(string $type, string $locale): \Illuminate\Support\Collection
    {
        if (!isset(self::$requestCache[$locale])) {
            self::$requestCache[$locale] = cache()->remember(
                "landing_contents_{$locale}", 300,
                fn() => self::where('locale', $locale)->get()->groupBy('type')
            );
        }

        return self::$requestCache[$locale][$type] ?? collect();
    }
}
