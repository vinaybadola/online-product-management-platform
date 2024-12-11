<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'price','slug'];

    public function setSlugAttribute($value){
        $this->attributes['slug'] = $value ? : strtolower(str_replace(' ', '-', $this->attributes['name']));
    }
    
}
