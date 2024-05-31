<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NSidebar extends Model
{
    use HasFactory;
    protected $fillable = ['title','access','route','is_parent','n_sidebar_id','serial','status'];

     public function child_bar(){
        return $this->hasMany(NSidebar::class,'n_sidebar_id','id');
    }
}
