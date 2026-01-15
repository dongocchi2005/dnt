<?php
// app/Models/Logo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logo extends Model
{
    protected $fillable = ['path'];

    public static function firstLogo()
    {
        return static::first();
    }
}
