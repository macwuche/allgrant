<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    private static ?\Illuminate\Support\Collection $allPlugins = null;

    public static function getAllCached(): \Illuminate\Support\Collection
    {
        if (self::$allPlugins === null) {
            self::$allPlugins = cache()->remember('plugins_all', 300, fn() => self::all());
        }
        return self::$allPlugins;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}
