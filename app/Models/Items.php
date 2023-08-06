<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Items extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name', 'desc', 'price', 'shop_id', 'photo_paths'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
    ];

    public function shop() {
        return $this->belongsTo(Shops::class, 'shop_id', 'id');
    }

}
