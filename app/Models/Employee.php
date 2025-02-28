<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "employee";

    protected $fillable = [
        'company_id',
        'manager_id',
        'name',
        'phone',
        'address'
    ];

    protected $dates = ['deleted_at'];

    public function manager()
    {
        return $this->belongsTo(Manager::class);
    }
}
