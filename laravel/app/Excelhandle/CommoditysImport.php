<?php

namespace App\Excelhandle;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

use App\admin\Commodity;
use App\admin\User;

use Barryvdh\Debugbar\Facade as DebugBar;
//新增
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CommoditysImport implements ToCollection
{
    public $request = null;
    public $phonenumber = '';
    public function __construct($request){
        $this->request=$request;
        $this->phonenumber=$request->phonenumber;
    }

    public function collection(Collection $rows)
    {
        unset($rows[0]);
        // 检查商品的数量上限
        $myinfo = User::find($this->phonenumber);
        $commoditynumber = DB::table('commodity'.$this->phonenumber)->count();
        $rowslength = count($rows);
        $commodityssum = $commoditynumber + $rowslength;
        if($commodityssum <= $myinfo->comsmax){
            // 商品的数量未超限制
            $rules = [
                'id' => ['required','string','min:1','max:10','regex:/^[A-Za-z0-9]+$/'],//只能为数字字母
                'name' => ['required','string','min:1','max:30'],
                'smallunit' => ['required','string','min:1','max:1'],
                'bigunit' => ['required','string','min:1','max:1'],
                'bigtosmall_specs' => ['required','integer','min:1','max:999999999']
            ];
            $db = DB::table('commodity'.$this->phonenumber);
            $data = [];
            $errorcominfos = [];
            foreach ($rows as $row) 
            {
                $formdata = [];
                $formdata['id']=$row[0];
                $formdata['name']=$row[1];
                $formdata['smallunit']=$row[2];
                $formdata['bigunit']=$row[3];
                $formdata['bigtosmall_specs']=$row[4];
                $validator = validator()->make($formdata,$rules);
                if ($validator->fails()) {
                    DebugBar::log($validator->errors());
                    // 4-输入不合法
                    unset($formdata['smallunit']);
                    unset($formdata['bigunit']);
                    unset($formdata['bigtosmall_specs']);
                    array_push($errorcominfos,[
                        'errorCode' => 4,
                        'errorText' => '输入不合法',
                        'errorCom' => $formdata
                    ]);
                }else{
                    $idIn = DB::table('commodity'.$this->phonenumber)->find($formdata['id']);
                    if(!$idIn){
                        array_push($data,[
                            'id' => $row[0],
                            'name' => $row[1],
                            'smallunit' => $row[2],
                            'bigunit' => $row[3],
                            'bigtosmall_specs' => $row[4]
                        ]);
                    }else{
                        // 5-重复添加商品
                        array_push($errorcominfos,[
                            'errorCode' => 5,
                            'errorText' => '重复添加商品',
                            'errorCom' => $formdata
                        ]);
                    }
                }
            }
            $this->request->session()->put('isExcess',false);
            $this->request->session()->put('errorcominfos',$errorcominfos);
            if(count($errorcominfos)==0){
                $db->insert($data);
            }
        }else{
            // 商品的数量超过限制
            $this->request->session()->put('isExcess',true);
            if($myinfo->auth == 0){
                $this->request->session()->put('whichMemberText','数据超额，请开通会员！');
            }else if($myinfo->auth == 1){
                $this->request->session()->put('whichMemberText','数据超额，请升级会员！');
            }else if($myinfo->auth == 2){
                $this->request->session()->put('whichMemberText','数据超额，最大1000个哦！');
            }
        }
    }
}
