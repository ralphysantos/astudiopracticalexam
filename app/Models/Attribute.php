<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'name',
        'type',
    ];

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function entities()
    {
        return $this->belongsToMany(Project::class, 'attributes_values', 'attribute_id', 'entity_id');
    }
}
