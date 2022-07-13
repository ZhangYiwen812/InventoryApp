<?php

namespace App\admin;

use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    public $table = 'commodity';
    public function __construct($phonenumber){
        $this->table='commodity'.$phonenumber;
    }
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['id','name','smallunit_amount','smallunit','bigunit','bigtosmall_specs'];
}
