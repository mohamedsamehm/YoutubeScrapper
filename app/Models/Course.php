<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;
    protected $fillable = [
        'playlist_id',
        'title',
        'description',
        'thumbnail_url',
        'channel_name',
        'category',
        'search_query',
        'video_count',
        'view_count',
        'playlist_duration'
    ];
    protected $primaryKey = 'playlist_id';

    protected $casts = [
        'video_count' => 'integer',
        'view_count'  => 'integer',
    ];

    public $incrementing = false;
    protected $keyType = 'string';  
 
    // YouTube playlist URL
    public function getYoutubeUrlAttribute(): string
    {
        return "https://www.youtube.com/playlist?list={$this->playlist_id}";
    }
 
    // Human-readable view count: 1200000 → "1.2M", 340000 → "340K"
    public function getFormattedViewCountAttribute(): string
    {
        if ($this->view_count >= 1000000) {
            return round($this->view_count / 1000000, 1) . 'M';
        }
 
        if ($this->view_count >= 1000) {
            return round($this->view_count / 1000, 1) . 'K';
        }
 
        return (string) $this->view_count;
    }
    
}
