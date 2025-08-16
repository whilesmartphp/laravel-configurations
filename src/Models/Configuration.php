<?php

namespace Whilesmart\ModelConfiguration\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Configuration extends Model
{
    use HasFactory;

    protected $fillable = ['configurable_id', 'configurable_type', 'key', 'type', 'value'];

    protected $casts = [
        'value' => 'json',
    ];

    public function configurable(): MorphTo
    {
        return $this->morphTo();
    }
}
