<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'webapi'],function () {
    /******************************  验证相关方法  ********************************/
    // 验证码验证
    Route::get('verif_captcha','VerifController@verifCaptcha');
    // 登录验证
    Route::get('verif_login','VerifController@verifLogin');
    // 退出登录
    Route::post('Log_out','VerifController@Logout');
    // *验证登录状态
    Route::post('verif_loginstate','VerifController@verifLoginstate');
    // 验证并注册
    Route::post('verif_register','VerifController@verifRegister');
    /****************************  找回密码相关方法  *******************************/
    // 设置修改密码的用户
    Route::post('set_updata_password_user','VerifController@setUpdataPasswordUser');
    // 发送验证码
    Route::post('send_emailvalidcode','VerifController@sendEmailvalidcode');
    // 验证表单
    Route::post('verif_form','VerifController@verifForm');

    /****************************  用户数据库操作  *********************************/
    // 注册用户
    Route::post('userdb/insert_userdata','UserDBController@insertUserdata');
    /* 登录后操作  */
    // 检查登录有效性
    Route::post('userdb/is_logon','UserDBController@isLogon');
    // 获取用户是否是管理用户
    Route::get('userdb/get_is_admin_user','UserDBController@getIsAdminUser');
    // 获取用户数据
    Route::get('userdb/get_only_userdata','UserDBController@getOnlyUserdata');
    // *获取指定管理用户的用户列表
    Route::post('userdb/get_only_admin_phonedata','UserDBController@getOnlyAdminPhonedata');
    // 获取指定管理用户的用户列表(适应穿梭框)
    Route::get('userdb/get_only_admin_phonedata_totransfer','UserDBController@getOnlyAdminPhonedataTotransfer');
    // 搜索管理用户的用户名或手机号并分页获取(根据手机号、搜索子串、分页数量、页码获取用户列表)
    Route::get('userdb/get_search_page_admin_phonedata','UserDBController@getSearchPageAdminPhonedata');
    // *修改密码
    Route::post('userdb/updata_user_password','UserDBController@updataUserPassword');
    // 修改姓名
    Route::post('userdb/updata_user_name','UserDBController@updataUserName');
    // 刷新管理密匙
    Route::post('userdb/refresh_key','UserDBController@refreshKey');
    // 销毁账号
    Route::post('userdb/destroy_userdata','UserDBController@destroyUserdata');
    // 初始化账号
    Route::post('userdb/reset_userdata','UserDBController@resetUserdata');
    // 添加管理手机号
    Route::post('userdb/insert_admin_phone','UserDBController@insertAdminPhone');
    // 清除所有受管理的手机号
    Route::post('userdb/del_all_admin_phone','UserDBController@delAllAdminPhone');
    // 删除所选的受管理的账号
    Route::post('userdb/del_selection_admin_phone','UserDBController@delSelectionAdminPhone');
    // 删除账号 复位管理账号
    Route::post('userdb/del_admin_phone','UserDBController@delAdminPhone');
    // Excel批量添加用户数据 使用post传数组
    Route::post('userdb/insert_excel_all_userdata','UserDBController@insertExcelAllUserdata');
    // 下载账号样表
    Route::get('userdb/download_user_exampletable','UserDBController@downloadUserExampletable');

    /***************************  商品数据操作  ***********************************/
    // 创建数据表
    Route::post('commoditydb/create_commoditytable','CommodityDBController@createCommoditytable');
    // 删除数据表
    Route::post('commoditydb/drop_commoditytable','CommodityDBController@dropCommoditytable');
    // 分页获取并搜索商品数据
    Route::get('commoditydb/get_search_page_commoditydata','CommodityDBController@getSearchPageCommoditydata');
    // *获取商品数据
    Route::post('commoditydb/get_commoditydata','CommodityDBController@getCommoditydata');
    // 添加商品数据
    Route::post('commoditydb/insert_commoditydata','CommodityDBController@insertCommoditydata');
    // 删除所选商品数据
    Route::post('commoditydb/del_selection_commoditydata','CommodityDBController@delSelectionCommoditydata');
    // 清除所有商品数据
    Route::post('commoditydb/del_all_commoditydata','CommodityDBController@delAllCommoditydata');
    // Excel批量添加商品数据 使用post传数组
    Route::post('commoditydb/insert_excel_all_commoditydata','CommodityDBController@insertExcelAllCommoditydata');
    // 下载商品样表
    Route::get('commoditydb/download_commodity_exampletable','CommodityDBController@downloadCommodityExampletable');
    // *获取联想的商品名
    Route::post('commoditydb/get_asso_commodityname','CommodityDBController@getAssoCommodityname');
    // 修改商品数据
    Route::post('commoditydb/update_commoditydata','CommodityDBController@updateCommoditydata');
    // 删除商品数据
    Route::post('commoditydb/del_commoditydata','CommodityDBController@delCommoditydata');

    /**************************  订单数据操作  ***********************************/
    // 获取订单列表
    Route::get('orderdb/get_order_list','OrderDBController@getOrderList');
    // 获取指定订单编号的盘点人员信息
    Route::get('orderdb/get_only_order_info','OrderDBController@getOnlyOrderInfo');
    // 创建订单
    Route::post('orderdb/create_order','OrderDBController@createOrder');
    // 删除订单
    Route::post('orderdb/del_order','OrderDBController@delOrder');
    // 强制删除订单
    Route::post('orderdb/del_order_mandatory_code','OrderDBController@delOrderMandatoryCode');
    // 验证盘点状态
    Route::post('orderdb/verif_states','OrderDBController@verifStates');
    // 下载Excel库存数据 使用post传数组
    Route::post('orderdb/download_stock','OrderDBController@downloadStock');
});

// Route::post('/userdb/get_only_userdata','UserDBController@getOnlyUserdata');
// Route::post('/api/valid_login','ValidController@validLogin');

// Route::get('/api/valid_login','ValidController@validLogin');
Route::any('/api/test','VerifController@test');
Route::get('/api/test2','VerifController@test2');

Route::get('userdb/test','UserDBController@test');
Route::get('userdb/test2','UserDBController@test2');
