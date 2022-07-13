<?php

namespace App\Http\Controllers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\admin\User;
use App\admin\Commodity;
use App\Excelhandle\CommoditysImport;
use Maatwebsite\Excel\Facades\Excel;

use Barryvdh\Debugbar\Facade as DebugBar;

class CommodityDBController extends Controller
{
    /****************************  *创建数据表  ********************************/
    // ['id','name','smallunit_amount','smallunit','bigunit','bigtosmall_specs']
    public function createCommoditytable(Request $request){
        $data = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$data->phonenumber && $request->cookie('password')==$data->password){
            if(!Schema::hasTable('commodity'.$request->phonenumber)) {
                Schema::create('commodity'.$request->phonenumber, function (Blueprint $table) {
                    $table->string('id',10)->unique();
                    $table->string('name',30);
                    $table->biginteger('smallunit_amount')->unsigned();
                    $table->string('smallunit',1);
                    $table->string('bigunit',1);
                    $table->integer('bigtosmall_specs');
                });
                return response()->json(['create'=>2]); // 2为如果没有表，那么创建表
            }else{
                return response()->json(['create'=>1]); // 1为如果有表，那么不创建表
            }
        }
        return response()->json(['create'=>0]); // 0为登录失效
    }
    /*****************************  *删除数据表  *********************************/
    public function dropCommoditytable(Request $request){
        $data = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$data->phonenumber && $request->cookie('password')==$data->password){
            if(Schema::hasTable('commodity'.$request->phonenumber)) {
                Schema::dropIfExists('commodity'.$request->phonenumber);
                return response()->json(['drop'=>2]); // 2为如果有表，那么删除表
            }else{
                return response()->json(['drop'=>1]); // 1为如果没有表，那么不删除
            }
        }
        return response()->json(['drop'=>0]); // 0为登录失效
    }
    /***********************  搜索商品数据并分页获取  *****************************/
    public function getSearchPageCommoditydata(Request $request){
        $data = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$data->phonenumber && $request->cookie('password')==$data->password){
            if(!Schema::hasTable('commodity'.$request->phonenumber)) {
                Schema::create('commodity'.$request->phonenumber, function (Blueprint $table) {
                    $table->string('id',10)->unique();
                    $table->string('name',30);
                    $table->biginteger('smallunit_amount')->unsigned(); //最大999 999 999
                    $table->string('smallunit',1);
                    $table->string('bigunit',1);
                    $table->integer('bigtosmall_specs')->unsigned(); //最大999 999 999
                });
            }
            if($request->searchtext!=""){
                // searchtext有数据
                $data1 = DB::table('commodity'.$request->phonenumber)
                    ->where('id','like','%'.$request->searchtext.'%')
                    ->orWhere('name','like','%'.$request->searchtext.'%');
                $data2= $data1->paginate($request->pageSize,['*'],'',$request->currentPage);
                return response()->json(['getdata'=>2,'data'=>$data2]);
            }else{
                // searchtext无数据
                $data1 = DB::table('commodity'.$request->phonenumber)->orderBy('id');
                $data2= $data1->paginate($request->pageSize,['*'],'',$request->currentPage);
                return response()->json(['getdata'=>1,'data'=>$data2]);
            }
        }
        return response()->json(['getdata'=>0]); // 0为登录失效
    }
    /*****************************  *获取商品数据  ********************************/
    public function getCommoditydata(Request $request){
        $data = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$data->phonenumber && $request->cookie('password')==$data->password){
            $data1 = DB::table('commodity'.$request->phonenumber)->find($request->id);
            return response()->json(['get'=>1,'data'=>$data1]);
        }
        return response()->json(['get'=>0]); // 0为登录失效
    }
    /*****************************  添加商品数据  ********************************/
    public function insertCommoditydata(Request $request){
        $phonenumber = $request->phonenumber;
        $myinfo = User::find($phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            $rules = [
                'id' => ['required','string','min:1','max:10','regex:/^[A-Za-z0-9]+$/'],//只能为数字字母
                'name' => ['required','string','min:1','max:30'],
                'smallunit' => ['required','string','min:1','max:1'],
                'bigunit' => ['required','string','min:1','max:1'],
                'bigtosmall_specs' => ['required','integer','min:1','max:999999999']
            ];
            $validator = validator()->make($request->all(),$rules);
            if (!$validator->fails()) {
                // 超限检查
                $myinfo = User::find($phonenumber);
                $commoditynumber = DB::table('commodity'.$phonenumber)->count()+1;
                // 检查管理用户的数量上限
                if($commoditynumber <= $myinfo->comsmax){
                    $idIn = DB::table('commodity'.$phonenumber)->find($request->id);
                    // DebugBar::log($commoditynumber);
                    if(!$idIn){
                        //改为DB读写数据库
                        $commodity = new Commodity($phonenumber);
                        $commodity->id = $request->id;
                        $commodity->name = $request->name;
                        $commodity->smallunit_amount = 0;
                        $commodity->smallunit = $request->smallunit;
                        $commodity->bigunit = $request->bigunit;
                        $commodity->bigtosmall_specs = $request->bigtosmall_specs;
                        $commodity->save();
                        return response()->json(['insert'=>6]); // 6-为插入成功
                    }else{
                        return response()->json(['insert'=>5]); // 5-重复添加商品
                    }
                }else{
                    if($myinfo->auth == 0){
                        // 4-数据超额，请开通会员！
                        $whichMemberText='数据超额，请开通会员！';
                        return response()->json(['insert'=>4,'whichMemberText'=>$whichMemberText]);
                    }else if($myinfo->auth == 1){
                        // 3-数据超额，请升级会员！
                        $whichMemberText='数据超额，请升级会员！';
                        return response()->json(['insert'=>3,'whichMemberText'=>$whichMemberText]);
                    }else if($myinfo->auth == 2){
                        // 2-数据超额，最大1000个哦！
                        $whichMemberText='数据超额，最大1000个哦！';
                        return response()->json(['insert'=>2,'whichMemberText'=>$whichMemberText]);
                    }
                }
            }else{
                return response()->json(['insert'=>1]); // 1-为输入不合法
            }
        }
        return response()->json(['insert'=>0]); // 0-为登录失效
    }
    /**************************  删除所选商品数据  ******************************/
    public function delSelectionCommoditydata(Request $request){
        $data = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$data->phonenumber && $request->cookie('password')==$data->password){
            $delCommodityids = $request->delCommodityids;
            DebugBar::log($delCommodityids);
            foreach($delCommodityids as $commodity){
                DebugBar::log($commodity);
                $commodity = DB::table('commodity'.$request->phonenumber)->where('id','=',$commodity['id']);
                $commodity->delete();
            }
            return response()->json(['delSele'=>1]);
        }
        return response()->json(['delSele'=>0]);
    }
    /**************************  清除所有商品数据  ******************************/
    public function delAllCommoditydata(Request $request){
        $data = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$data->phonenumber && $request->cookie('password')==$data->password){
            // 删除数据表
            Schema::dropIfExists('commodity'.$request->phonenumber);
            // 创建数据表
            Schema::create('commodity'.$request->phonenumber, function (Blueprint $table) {
                $table->string('id',10)->unique();
                $table->string('name',30);
                $table->biginteger('smallunit_amount')->unsigned();
                $table->string('smallunit',1);
                $table->string('bigunit',1);
                $table->integer('bigtosmall_specs');
            });
            return response()->json(['delAll'=>1]);
        }
        return response()->json(['delAll'=>0]);
    }
    /*************************  Excel批量添加商品数据  **************************/
    public function insertExcelAllCommoditydata(Request $request){
        $phonenumber = $request->phonenumber;
        $data = User::find($phonenumber);
        if($request->cookie('phonenumber')==$data->phonenumber && $request->cookie('password')==$data->password){
            // 判断文件是否存在
            if($request->hasFile('addCommoditysfile')){
                // 读取文件验证是否上传成功
                if($request->file('addCommoditysfile')->isValid()){
                    $path = $request->file('addCommoditysfile')->move('./uploads'
                                ,'excelFileCommodity'.$phonenumber.'.'.
                                $request->file('addCommoditysfile')->getClientOriginalExtension()
                            );
                    Excel::import(new CommoditysImport($request),$path);
                    $isExcess=$request->session()->pull('isExcess',false);
                    if(!$isExcess){
                        // 商品的数量未超限制
                        $errorcominfos=$request->session()->pull('errorcominfos',[]);
                        if(count($errorcominfos)==0){
                            return response()->json(['insertExcelAll'=>7]); // 7-上传成功
                        }else{
                            return response()->json(['insertExcelAll'=>6,'errorcominfos'=>$errorcominfos]); // 6-数据不合法或验证未通过
                        }
                    }else{
                        // 商品的数量超过限制
                        $whichMemberText=$request->session()->pull('whichMemberText','数据超额');
                        return response()->json(['insertExcelAll'=>3,'whichMemberText'=>$whichMemberText]); // 3-数据超额
                    }
                }else{
                    return response()->json(['insertExcelAll'=>2]); // 2-上传失败
                }
            }else{
                return response()->json(['insertExcelAll'=>1]); // 1-文件不存在
            }
        }
        return response()->json(['insertExcelAll'=>0]); // 0-登录失效
    }
    /****************************  下载商品样表  ********************************/
    public function downloadCommodityExampletable(){
        $pathToFile="./批量添加商品样表.xlsx";
        return response()->download($pathToFile);
    }
    /************************  *获取联想的商品名  *******************************/
    public function getAssoCommodityname(Request $request){
        $data = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$data->phonenumber && $request->cookie('password')==$data->password){
            if($request->searchtext!=""){
                $data1 = DB::table('commodity'.$request->phonenumber)
                    ->where('name','like',$request->searchtext.'%')
                    ->select('name')->get()->toArray();
                // $data1
                return response()->json(['get'=>1,'textarray'=>$data1]);
            }
        }
        return response()->json(['get'=>0]); // 0为登录失效
    }
    /*****************************  修改商品数据  ********************************/
    public function updateCommoditydata(Request $request){
        $data = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$data->phonenumber && $request->cookie('password')==$data->password){
            $rules = [
                'name' => ['required','string','min:1','max:30'],
                'smallunit' => ['required','string','min:1','max:1'],
                'bigunit' => ['required','string','min:1','max:1'],
                'bigtosmall_specs' => ['required','integer','min:1','max:999999999']
            ];
            $validator = validator()->make($request->all(),$rules);
            if ($validator->fails()) {
                return response()->json(['update'=>1]); // 1为输入不合法
            }else{
                $commodity = DB::table('commodity'.$request->phonenumber)->where('id','=',$request->id);
                $commodity->update([
                    'name' => $request->name,
                    'smallunit' => $request->smallunit,
                    'bigunit' => $request->bigunit,
                    'bigtosmall_specs' => $request->bigtosmall_specs,
                ]);
                return response()->json(['update'=>2]); // 2为修改成功
            }
        }
        return response()->json(['update'=>0]); // 0为登录失效
    }
    /***************************  删除商品数据  *********************************/
    public function delCommoditydata(Request $request){
        $data = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$data->phonenumber && $request->cookie('password')==$data->password){
            $commodity = DB::table('commodity'.$request->phonenumber)->where('id','=',$request->removeCommodityid);
            $commodity->delete();
            return response()->json(['del'=>1]);
        }
        return response()->json(['del'=>0]);
    }
    /***************************************************************************/



    /*****************************  插入商品数据  *******************************/
    public function updataAdminPhone($phonenumber,$adminphone){
        $data = User::find($phonenumber);
        $data->adminphone=$adminphone;
        $data->save();
    }
    public function getOnlyUserdata($phonenumber){
        // 如果不存在匹配的模型，则返回null
        $data = User::find($phonenumber);
        return $data;
    }
    public function getAllUserdata(){
        $data = User::all();
        return $data;
    }
    public function getOnlyAdminPhonedata($AdminPhone){
        $data = User::where('adminphone','=',$AdminPhone);
        return $data;
    }
}
