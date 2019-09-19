<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComicFollow extends Model
{
//    use SoftDeletes;

    protected $table = 'comic_follow';

    protected $guarded = [];
}
