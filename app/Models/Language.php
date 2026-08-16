<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    private static ?\Illuminate\Support\Collection $allLanguages = null;

    public static function getAllCached(): \Illuminate\Support\Collection
    {
        if (self::$allLanguages === null) {
            self::$allLanguages = cache()->remember('languages_all', 300, fn() => self::all());
        }
        return self::$allLanguages;
    }

    public function scopeOrder($query, string $order)
    {
        if ($order !== null) {
            return $query->orderBy('id', $order);
        }

        return $query;
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('name', 'like', '%'.$search.'%')
                ->orWhere('locale', 'like', '%'.$search.'%');
        }

        return $query;
    }
}
