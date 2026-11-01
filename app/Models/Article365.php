<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article365 extends Model
{
    protected $table = 'articles';

    protected $fillable = [
        'user_id',
        'article_category_id',
        'title',
        'slug',
        'summary',
        'content',
        'cover_image',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            ArticleCategorie365::class,
            'article_category_id'
        );
    }
}
