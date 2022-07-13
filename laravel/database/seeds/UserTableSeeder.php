<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'phonenumber' => '15050500001','name' => '张三',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050500002','adminkey' => '11202','auth' => 0,
            ],
            [
                'phonenumber' => '15050500002','name' => '李四',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '12345','auth' => 0,
            ],
            [
                'phonenumber' => '15050572717','name' => '王五',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '23546','auth' => 0,
            ],
            [
                'phonenumber' => '15050500003','name' => '二六',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '78966','auth' => 0,
            ],
            [
                'phonenumber' => '15050500004','name' => '小红',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '12355','auth' => 0,
            ],
            [
                'phonenumber' => '15050500005','name' => '小张',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '98652','auth' => 0,
            ],
            [
                'phonenumber' => '15050500006','name' => '晓霞',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '99968','auth' => 0,
            ],
            [
                'phonenumber' => '15050500007','name' => '宁宁',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '78889','auth' => 0,
            ],
            [
                'phonenumber' => '15050500008','name' => '小刚',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '96583','auth' => 0,
            ],
            [
                'phonenumber' => '15050500009','name' => '甘冈',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050500001','adminkey' => '23225','auth' => 0,
            ],
            [
                'phonenumber' => '15050500010','name' => '塔基',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '45695','auth' => 0,
            ],
            [
                'phonenumber' => '15050500011','name' => '雄安',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '99363','auth' => 0,
            ],
            [
                'phonenumber' => '15050500012','name' => '小明',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050500001','adminkey' => '77458','auth' => 0,
            ],
            [
                'phonenumber' => '15050500013','name' => '小李',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '45689','auth' => 0,
            ],
            [
                'phonenumber' => '15050500014','name' => '可可',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '32178','auth' => 0,
            ],
            [
                'phonenumber' => '15050500015','name' => '琪琪',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '00235','auth' => 0,
            ],
            [
                'phonenumber' => '15050500016','name' => '钉钉',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '65000','auth' => 0,
            ]
        ];
        DB::table('user')->insert($data);
    }
}
