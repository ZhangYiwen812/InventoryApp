<?php

namespace App\Http\Controllers;

use App\admin\Commodity;
use App\admin\Order;
use App\Http\Controllers\UserDBController;
use Illuminate\Http\Request;
use App\Mail\VerifMail;
use App\Mail\CompleteMail;
use Illuminate\Support\Facades\Mail;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use App\admin\User;
use Captcha;
use Barryvdh\Debugbar\Facade as DebugBar;

class VerifController extends Controller
{
    /******************************  验证码验证  ******************************/
    public function verifCaptcha(){
        $rules = ['captcha' => 'required|captcha_api:'. request('key') . ',math'];
        $validator = validator()->make(request()->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['verif'=>0]);
        } else {
            return response()->json(['verif'=>1]);
        }
    }
    /******************************  登录验证  ******************************/
    // get请求 http://localhost:8080/api/captcha/api/math 获得img和url
    public function verifLogin(Request $request){
        DebugBar::log($request->all());
        if($request->method() == 'GET'){
            $rules = [
                'phonenumber'=> ['required','string','min:11','max:11','regex:/^\d+$/'],
                'password' => ['required','string','min:8','max:20'],
            ];
            // 检查表单输入是否合法
            $validator = validator()->make($request->all(),$rules);
            if($validator->fails()){
                DebugBar::log($validator->errors());
                return response()->json(['verif'=>-1]);
            }else{
                // 手机号是否注册过
                $data = User::find($request->get('phonenumber'));
                if($data){
                    // 验证密码是否正确
                    if($data->password==$request->get('password')){
                        // 密码正确
                        Cookie::queue('phonenumber', $request->get('phonenumber'),20);
                        Cookie::queue('password', $request->get('password'),20);
                        return response()->json(['verif'=>2]);
                    }else{
                        // 密码错误
                        return response()->json(['verif'=>1]);
                    }
                }else{
                    // 没有注册
                    return response()->json(['verif'=>0]);
                }
            }
        }
        return 'method GET';
    }
    /******************************  退出登录  ******************************/
    public function Logout(Request $request){
        $myinfo = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            Cookie::queue('phonenumber', null, -1);
            Cookie::queue('password', null,-1);
            return response()->json(['Logout'=>1]);
        }
        return response()->json(['Logout'=>0]);
    }
    /*****************************  *验证登录状态  ******************************/
    public function verifLoginstate(Request $request){
        $data = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$data->phonenumber && $request->cookie('password')==$data->password){
            return response()->json(['loginstate'=>1]);
        }else{
            return response()->json(['loginstate'=>0]);
        }
    }
    /******************************  注册验证  ******************************/
    public function verifRegister(Request $request){
        if($request->method() == 'POST'){
            $rules = [
                'phonenumber'=> ['required','string','min:11','max:11','regex:/^\d+$/'],
                'name'=> ['required','string','min:1','max:20'],
                'email'=> ['required','string','email','min:1','max:320'],
                'password1' => ['required','string','min:8','max:20'],
                'password2'=> ['required','string','min:8','max:20','same:password1']
            ];
            // 检查表单输入是否合法
            $validator = validator()->make($request->all(),$rules);
            if ($validator->fails()) {
                return response()->json(['insert'=>0]);
            }else{
                $userDBcontroller = new UserDBController();
                return $userDBcontroller->insertUserdata($request);
            }
        }
        return 'method GET';
    }
    /***************************  修改密码相关方法  *****************************/
    // 设置修改密码的用户并返回email
    public function setUpdataPasswordUser(Request $request){
        if($request->method() == 'POST'){
            // 手机号是否注册过
            $data = User::find($request->get('phonenumber'));
            if($data){
                $request->session()->put('phonenumber', $request->get('phonenumber'));
                return response()->json(['setcomplete'=>1, 'email'=>$data->email]);
            }else{
                return response()->json(['setcomplete'=>0]);
            }
        }
        return 'method GET';
    }
    // 发送邮箱 验证码
    public function sendEmailvalidcode(Request $request){
        if($request->method() == 'POST'){
            $data = User::find($request->get('phonenumber'));
            $validcode = '';
            for($i=0;$i<5;$i++){
                $r = random_int(0,9);
                $validcode = $validcode . $r;
            }
            Mail::to($data->get('email'))->send(new VerifMail($validcode));
            $request->session()->put('validcode', $validcode);
        }
        return 'method GET';
    }
    // 修改密码表单验证 参数：phonenumber newpassword1 newpassword2 validcode captcha key
    public function verifForm(Request $request){
        if($request->method() == 'POST'){
            $rules1 = ['captcha' => 'required|captcha_api:'. request('key') . ',math'];
            $validator1 = validator()->make(request()->all(), $rules1);
            if ($validator1->fails()) {
                // 返回1表示验证码错误
                return response()->json(['verif'=>1]);
            } else {
                $validcode = $request->session()->get('validcode');
                // Debugbar::info($validcode);
                if($validcode!=$request->validcode){
                    // 返回2表示邮箱验证错误
                    return response()->json(['verif'=>2]);
                } else {
                    $rules2 = [
                        'newpassword1' => ['required','string','min:8','max:20'],
                        'newpassword2'=> ['required','string','min:8','max:20','same:newpassword1']
                    ];
                    $validator2 = validator()->make(request()->all(), $rules2);
                    if($validator2->fails()){
                        // 返回3表示密码验证错误
                        return response()->json(['verif'=>3]);
                    } else {
                        $data = User::find($request->phonenumber);
                        $data->password=$request->newpassword1;
                        $data->save();
                        $this->sendEmailcomplete($request);
                        // 返回4表示验证成功并修改了密码
                        return response()->json(['verif'=>4]);
                    }
                }
            }
        }
        return 'method GET';
    }
    // 发送邮箱 完成密码修改
    public function sendEmailcomplete(Request $request){
        if($request->method() == 'POST'){
            $data = User::find($request->phonenumber);
            $lastnumber=substr($request->phonenumber,7,11);
            Mail::to($data->get('email'))->send(new CompleteMail($lastnumber));
            $request->session()->put('lastnumber', $lastnumber);
        }
        return 'method GET';
    }
    /***************************  *支付验证  **********************************/
    public function verifPay(Request $request){
        $phonenumber = $request->phonenumber;
        $auth = $request->auth;
        $data = User::find($phonenumber);
        if($request->cookie('phonenumber')==$data->phonenumber && $request->cookie('password')==$data->password){
            
            
            return response()->json(['pay'=>1]);
        }
        return response()->json(['pay'=>0]);
    }
    /*************************************************************************/



    // 获取token
    public function getToken(Request $request){
        return csrf_token();
    }
    // 测试代码
    public function test2(Request $request){
        // Debugbar::info($this->name);
        // $random = '';
        // for($i=0;$i<4;$i++){
        //     $z = random_int(0,9);// 随机生成数字包含0和9
        //     $random = $random . $z;
        // }
        return response()->json(['data'=>123]);
    }

    // public function getOrderId(){
    //     //订单号码主体(YYYYMMDDHHIISSNNNNNNNN)
    //     // $order_date = date('Y-m-d');
    //     $orderid = date('YmdHis') . rand(100000,999999);
    //     $data = Order::find($orderid);
    //     if($data!=null){
    //         return $this->getOrderId();
    //     }else{
    //         return $orderid;
    //     }
    // }
    public function test(Request $request){
        // $data1 = User::where('adminphone','15050500002')->get()->toArray();
        // $data2 = DB::table('user')->where('adminphone','15050500002')->get()->toArray();
        // DebugBar::log($data1);
        // DebugBar::log($data2);

        // $arr = ['15050500001','15050572717','15050500006','15050500005','15050500015'];
        // $userdb = DB::table('user')->whereIn('phonenumber',$arr);
        // DebugBar::log($userdb->get()->toArray());

        // $userdb = DB::table('user');
        // $userdb1 = clone $userdb->where('phonenumber','like','%001%');
        // DebugBar::log($userdb1->get()->toArray());
        // $userdb2 = $userdb1->where('name','like','%小%');
        // DebugBar::log($userdb2->get()->toArray());
        // $userdb3 = $userdb2->select('*');
        // DebugBar::log($userdb3->get()->toArray());
        // DebugBar::log($userdb->get()->toArray());

        // $request->cookie('laravel_session');
        // $data1 = Cookie::queue('password', 'hhhh', 10);
        // $data1 = Cookie::make('idd','123456',10);
        // $data2 = Cookie::get('password');


        // session_start();
        // $request->session_start();
        // $data2 = Session::put('name','小杨');
        // $data2 = Session::get('name');
        // $data2 = Session::all();
        // Session::forget('name');
        
        // Session::flush();
        // $data2 = session(['key' => 'nnn']);
        // $data2 = session('name');

        // $request->session()->put('name', null);
        // DebugBar::log($request->session()->has('name'));
        // DebugBar::log($request->session()->exists('name'));
        // $request->session()->put('name', 'iiio');
        // $request->session()->put('id', '123');
        // $data1 = $request->session()->get('id');
        // $data2 = $request->session()->exists('name');
        // DebugBar::log($data1);
        // DebugBar::log($data2);
        // DebugBar::log($request->session()->has('name'));
        // DebugBar::log($request->session()->exists('name'));

        // $date = date('YmdHis');
        // $pathToFile = substr($date,0,4)."年".
        //         substr($date,4,2)."月".
        //         substr($date,6,2)."日".
        //         substr($date,8,2)."时".
        //         substr($date,10,2)."分".
        //         substr($date,12,2)."秒".rand(10000,99999)."号导出文件.xlsx";

        // 建立订单表
        // Schema::create('order', function (Blueprint $table) {
        //     $table->string('orderid',27);
        //     $table->string('sendphonenumber',11);
        //     $table->string('recphonenumber',11);
        //     $table->integer('state');
        //     $table->integer('subtotal')->unsigned();
        // });

        // 建立用户表
        //int 2147483647   unsignedint 4294967295
        // Schema::create('user', function (Blueprint $table) {
        //     $table->string('phonenumber',11)->unique();
        //     $table->string('name',20);
        //     $table->string('email',320);
        //     $table->string('password',20);
        //     $table->string('adminphone',11);
        //     $table->string('adminkey',5);
        //     $table->integer('auth')->default(2);
        //     $table->integer('usersmax')->default(60);
        //     $table->integer('comsmax')->default(1000);
        //     $table->integer('ordersmax')->default(10);
        // });
        $data = [
            [
                'phonenumber' => '15050500001','name' => '张三',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050500002','adminkey' => '11202'
            ],
            [
                'phonenumber' => '15050500002','name' => '李四',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '12345'
            ],
            [
                'phonenumber' => '15050572717','name' => '王五',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '23546'
            ],
            [
                'phonenumber' => '15050500003','name' => '二六',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '78966'
            ],
            [
                'phonenumber' => '15050500004','name' => '小红',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '12355'
            ],
            [
                'phonenumber' => '15050500005','name' => '小张',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '98652'
            ],
            [
                'phonenumber' => '15050500006','name' => '晓霞',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '99968'
            ],
            [
                'phonenumber' => '15050500007','name' => '宁宁',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '78889'
            ],
            [
                'phonenumber' => '15050500008','name' => '小刚',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '96583'
            ],
            [
                'phonenumber' => '15050500009','name' => '甘冈',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050500001','adminkey' => '23225'
            ],
            [
                'phonenumber' => '15050500010','name' => '塔基',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '45695'
            ],
            [
                'phonenumber' => '15050500011','name' => '雄安',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '99363'
            ],
            [
                'phonenumber' => '15050500012','name' => '小明',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050500001','adminkey' => '77458'
            ],
            [
                'phonenumber' => '15050500013','name' => '小李',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '45689'
            ],
            [
                'phonenumber' => '15050500014','name' => '可可',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '32178'
            ],
            [
                'phonenumber' => '15050500015','name' => '琪琪',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '00235'
            ],
            [
                'phonenumber' => '15050500016','name' => '钉钉',
                'email' => '812901456@qq.com','password' => '12345678',
                'adminphone' => '15050572717','adminkey' => '65000'
            ]
        ];
        DB::table('user')->insert($data);
    }
}
