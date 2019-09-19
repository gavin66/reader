<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NovelFollow extends Model
{
//    use SoftDeletes;

    protected $table = 'novel_follow';

    protected $guarded = [];
}
