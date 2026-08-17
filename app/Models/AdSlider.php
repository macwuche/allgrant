<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdSlider extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Ready-made visual presets an admin can pick from when creating a
     * slide: a background gradient, an accent icon (lucide name + color),
     * and whether the template reads as a "dark" card (affects text color).
     */
    public static function templates(): array
    {
        return [
            1 => ['label' => 'Lavender', 'gradient' => ['#dfe3fb', '#c7cff6'], 'icon' => 'hand-coins', 'icon_color' => '#4a3aeb', 'dark' => false],
            2 => ['label' => 'Mint', 'gradient' => ['#d7f5e3', '#b7ecce'], 'icon' => 'piggy-bank', 'icon_color' => '#1c9a5b', 'dark' => false],
            3 => ['label' => 'Sky', 'gradient' => ['#d7ebfd', '#b6dbfb'], 'icon' => 'trending-up', 'icon_color' => '#0b6ee0', 'dark' => false],
            4 => ['label' => 'Peach', 'gradient' => ['#fde3d0', '#fbc9a3'], 'icon' => 'gift', 'icon_color' => '#e0631c', 'dark' => false],
            5 => ['label' => 'Rose', 'gradient' => ['#fbdce7', '#f6b9d0'], 'icon' => 'heart-handshake', 'icon_color' => '#d63384', 'dark' => false],
            6 => ['label' => 'Sunny', 'gradient' => ['#fdf3c8', '#fbe58c'], 'icon' => 'star', 'icon_color' => '#a5720b', 'dark' => false],
            7 => ['label' => 'Teal', 'gradient' => ['#d0f5ee', '#a3ecdc'], 'icon' => 'shield-check', 'icon_color' => '#0d9488', 'dark' => false],
            8 => ['label' => 'Charcoal', 'gradient' => ['#2b2f3a', '#12141c'], 'icon' => 'zap', 'icon_color' => '#ffffff', 'dark' => true],
            9 => ['label' => 'Indigo', 'gradient' => ['#e0e0fd', '#c7c7fb'], 'icon' => 'credit-card', 'icon_color' => '#4338ca', 'dark' => false],
            10 => ['label' => 'Coral', 'gradient' => ['#ffe1db', '#ffbfb0'], 'icon' => 'award', 'icon_color' => '#e34d3c', 'dark' => false],
        ];
    }

    public function templateData(): array
    {
        return self::templates()[$this->template] ?? self::templates()[1];
    }
}
