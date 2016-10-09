<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Chat;

class Alias extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'alias', 'atcocode',
    ];

    /**
     * Get the chat this alias belongs to
     */
    public function chat()
    {
        $this->belongsTo(Chat::class);
    }
}
