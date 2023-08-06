<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class Verify extends Model
{

    protected $table = 'verifications_tokens';
    public $timestamps = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'user_id', 'token', 'order_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
    ];

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->user_id = Auth::user()->id;
        });
    }

}
