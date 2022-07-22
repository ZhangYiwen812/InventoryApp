<?php

namespace App\Excelhandle;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Illuminate\Http\Request;

use Barryvdh\Debugbar\Facade as DebugBar;

class StockExport implements FromCollection,ShouldAutoSize
{
    public $request;
    //构造函数传值
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    //数组转集合
    public function collection()
    {
        return new Collection($this->createData());
    }
    //业务代码
    public function createData()
    {
        $sendphonenumber = $this->request->sendphonenumber;
        $orderidhasphone = $this->request->orderid.$sendphonenumber;
        $sumPhonenumberdatas = $this->request->sumPhonenumbers;
        // 计算哪些盘点表需要求和
        $sumPhonenumbers = [];
        foreach ($sumPhonenumberdatas as $sumPhonenumberdata){
            array_push($sumPhonenumbers,$sumPhonenumberdata['phonenumber']);
        }
        // 构建导出数据
        $iddataarray = DB::table('stockinfo'.$orderidhasphone)
                    ->select('id','name','smallunit','bigunit','bigtosmall_specs')
                    ->get()->toArray();
        $data=[['商品编号','商品名称','库存(小单位)','库存(按规格计数)','小单位','大单位','规格']];
        foreach($iddataarray as $iddata){
            $idsumAmount = DB::table('stock'.$orderidhasphone)
                        ->where('id',$iddata->id)
                        ->whereIn('whosestock',$sumPhonenumbers)
                        ->sum('smallunit_amount');
            $textAmountbig = intval($idsumAmount/$iddata->bigtosmall_specs);
            $textAmountsmall = $idsumAmount%$iddata->bigtosmall_specs;
            $textAmount = "";
            if($textAmountbig==0 && $textAmountsmall>=0){
                $textAmount = $textAmountsmall.$iddata->smallunit;
            }else if($textAmountbig>0 && $textAmountsmall==0){
                $textAmount = $textAmountbig.$iddata->bigunit;
            }else if($textAmountbig>0 && $textAmountsmall>0){
                $textAmount = $textAmountbig.$iddata->bigunit.
                            $textAmountsmall.$iddata->smallunit;
            }
            $textspecs = "1".$iddata->bigunit."=".
                        $iddata->bigtosmall_specs.$iddata->smallunit;
            array_push($data,[
                $iddata->id,$iddata->name,$idsumAmount,
                $textAmount,$iddata->smallunit,$iddata->bigunit,$textspecs
            ]);
        }
        // 更新状态为 盘点完成，已求和
        $iddataarray = DB::table('order')
                    ->where('orderid',$orderidhasphone)
                    ->whereIn('recphonenumber',$sumPhonenumbers)
                    ->update(['state' => 3]);
        return $data;
    }
}
