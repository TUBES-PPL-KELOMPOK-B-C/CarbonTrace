<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleCategorie365 extends Model
{
    protected $table = 'article_categories';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function articles()
    {
        return $this->hasMany(
            Article365::class,
            'article_category_id'
        );
    }
}
