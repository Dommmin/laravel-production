<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportJob extends Model
{
    protected $fillable = [
        'status',
        'errors',
        'total_rows',
        'processed_rows',
        'filename',
    ];

    protected $casts = [
        'errors' => 'array',
    ];

    public function hasErrors(): bool
    {
        return (bool) $this->errors;
    }
}
