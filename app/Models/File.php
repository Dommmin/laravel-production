<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
    ];

    protected $appends = ['url'];

    protected function url(): Attribute
    {
        return Attribute::make(get: function () {
            return asset('storage/'.$this->path);
        });
    }
}
