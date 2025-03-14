<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'status',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'attributes_values','entity_id','attribute_id')->withPivot('value')->withTimestamps(); 
    }

}
