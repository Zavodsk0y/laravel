<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileAccess extends Model
{
    use HasFactory;

    protected $table = 'file_accesses';

    public $timestamps = false;

    protected $fillable = ['user_id', 'file_id', 'type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }
}
