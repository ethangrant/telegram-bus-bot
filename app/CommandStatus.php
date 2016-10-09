<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Chat;

class CommandStatus extends Model
{
    /**
     * Specify the name of the table.
     *
     * @var string
     */
    public $table = 'command_status';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status',
    ];

    /**
     * Get the chat this command status belongs to
     */
    public function chat()
    {
        $this->belongsTo(Chat::class);
    }
}
