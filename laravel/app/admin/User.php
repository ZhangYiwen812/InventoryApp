<?php

namespace App\admin;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'User';
    protected $primaryKey = 'phonenumber';
    public $timestamps = false;
    protected $fillable = ['phonenumber','name','email','password','adminphone','adminkey','auth'];
}
