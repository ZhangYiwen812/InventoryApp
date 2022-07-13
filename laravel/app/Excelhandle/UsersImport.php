<?php

namespace App\Excelhandle;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use App\admin\User;

use Barryvdh\Debugbar\Facade as DebugBar;

use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class UsersImport implements ToCollection
{
    public $request = null;
    public $phonenumber = '';
    public function __construct($request){
        $this->request = $request;
        $this->phonenumber = $request->phonenumber;
    }

    public function collection(Collection $rows)
    {
        unset($rows[0]);
        // 准备求和数据
        $myinfo = User::find($this->phonenumber);
        $adminnum = User::where('adminphone',$this->phonenumber)->count();
        $rowslength = count($rows);
        $userssum = $adminnum + $rowslength;
        // 检查管理用户的数量上限
        if($userssum <= $myinfo->usersmax){
            // 管理用户的数量未超限制
            $erroruserinfos = [];
            $rules = [
                'phonenumber'=> ['required','string','min:11','max:11','regex:/^\d+$/'],
                'adminkey' => ['required','min:5','max:5','regex:/^\d+$/'],
            ];
            $isSave = true;
            $headaddusers = [];
            foreach ($rows as $row) 
            {
                $formdata = [];
                $formdata['phonenumber']=$row[0];
                $formdata['adminkey']=$row[1];
                $validator = validator()->make($formdata,$rules);
                if($validator->fails()){ // DebugBar::log($validator->errors());
                    // 4-输入不合法
                    array_push($erroruserinfos,[
                        'errorCode' => 4,
                        'errorText' => '输入不合法',
                        'errorUser' => $formdata
                    ]);
                }else{
                    $adduser = User::find($row[0]);
                    if($adduser!=null){
                        if($adduser->phonenumber==$adduser->adminphone){
                            if($row[1]==$adduser->adminkey){
                                if($isSave){
                                    array_push($headaddusers,$row[0]);
                                    $adduser->adminphone=$this->phonenumber;
                                    $adduser->save();
                                }
                            }else{
                                // 7-管理密匙错误
                                $isSave = false;
                                array_push($erroruserinfos,[
                                    'errorCode' => 7,
                                    'errorText' => '管理密匙错误',
                                    'errorUser' => $formdata
                                ]);
                            }
                        }else{
                            // 6-此账号已被管理，需要初始化。
                            $isSave = false;
                            array_push($erroruserinfos,[
                                'errorCode' => 6,
                                'errorText' => '此账号已被管理，需要初始化。',
                                'errorUser' => $formdata
                            ]);
                        }
                    }else{
                        // 5-账号未注册
                        $isSave = false;
                        array_push($erroruserinfos,[
                            'errorCode' => 5,
                            'errorText' => '账号未注册',
                            'errorUser' => $formdata
                        ]);
                    }
                }
            }
            $this->request->session()->put('isExcess',false);
            $this->request->session()->put('headaddusers',$headaddusers);
            $this->request->session()->put('erroruserinfos',$erroruserinfos);
        }else{
            // 管理用户的数量超过限制
            $this->request->session()->put('isExcess',true);
            if($myinfo->auth == 0){
                $this->request->session()->put('whichMemberText','数据超额，请开通会员！');
            }else if($myinfo->auth == 1){
                $this->request->session()->put('whichMemberText','数据超额，请升级会员！');
            }else if($myinfo->auth == 2){
                $this->request->session()->put('whichMemberText','数据超额，最大50人哦！');
            }
        }
    }
}
