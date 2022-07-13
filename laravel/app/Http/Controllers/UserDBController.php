<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\admin\User;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use App\Excelhandle\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

use App\Option;

use Barryvdh\Debugbar\Facade as DebugBar;

class UserDBController extends Controller
{
    /******************************  注册用户  ******************************/
    public function insertUserdata(Request $request){
        // 手机号是否注册过
        if(User::find($request->get('phonenumber'))){
            return response()->json(['insert'=>1]);
        }else {
            $data = new User();
            $data->phonenumber=$request->get('phonenumber');
            $data->name=$request->get('name');
            $data->email=$request->get('email');
            $data->password=$request->get('password1');
            $data->adminphone=$request->get('phonenumber');
            // 随机生成5位有效数字
            $validcode = '';
            for($i=0;$i<5;$i++){
                $r = random_int(0,9);
                $validcode = $validcode . $r;
            }
            $data->adminkey=$validcode;
            $data->save();
            return response()->json(['insert'=>2]);
        }
    }
    /******************************  登录后操作  ****************************/
    /****************************  检查登录有效性  **************************/
    public function isLogon(Request $request){
        $myinfo = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            return response()->json(['is'=>1]);
        }
        return response()->json(['is'=>0]);
    }
    /************************  获取用户是否是管理用户  ***********************/
    public function getIsAdminUser(Request $request){
        $myinfo = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            if($myinfo->phonenumber==$myinfo->adminphone){
                // 是管理用户
                return response()->json(['is'=>2,'name'=>$myinfo->name,'auth'=>$myinfo->auth]);
            }else{
                // 是被管理用户
                return response()->json(['is'=>1,'adminphone'=>substr($myinfo->adminphone,7,11),'auth'=>$myinfo->auth]);
            }
        }
        return response()->json(['is'=>0]);
    }
    /***************************  获取用户数据  ******************************/
    public function getOnlyUserdata(Request $request){
        $myinfo = User::find($request->get('phonenumber'));
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            return response()->json(['data'=>$myinfo,'getdata'=>1]);
        }
        return response()->json(['getdata'=>0]);
    }
    /********************  *获取指定管理用户的用户列表  ***********************/
    public function getOnlyAdminPhonedata(Request $request){
        $myinfo = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            $data = User::where('adminphone','=',$request->phonenumber)->get()->toArray();
            DebugBar::log($data);
            return response()->json(['data'=>$data,'getdata'=>1]);
        }
        return response()->json(['getdata'=>0]);
    }
    /***************  获取指定管理用户的用户列表(适应穿梭框)  *****************/
    public function getOnlyAdminPhonedataTotransfer(Request $request){
        $myinfo = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            $users = User::where('adminphone','=',$request->phonenumber)->get()->toArray();
            $usersTotransfer = [];
            foreach($users as $user){
                array_push($usersTotransfer,[
                    'key' => $user['phonenumber'],
                    'label' => $user['name'].'('.$user['phonenumber'].')',
                    'disabled' => false
                ]);
            }
            return response()->json(['data'=>$usersTotransfer,'get'=>1]);
        }
        return response()->json(['get'=>0]);
    }
    /****************  搜索管理用户的用户名或手机号并分页获取  *****************/
    public function getSearchPageAdminPhonedata(Request $request){
        $phonenumber_AdminPhone = $request->phonenumber_AdminPhone;
        $searchtext = $request->searchtext;
        $myinfo = User::find($phonenumber_AdminPhone);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            $data1= User::where('adminphone','=',$phonenumber_AdminPhone);
            if($searchtext!=""){
                // searchtext有数据
                $data2 = $data1->where(function($query) use($searchtext) {
                                    $query->where('phonenumber','like','%'.$searchtext.'%')
                                        ->orWhere('name','like','%'.$searchtext.'%');
                                })->orderBy('phonenumber');
                $data3= $data2->paginate($request->pageSize,['*'],'',$request->currentPage);
                return response()->json(['getdata'=>2,'data'=>$data3]);
            }else{
                // searchtext无数据
                $data2=$data1->orderBy('phonenumber')
                            ->paginate($request->pageSize,['*'],'',$request->currentPage);
                return response()->json(['getdata'=>1,'data'=>$data2]);
            }
        }
        return response()->json(['getdata'=>0]);
    }
    /*****************************  *修改密码  *****************************/
    public function updataUserPassword(Request $request){
        $myinfo = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            $myinfo->password=$request->password;
            $myinfo->save();
            return response()->json(['updata'=>1]);
        }
        return response()->json(['updata'=>0]);
    }
    /******************************  修改姓名  ******************************/
    public function updataUserName(Request $request){
        $myinfo = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            $rules = [
                'newname'=> ['required','string','min:1','max:20'],
            ];
            $validator=validator()->make($request->all(),$rules);
            if($validator->fails()){
                return response()->json(['updata'=>1]);
            } else {
                $myinfo->name=$request->newname;
                $myinfo->save();
                return response()->json(['updata'=>2]);
            }
        }
        return response()->json(['updata'=>0]);
    }
    /****************************  刷新管理密匙  ****************************/
    public function refreshKey(Request $request){
        $myinfo = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            // 随机生成5位有效数字
            $validcode = '';
            for($i=0;$i<5;$i++){
                $r = random_int(0,9);
                $validcode = $validcode . $r;
            }
            $myinfo->adminkey=$validcode;
            $myinfo->save();
            return response()->json(['data'=>$myinfo,'refresh'=>1]);
        }
        return response()->json(['refresh'=>0]);
    }
    /******************************  销毁账号  *****************************/
    public function destroyUserdata(Request $request){
        $myinfo = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            // 所有子账号恢复自管理  mysql语句是否可以实现
            $users = User::where('adminphone',$request->phonenumber)
                            ->select('phonenumber')
                            ->get()->toArray();
            DebugBar::log($users);
            foreach($users as $value){
                $user = User::find($value['phonenumber']);
                $user->update(['adminphone' => $value['phonenumber']]);
            }
            //删除商品数据
            Schema::dropIfExists('commodity'.$request->phonenumber);
            //删除盘点表及盘点订单
            $orderids=DB::table('order')
                        ->select('orderid')
                        ->orderBy('orderid', 'asc')
                        ->distinct()->get()->toArray();
            foreach($orderids as $orderid){
                Schema::dropIfExists('stock'.$orderid->orderid);
            }
            $orders=DB::table('order')->where('sendphonenumber',$request->phonenumber);
            $orders->delete();
            // 删除账号
            $myinfo->delete();
            return response()->json(['destroy'=>1]);
        }
        return response()->json(['destroy'=>0]);
    }
    /*****************************  初始化账号  ****************************/
    public function resetUserdata(Request $request){
        $myinfo = User::find($request->get('phonenumber'));
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            // 所有子账号恢复自管理  mysql语句是否可以实现
            $users = User::where('adminphone',$request->phonenumber)
                            ->select('phonenumber')
                            ->get()->toArray();
            foreach($users as $value){
                $user = User::find($value['phonenumber']);
                $user->update(['adminphone' => $value['phonenumber']]);
            }
            //删除商品数据
            Schema::dropIfExists('commodity'.$request->phonenumber);
            //删除盘点表及盘点订单
            $orderids=DB::table('order')
                        ->select('orderid')
                        ->orderBy('orderid', 'asc')
                        ->distinct()->get()->toArray();
            foreach($orderids as $orderid){
                Schema::dropIfExists('stock'.$orderid->orderid);
            }
            $orders=DB::table('order')->where('sendphonenumber',$request->phonenumber);
            $orders->delete();
            // 复位账号
            $myinfo->adminphone=$request->get('phonenumber');
            $myinfo->save();
            return response()->json(['reset'=>1]);
        }
        return response()->json(['reset'=>0]);
    }
    /****************************  添加管理账号  *****************************/
    public function insertAdminPhone(Request $request){
        $myinfo = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            $rules = [
                'addPhonenumber'=> ['required','string','min:11','max:11','regex:/^\d+$/'],
                'adminkey' => ['required','min:5','max:5','regex:/^\d+$/'],
            ];
            $validator = validator()->make($request->all(),$rules);
            if(!$validator->fails()){
                $adduser = User::find($request->addPhonenumber);
                if($adduser!=null){
                    if($adduser->phonenumber==$adduser->adminphone){
                        if($request->adminkey==$adduser->adminkey){
                            $adduser->adminphone=$request->addAdminphone;
                            $adduser->save();
                            return response()->json(['insert'=>5]); // 5-添加账号成功
                        }else{
                            return response()->json(['insert'=>4]); // 4-管理密匙错误
                        }
                    }else{
                        // 3-此账号已被管理，如需管理此账号，需要初始化。
                        return response()->json(['insert'=>3,'data'=>substr($adduser->adminphone,7,11)]);
                    }
                }else{
                    return response()->json(['insert'=>2]); // 2-账号未注册
                }
            }else{
                return response()->json(['insert'=>1]); // 1-输入不合法
            }
        }
        return response()->json(['insert'=>0]); // 0-登录失效
    }
    /***********************  清除所有受管理的账号  ***************************/
    public function delAllAdminPhone(Request $request){
        $myinfo = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            $allusers = User::where('adminphone','=',$request->phonenumber)->get()->toArray();
            foreach($allusers as $user)
            {
                $this->delOneAdminPhone($user['phonenumber']);
            }
            return response()->json(['delAll'=>1]);
        }
        return response()->json(['delAll'=>0]);
    }
    /***********************  删除所选的受管理的账号  ***************************/
    public function delSelectionAdminPhone(Request $request){
        $myinfo = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            $removePhonenumbers = $request->removePhonenumbers;
            DebugBar::log($removePhonenumbers);
            foreach($removePhonenumbers as $removePhonenumber)
            {
                DebugBar::log($removePhonenumber);
                $this->delOneAdminPhone($removePhonenumber['phonenumber']);
            }
            return response()->json(['delSele'=>1]);
        }
        return response()->json(['delSele'=>0]);
    }
    /************************  删除账号 复位管理账号  **************************/
    public function delAdminPhone(Request $request){
        $myinfo = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            // 如果是登录状态，那么修改移除账号的管理账号为自己
            $this->delOneAdminPhone($request->removePhonenumber);
            return response()->json(['del'=>1]);
        }
        return response()->json(['del'=>0]);
    }
    /***************  复位某账号的受管理的账号(自管理) 复用代码  *****************/
    public function delOneAdminPhone($removePhonenumber){
        $data = User::find($removePhonenumber);
        $data->adminphone = $removePhonenumber;
        $data->save();
    }
    /************************  Excel批量添加用户数据  **************************/
    public function insertExcelAllUserdata(Request $request){
        $phonenumber = $request->phonenumber;
        $erroruserinfos = [];
        $myinfo = User::find($phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            // 判断文件是否存在
            if($request->hasFile('addUsersfile')){
                // 读取文件验证是否上传成功
                if($request->file('addUsersfile')->isValid()){
                    $path = $request->file('addUsersfile')->move('./uploads'
                                ,'excelFileUser'.$phonenumber.'.'.
                                $request->file('addUsersfile')->getClientOriginalExtension()
                            );
                    Excel::import(new UsersImport($request),$path);
                    $isExcess=$request->session()->pull('isExcess',true);
                    $whichMemberText=$request->session()->pull('whichMemberText','数据超额');
                    if(!$isExcess){
                        $erroruserinfos=$request->session()->pull('erroruserinfos',[]);
                        if(count($erroruserinfos)==0){
                            // 8-上传成功
                            return response()->json(['insertExcelAll'=>9]);
                        }else{
                            // 上传失败 清除刚刚添加的前段所有受管理的账号
                            $headaddusers=$request->session()->pull('headaddusers',[]);
                            foreach($headaddusers as $headadduser)
                            {
                                $this->delOneAdminPhone($headadduser);
                            }
                            // 8-数据不合法或验证未通过
                            // 4-数据不合法 5-账号未注册 6-此账号已被管理 7-管理密匙错误
                            // DebugBar::log($erroruserinfos);
                            return response()->json(['insertExcelAll'=>8,'erroruserinfos'=>$erroruserinfos]);
                        }
                    }else{
                        // 3-数据超额
                        return response()->json(['insertExcelAll'=>3,'whichMemberText'=>$whichMemberText]);
                    }
                }else{
                    // 2-文件上传失败
                    return response()->json(['insertExcelAll'=>2]);
                }
            }else{
                // 1-文件不存在
                return response()->json(['insertExcelAll'=>1]);
            }
        }
        return response()->json(['insertExcelAll'=>0]); // 0-登录失效
    }
    /****************************  下载账号样表  ********************************/
    public function downloadUserExampletable(){
        $pathToFile="./批量添加账号样表.xlsx";
        return response()->download($pathToFile);
        // return response()->download($file,$filename.'.xls');
    }
    /***************************************************************************/


    // 测试代码
    public function test2(Request $request){
        // $data1 = Session::get('name', 'kk');
        // return back();
    }
    public function test(Request $request){

    }
}