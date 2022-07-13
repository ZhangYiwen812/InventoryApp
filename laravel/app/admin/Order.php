<?php

namespace App\admin;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public $table = 'order';
    public $timestamps = false;
    protected $fillable = ['orderid','sendphonenumber','recphonenumber','state','subnumber'];
}
