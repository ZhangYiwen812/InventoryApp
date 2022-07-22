<?php

namespace App\Http\Controllers;

use App\admin\Order;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\admin\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Excelhandle\StockExport;

use Barryvdh\Debugbar\Facade as DebugBar;

class OrderDBController extends Controller
{
    /*****************************  获取订单列表  *******************************/
    public function getOrderList(Request $request){
        $data1 = User::find($request->sendphonenumber);
        if($request->cookie('phonenumber')==$data1->phonenumber && $request->cookie('password')==$data1->password){
            // 建立订单表
            if(!Schema::hasTable('order')){
                Schema::create('order', function (Blueprint $table) {
                    $table->string('orderid',27);
                    $table->string('sendphonenumber',11);
                    $table->string('recphonenumber',11);
                    $table->integer('state');
                    $table->integer('subtotal')->unsigned();
                });
            }
            $orderids=DB::table('order')->select('orderid')->orderBy('orderid', 'asc')->distinct()->get()->toArray();
            // DebugBar::log($orderids);
            $orderList = [];
            foreach($orderids as $orderid){
                /***********  计算提交时间  ***********/
                $orderid_subtime=substr($orderid->orderid,0,4)."年".
                                substr($orderid->orderid,4,2)."月".
                                substr($orderid->orderid,6,2)."日".
                                substr($orderid->orderid,8,2)."时".
                                substr($orderid->orderid,10,2)."分".
                                substr($orderid->orderid,12,2)."秒";
                /*************  计算状态  *************/
                $orderid_states=DB::table('order')->where('orderid','=',$orderid->orderid)->select('state')->get()->toArray();
                // 单个state 0-未盘点 1-正在盘点 2-盘点结束 3-已求和
                // DebugBar::log($orderid_states);
                $state0=[];$state1=[];$state2=[];$state3=[];
                foreach($orderid_states as $state){
                    switch($state->state){
                        case 0 :
                            array_push($state0,true);array_push($state1,false);
                            array_push($state2,false);array_push($state3,false);
                            break;
                        case 1 :
                            array_push($state0,false);array_push($state1,true);
                            array_push($state2,false);array_push($state3,false);
                            break;
                        case 2 :
                            array_push($state0,false);array_push($state1,false);
                            array_push($state2,true);array_push($state3,false);
                            break;
                        case 3 :
                            array_push($state0,false);array_push($state1,false);
                            array_push($state2,false);array_push($state3,true);
                            break;
                    }
                }
                $waitcount = false; // 有一些0-等待盘点 返回0
                $counting = false; // 所有都不是0 且有一些1-正在盘点 返回1
                $counted = false; // 所有都不是0、1 且有一些2-盘点结束，等待求和 返回2
                $summed = false; // 所有都不是0、1、2 且有一些是3且不全为3-正在求和 返回3
                $complete = true; // 所有是3-订单已完成 返回4
                $length = count($state0);
                for($i=0; $i<$length; $i++){
                    $waitcount = $waitcount || $state0[$i];
                    $counting = $counting || $state1[$i];
                    $counted = $counted || $state2[$i];
                    $summed = $summed || $state3[$i];
                    $complete = $complete && $state3[$i];
                }
                $summed = $summed && !$complete;
                $orderid_state = -1;
                $orderid_statetext = '待处理';
                // DebugBar::log($waitcount);DebugBar::log($counting);DebugBar::log($counted);DebugBar::log($summed);DebugBar::log($complete);
                if($waitcount){
                    $orderid_state = 0;
                    $orderid_statetext = '等待盘点';
                }else if($counting){
                    $orderid_state = 1;
                    $orderid_statetext = '正在盘点';
                }else if($counted){
                    $orderid_state = 2;
                    $orderid_statetext = '盘点结束，等待求和';
                }else if($summed){
                    $orderid_state = 3;
                    $orderid_statetext = '待求和';
                }else if($complete){
                    $orderid_state = 4;
                    $orderid_statetext = '订单已完成';
                }
                array_push($orderList,[
                    'orderid'=>substr($orderid->orderid,0,16),
                    'subtime' => $orderid_subtime,
                    'state' => $orderid_state,
                    'statetext' => $orderid_statetext,
                ]);
            }
            // DebugBar::log($orderList);
            return response()->json(['data'=>$orderList,'get'=>1]);
        }
        return response()->json(['get'=>0]);
    }
    /**********************  获取指定订单编号的盘点人员信息  ***********************/
    public function getOnlyOrderInfo(Request $request){
        // DebugBar::log($request->sendphonenumber.','.$request->orderid);
        $orderidhasphone = $request->orderid.$request->sendphonenumber;
        $myinfo = User::find($request->sendphonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            $recphonenumbers=DB::table('order')
                        ->where('orderid','=',$orderidhasphone)
                        ->where('sendphonenumber','=',$request->sendphonenumber)
                        ->get()->toArray();
            $stockPersons = [];
            $statetext = "";
            foreach ($recphonenumbers as $recphonenumberinfo){
                $recuser=User::find($recphonenumberinfo->recphonenumber);
                switch($recphonenumberinfo->state){
                    case 0 : $statetext="等待盘点"; break;
                    case 1 : $statetext="正在盘点"; break;
                    case 2 : $statetext="盘点结束，已提交"; break;
                    case 3 : $statetext="盘点完成，已求和"; break;
                    default : $statetext="待处理"; break;
                }
                array_push($stockPersons,[
                    'phonenumber' => $recuser->phonenumber,
                    'name' => $recuser->name,
                    'state' => $recphonenumberinfo->state,
                    'statetext' => $statetext,
                    'subtotal' => $recphonenumberinfo->subtotal
                ]);
            }
            return response()->json(['data'=>$stockPersons,'get'=>1]);
        }
        return response()->json(['get'=>0]);
    }
    /******************************  新建订单  *********************************/
    public function createOrder(Request $request){
        $sendphonenumber=$request->sendphonenumber;
        $myinfo = User::find($sendphonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            $recphonenumbers=$request->recphonenumbers;
            // 检查盘点员数量
            if(count($recphonenumbers)>0){
                // 获取本用户的订单数量再加一
                $ordernum = Order::where('sendphonenumber',$sendphonenumber)
                                ->select('orderid')->distinct()
                                ->get()->count()+1;
                // 检查订单的数量上限
                if($ordernum <= $myinfo->ordersmax){
                    // 订单的数量未超限制
                    $orderidhasphone=date('YmdHis').rand(10,99).$sendphonenumber; // 获取订单编号
                    // 建立盘点信息表(用于存储盘点商品的信息)
                    $commodityinfos='commodity'.$sendphonenumber;
                    $stockinfos='stockinfo'.$orderidhasphone;
                    DB::update("CREATE TABLE {$stockinfos} SELECT * FROM {$commodityinfos}");
                    // 建立盘点表(用于存储盘点员信息和盘点的商品数量)
                    Schema::create('stock'.$orderidhasphone, function (Blueprint $table) {
                        $table->string('id',10);
                        $table->biginteger('smallunit_amount')->unsigned()->default(0);;
                        $table->string('whosestock',11);
                        $table->integer('subnumber')->unsigned()->default(0);;
                    });
                    $commoditys = DB::table('commodity'.$sendphonenumber)->get()->toArray();
                    $orderdb = DB::table('order');
                    $orderdbdata = [];
                    $stockdb = DB::table('stock'.$orderidhasphone);
                    $stockdbdata = [];
                    // 向盘点表和订单表填充数据
                    foreach($recphonenumbers as $recphonenumber){
                        // 填充该批次某盘点员的所有商品的盘点信息
                        foreach($commoditys as $commodity){
                            array_push($stockdbdata,[
                                'id' => $commodity->id,
                                'smallunit_amount' => 0,
                                'whosestock' => $recphonenumber,
                                'subnumber' => 0  // 商品的提交编号
                            ]);
                        }
                        // 在订单表中添加该批次某盘点员的的订单
                        array_push($orderdbdata,[
                            'orderid' => $orderidhasphone,
                            'sendphonenumber' => $sendphonenumber,
                            'recphonenumber' => $recphonenumber,
                            'state' => 0,
                            'subtotal' => 0  // 盘点表提交总数
                        ]);
                    }
                    $stockdb->insert($stockdbdata);
                    $orderdb->insert($orderdbdata);
                    return response()->json(['create'=>5]); // 5-创建订单成功
                }else{
                    // 订单的数量超过限制
                    if($myinfo->auth == 0){
                        // 3-数据超额，请开通会员！
                        $whichMemberText='数据超额，请开通会员！';
                        return response()->json(['create'=>4,'whichMemberText'=>$whichMemberText]);
                    }else if($myinfo->auth == 1){
                        // 2-数据超额，请升级会员！
                        $whichMemberText='数据超额，请升级会员！';
                        return response()->json(['create'=>3,'whichMemberText'=>$whichMemberText]);
                    }else if($myinfo->auth == 2){
                        // 1-数据超额，最大10个哦！
                        $whichMemberText='数据超额，最大'.$myinfo->ordersmax.'个哦！';
                        return response()->json(['create'=>2,'whichMemberText'=>$whichMemberText]);
                    }
                }
            }else{
                return response()->json(['create'=>1]); // 1-创建订单失败，没有盘点员
            }
        }
        return response()->json(['create'=>0]);
    }
    /*****************************  删除订单  *********************************/
    public function delOrder(Request $request){
        $orderidhasphone = $request->orderid.$request->phonenumber;
        $myinfo = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            // 检查订单状态 必须都是状态3(订单完成，已求和)，才能删除
            $hasSomeStates = DB::table('order')
                            ->where('orderid',$orderidhasphone)
                            ->whereIn('state',[0,1,2])->count();
            // 验证所有盘点表是否都是状态3
            if($hasSomeStates==0){
                // 删除订单
                $orderiddb=DB::table('order')->where('orderid',$orderidhasphone);
                $orderiddb->delete();
                // 删除对应订单的盘点信息表和盘点表
                Schema::dropIfExists('stockinfo'.$orderidhasphone);
                Schema::dropIfExists('stock'.$orderidhasphone);
                return response()->json(['del'=>2]); // 2-所有盘点表都是状态3
            }else{
                return response()->json(['del'=>1]); // 1-有一些盘点表不是状态3
            }
            
        }
        return response()->json(['del'=>0]); // 0-登录失效
    }
    /**************************  强制删除订单  *********************************/
    public function delOrderMandatoryCode(Request $request){
        $orderidhasphone = $request->orderid.$request->phonenumber;
        $myinfo = User::find($request->phonenumber);
        if($request->cookie('phonenumber')==$myinfo->phonenumber && $request->cookie('password')==$myinfo->password){
            // 删除订单
            $orderiddb=DB::table('order')->where('orderid',$orderidhasphone);
            $orderiddb->delete();
            // 删除对应订单的盘点信息表和盘点表
            Schema::dropIfExists('stockinfo'.$orderidhasphone);
            Schema::dropIfExists('stock'.$orderidhasphone);
            return response()->json(['del'=>1]);
        }
        return response()->json(['del'=>0]);
    }
    /***************************  验证盘点状态  *******************************/
    public function verifStates(Request $request){
        $sendphonenumber = $request->sendphonenumber;
        $orderid = $request->orderid.$sendphonenumber;
        $sumPhonenumbers = $request->sumPhonenumbers;
        $data = User::find($sendphonenumber);
        if($request->cookie('phonenumber')==$data->phonenumber && $request->cookie('password')==$data->password){
            $states = true;
            foreach($sumPhonenumbers as $sumPhonenumber){
                $statedb = DB::table('order')
                            ->where('sendphonenumber','=',$sendphonenumber)
                            ->where('orderid','=',$orderid)
                            ->where('recphonenumber','=',$sumPhonenumber)
                            ->get()->toArray();
                if($statedb[0]->state!=2 && $statedb[0]->state!=3){
                    $states = false;
                    break;
                }
            }
            if($states){
                return response()->json(['verif'=>2]); // 2-验证成功
            }else{
                return response()->json(['verif'=>1]); // 1-验证失败
            }
        }
        return response()->json(['verif'=>0]); // 0-登陆超时
    }
    /************************  下载Excel库存数据  ******************************/
    public function downloadStock(Request $request){
        $pathToFile = "导出文件.xlsx";
        return Excel::download(new StockExport($request),$pathToFile);
    }
    /**************************************************************************/
}
