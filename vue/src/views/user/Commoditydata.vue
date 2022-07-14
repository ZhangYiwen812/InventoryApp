<template>
  <div>
    <div class="title">商品数据</div>
      <div class="editlist">
        <el-button class="editbutton" type="primary" @click="insertUpdataOpen(true)">添加商品</el-button>
        <el-button class="editbutton" style="margin:0px 10px 0px 0px" type="primary" @click="selectionDialogVisible=true">删除所选商品</el-button>
        <el-button class="editbutton" style="margin:0px 10px 0px 0px" type="danger" @click="delAllDialogVisible=true">清除所有商品</el-button>
        <el-button class="editbutton" style="margin:0px 10px 0px 0px" @click="insertAllDialogVisible=true" >
          批量添加商品
        </el-button>

        <el-button class="editbutton"
            style="width:50px;float:right;margin: 0px 0px 0px 10px" 
            type="primary" @click="searchCommodity">搜索
        </el-button>
        <el-input 
          class="editbutton" 
          style="width:200px;height:35px;float:right;margin: 0px 0px 0px 0px" 
          type="search" 
          maxlength='20' 
          placeholder="输入编号或名称"
          v-model="searchtext"
          @keyup.enter="searchCommodity"
        />
      </div>
      
      <el-table style="width: 802px" :data="commodityData"
        border='border' 
        stripe='stripe'
        @selection-change="selectionChange">
        
        <el-table-column type="selection" width="54px" align="center"/>

        <el-table-column label="序号" type="index" width="60px" align="center">
          <template #default="scope" v-slot="{$index}">
            <div class="tablebox">
              <span style="margin:auto;">{{scope.$index+pageSize*(currentPage-1)+1}}</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="编号" width="120px" align="center">
          <template #default="scope">
            <div class="tablebox">
              <span style="margin:auto;">{{scope.row.id}}</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="名称" width="235px" align="center">
          <template #default="scope">
            <div class="tablebox">
              <span style="margin:auto;">{{scope.row.name}}</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="规格" width="175px" align="center">
          <template #default="scope">
            <div class="tablebox">
              <span style="margin:auto;">
                1{{scope.row.bigunit}}={{scope.row.bigtosmall_specs}}{{scope.row.smallunit}}
              </span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="操作" width="155px" align="center">
          <template #default="scope">
            <div class="tablebox">
              <el-button style="margin:auto;" size="small" type="" 
              @click="insertUpdataOpen(false,scope.row)">
                修改
              </el-button>
              <el-button style="margin:auto;" size="small" type="" 
              @click="delDialogVisible=true;removeCommodityid=scope.row.id">
                删除
              </el-button>
            </div>
          </template>
        </el-table-column>

      </el-table>

      <div style="display: inline-block">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :page-sizes="[5, 10, 25, 50, 200, 500]"
          :total="totalSize"
          :small="small"
          :disabled="disabled"
          :background="background"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="getData()"
          @current-change="getData()">
        </el-pagination>
      </div>
      
      <el-dialog v-bind:title="insertUpdataDgtext" v-model="insertUpdataDgVisible" width="380px">
          <div>
            <el-form label-width="70px">
              <el-form-item label="编号">
                <el-input type="text" maxlength='10' :disabled="insertUpdataDgModel" v-model="addupdataComm.id"/>
                <label class="inputlabel">编号是英文或数字，最大10个字符。</label>
              </el-form-item>
              <el-form-item label="名称">
                <el-input type="text" maxlength='30' v-model="addupdataComm.name"/>
                <label class="inputlabel">名称是汉字、英文或数字等，最大30个字符。</label>
              </el-form-item>
              <el-form-item label="规格">
                <el-dropdown>
                  <div class="guigebox">
                    <span>1&nbsp;</span>
                    <el-button class="guigebtn">{{addupdataComm.bigunit}}</el-button>
                    <span>&nbsp;=&nbsp;</span>
                  </div>
                  <template #dropdown>
                    <el-dropdown-menu>
                      <el-dropdown-item v-for="(item) in bigunits" @click="setBigunit(item)">
                        <span>{{item}}</span>
                      </el-dropdown-item>
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
                <el-input class="guigeinput" maxlength='9' type="text" v-model="addupdataComm.bigtosmall_specs"/>
                <el-dropdown>
                  <el-button class="guigebtn">{{addupdataComm.smallunit}}</el-button>
                  <template #dropdown>
                    <el-dropdown-menu>
                      <el-dropdown-item v-for="(item) in smallunits" @click="setSmallunit(item)">
                        <span>{{item}}</span>
                      </el-dropdown-item>
                    </el-dropdown-menu>
                  </template>
                </el-dropdown>
                <label class="inputlabel">单位：一个汉字，规格范围：最大9位数字。</label>
              </el-form-item>
            </el-form>
          </div>
          <br>
          <span class="dialog-footer">
            <el-button type="primary" @click="insertUpdataDgVisible = false">取 消</el-button>
            <el-button type="primary" @click="handleInsertUpdata()">确 定</el-button>
          </span>
      </el-dialog>

      <el-dialog title="温馨提示" v-model="selectionDialogVisible" width="380px">
          <span>是否删除所选商品？</span><br>
          <br><br>
          <span class="dialog-footer">
            <el-button type="primary" @click="selectionDialogVisible=false">取 消</el-button>
            <el-button type="primary" @click="handleSelectionDelete()">确 定</el-button>
          </span>
      </el-dialog>


      <el-dialog title="温馨提示" v-model="delAllDialogVisible" width="380px">
          <span>是否清除所有商品？</span><br>
          <br><br>
          <span class="dialog-footer">
            <el-button type="primary" @click="delAllDialogVisible=false">取 消</el-button>
            <el-button @click="handleAllDelete()">确 定</el-button>
          </span>
      </el-dialog>

      <el-dialog title="温馨提示" v-model="delDialogVisible" width="380px">
        <span>这个商品将从您的列表中删除！</span><br>
        <br><br>
        <span class="dialog-footer">
          <el-button type="primary" @click="delDialogVisible = false">取 消</el-button>
          <el-button type="primary" @click="handleDelete()">确 定</el-button>
        </span>
      </el-dialog>

    <el-dialog title="批量添加商品" v-model="insertAllDialogVisible" width="380px" destroy-on-close>
      <span>使用Excel批量添加商品，请先
        <el-link type="primary" href="/api/api/commoditydb/download_commodity_exampletable" target="_blank">
        下载样表
        </el-link>。
      </span>
      <br><br>
      <div>
        <input style="border: 1px solid;" type="file" @change="changefile"/>
      </div>
      <br><br>
      <span class="dialog-footer">
        <el-button type="primary" @click="insertAllDialogVisible=false">取 消</el-button>
        <el-button type="primary" @click="updataExcel">上 传</el-button>
      </span>
    </el-dialog>

    <el-dialog title="数据不合法或验证未通过" v-model="insertAllErrorsDialogVisible" width="480px">
      <span>请您仔细检查Excel文件，已为您罗列出错项。</span>
      <br><br>
      <div>
        <el-table :data="errorcominfos" style="overflow-y:scroll;width:802px;height:200px;font-size:10px" 
          border='border' stripe='stripe' >
          
          <el-table-column label="序号" type="index" width="55px" align="center">
            <template #default="scope" v-slot="{$index}">
              <div class="tablebox">
                <span style="margin:auto;">{{scope.$index+1}}</span>
              </div>
            </template>
          </el-table-column>

          <el-table-column label="编号" width="100px" align="center">
            <template #default="scope">
              <div class="tablebox">
                <span style="margin:auto;">{{scope.row.errorCom.id}}</span>
              </div>
            </template>
          </el-table-column>

          <el-table-column label="名称" width="120px" align="center">
            <template #default="scope">
              <div class="tablebox">
                <span style="margin:auto;">{{scope.row.errorCom.name}}</span>
              </div>
            </template>
          </el-table-column>

          <el-table-column label="原因" width="200px" align="center">
            <template #default="scope">
              <div class="tablebox">
                <span style="margin:auto;">{{scope.row.errorText}}</span>
              </div>
            </template>
          </el-table-column>

        </el-table>
      </div>
      <br><br>
      <span class="dialog-footer">
        <el-button type="primary" @click="insertAllErrorsDialogVisible=false">确 认</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script >
  import axios from '../../http';
  import { ElMessage } from 'element-plus';

  export default {
    name: 'Commoditydata',
    props: ['phonenumber'],
    components:{},
    data() {
      return {
        isVisable: true,

        commodityData:[], // 商品数据
        currentPage: 1, // 当前页
        pageSize: 50, // 每分页数量
        small: false, // 显示大小
        totalSize: 0, // 项目总数
        background: true, // 背景是否有框
        disabled: false, // 是否可用

        bigunits: ['箱','桶','包','盒','袋'],
        smallunits: ['袋','瓶','组','盒','支','罐','桶','套'],
        addupdataComm: {
          id: '',name: '',
          // smallunit_amount: 0,
          smallunit: '',bigunit: '',
          bigtosmall_specs: ''
        },
        formData: {},
        searchtext: '',
        removeCommodityid: '',
        delCommodityids: [],
        errorcominfos: [],

        insertUpdataDgtext: '',insertUpdataDgModel: true,insertUpdataDgVisible: false,
        selectionDialogVisible: false,
        delAllDialogVisible: false,
        delDialogVisible: false,
        insertAllDialogVisible: false,
        insertAllErrorsDialogVisible: false,
      }
    },
    // mounted() {
    created() {
      // 指定分页数量获取第一页数据
      this.getData();
    },
    methods: {
      /**********  根据手机号、搜索子串、分页数量、页码获取商品列表  ***********/
      getData(){
        let that = this;
        axios.get('/api/api/commoditydb/get_search_page_commoditydata',{
          params:{
            phonenumber: that.phonenumber,
            searchtext: that.searchtext,
            pageSize: that.pageSize,
            currentPage: that.currentPage,
          }
        }).then(function(response) {
          if(response.data.getdata==1 || response.data.getdata==2){
            console.log('获取数据成功');
            that.totalSize = response.data.data.total;
            that.commodityData=response.data.data.data;
          }else if(response.data.getdata==0){
            console.log('获取数据失败！');
            that.loginfail();
          }
        },function(err){console.log('获取数据错误！');});
      },
      /***************************  设置小单位  *****************************/
      setSmallunit(item){
        this.addupdataComm.smallunit=item;
      },
      /***************************  设置大单位  *****************************/
      setBigunit(item){
        this.addupdataComm.bigunit=item;
      },
      /*******************  设置添加或修改商品的打开模式  *********************/
      insertUpdataOpen(openmodel,comm){
        if(openmodel){
          // 添加模式
          this.addupdataComm.id='12345678';
          this.addupdataComm.name='杀毒矾100g';
          this.addupdataComm.smallunit_amount=0;
          this.addupdataComm.smallunit=this.smallunits[0];
          this.addupdataComm.bigunit=this.bigunits[0];
          this.addupdataComm.bigtosmall_specs='100';
          this.insertUpdataDgModel=false;
          this.insertUpdataDgtext='添加商品';
          this.insertUpdataDgVisible=true
        }else{
          // 修改模式
          this.addupdataComm.id=comm.id;
          this.addupdataComm.name=comm.name;
          this.addupdataComm.smallunit_amount=comm.smallunit_amount;
          this.addupdataComm.smallunit=comm.smallunit;
          this.addupdataComm.bigunit=comm.bigunit;
          this.addupdataComm.bigtosmall_specs=comm.bigtosmall_specs;
          this.insertUpdataDgModel=true;
          this.insertUpdataDgtext='修改商品信息';
          this.insertUpdataDgVisible=true
        }
      },
      /**********************  执行添加或修改商品  ***************************/
      handleInsertUpdata(){
        if(this.insertUpdataDgModel){
          this.handleUpdata();
        }else{
          this.handleInsert();
        }
      },
      /********************* 添加商品信息 添加更新对话框 **********************/
      handleInsert(){
        let that = this;
        axios.post('/api/api/commoditydb/insert_commoditydata',{
          phonenumber: that.phonenumber,
          id: that.addupdataComm.id, // 编号
          name: that.addupdataComm.name, // 名称
          smallunit: that.addupdataComm.smallunit, // 小单位
          bigunit: that.addupdataComm.bigunit, // 大单位
          bigtosmall_specs: that.addupdataComm.bigtosmall_specs, // 大单位到小单位数量
        }).then(function(response) {
          let insert = response.data.insert;
          if(insert==6){
            console.log('添加商品成功');
            console.log(response);
            that.addupdataComm.id='';
            that.addupdataComm.name='';
            that.addupdataComm.smallunit=that.smallunits[0];
            that.addupdataComm.bigunit=that.bigunits[0];
            that.addupdataComm.bigtosmall_specs='';
            that.insertUpdataDgVisible = false;
            ElMessage({
                showClose: true,
                message: '添加商品成功',
                type: 'success',
            });
            that.currentPage=1;
            that.getData();
          }else if(insert==5){
            console.log('重复添加商品！');
            ElMessage({
                showClose: true,
                message: '重复添加商品！',
                type: 'error',
            });
          }else if(insert==4){
            let whichMemberText=response.data.whichMemberText;
            console.log(whichMemberText);
            ElMessage({
                showClose: true,
                message: whichMemberText,
                type: 'error',
            });
          }else if(insert==3){
            let whichMemberText=response.data.whichMemberText;
            console.log(whichMemberText);
            ElMessage({
                showClose: true,
                message: whichMemberText,
                type: 'error',
            });
          }else if(insert==2){
            let whichMemberText=response.data.whichMemberText;
            console.log(whichMemberText);
            ElMessage({
                showClose: true,
                message: whichMemberText,
                type: 'error',
            });
          }else if(insert==1){
            console.log('输入不合法！');
            ElMessage({
                showClose: true,
                message: '输入不合法！',
                type: 'error',
            });
          }else if(insert==0){
            console.log('添加商品失败！');
            that.loginfail();
          }
        },function(err){console.log('添加商品错误！');});
      },
      /******************* 更新商品信息 添加更新对话框 ************************/
      handleUpdata(){
        let that = this;
        axios.post('/api/api/commoditydb/update_commoditydata',{
          phonenumber: that.phonenumber,
          id: that.addupdataComm.id, // 编号
          name: that.addupdataComm.name, // 名称
          smallunit: that.addupdataComm.smallunit, // 小单位
          bigunit: that.addupdataComm.bigunit, // 大单位
          bigtosmall_specs: that.addupdataComm.bigtosmall_specs, // 大单位到小单位数量
        }).then(function(response) {
          if(response.data.update==2){
            console.log('修改商品成功');
            that.addupdataComm.id='';
            that.addupdataComm.name='';
            that.addupdataComm.smallunit=that.smallunits[0];
            that.addupdataComm.bigunit=that.bigunits[0];
            that.addupdataComm.bigtosmall_specs='';
            that.insertUpdataDgVisible = false;
            ElMessage({
                showClose: true,
                message: '修改商品成功',
                type: 'success',
            });
            that.currentPage=1;
            that.getData();
          }else if(response.data.update==1){
            console.log('输入不合法！');
            ElMessage({
                showClose: true,
                message: '输入不合法！',
                type: 'error',
            });
          }else if(response.data.update==0){
            console.log('添加商品失败！');
            that.loginfail();
          }
        },function(err){console.log('添加商品错误！');});
      },
      /**********************  选择需要删除的商品  ***************************/
      selectionChange(selection){
        this.delCommodityids=selection;
      },
      /***********************  删除所选商品  ********************************/
      handleSelectionDelete(){
        if(this.delCommodityids.length==0){
          this.selectionDialogVisible=false;
          ElMessage({
                showClose: true,
                message: '无删除商品！',
                type: 'error'
            });
        }else{
          let that = this;
          axios.post('/api/api/commoditydb/del_selection_commoditydata',{
            phonenumber: that.phonenumber,
            delCommodityids: that.delCommodityids,
          }).then(function(response) {
            if(response.data.delSele==1){
              console.log('删除所选商品成功');
              that.selectionDialogVisible=false;
              ElMessage({
                  showClose: true,
                  message: '删除所选商品成功',
                  type: 'success',
              });
              that.currentPage=1;
              that.getData();
            }else if(response.data.delSele==0){
              console.log('删除所选商品失败！');
              that.loginfail();
            }
          },function(err){console.log('删除所选商品错误！');});
        }
      },
      /***********************  清除所有商品  ********************************/
      handleAllDelete(){
        let that = this;
        axios.post('/api/api/commoditydb/del_all_commoditydata',{
          phonenumber: that.phonenumber,
        }).then(function(response) {
          if(response.data.delAll==1){
            console.log('移除所有商品成功');
            that.delAllDialogVisible=false;
            ElMessage({
                showClose: true,
                message: '移除所有商品成功',
                type: 'success',
            });
            that.currentPage=1;
            that.getData();
          }else if(response.data.delAll==0){
            console.log('移除所有商品失败！');
            that.loginfail();
          }
        },function(err){console.log('移除所有商品错误！');});
      },
      /******************  获取Excel文件的表单信息  ***************************/
      changefile(event){
        let files = event.target.files[0];
        this.formData = new FormData();
        this.formData.append('phonenumber',this.phonenumber);
        this.formData.append('addCommoditysfile',files)
      },
      /**************  Excel批量添加商品数据 使用post传数组  *******************/
      updataExcel() {
        let that = this;
        axios.post('/api/api/commoditydb/insert_excel_all_commoditydata',
          that.formData,
          {headers: {'Content-Type': 'multipart/form-data'}}
        ).then(function(response) {
          if(response.data.insertExcelAll==7){
            console.log('批量添加成功');
            ElMessage({
              showClose: true,
              message: '批量添加成功',
              type: 'success',
            });
            that.currentPage=1;
            that.getData();
            that.insertAllDialogVisible=false;
          }else if(response.data.insertExcelAll==6){
            console.log('数据不合法或验证未通过');
            ElMessage({
              showClose: true,
              message: '数据不合法或验证未通过',
              type: 'error',
            });
            that.errorcominfos=response.data.errorcominfos;
            that.insertAllDialogVisible=false;
            that.insertAllErrorsDialogVisible=true;
          }else if(response.data.insertExcelAll==3){
            // 数据超额
            let whichMemberText=response.data.whichMemberText;
            console.log(whichMemberText);
            ElMessage({
              showClose: true,
              message: whichMemberText,
              type: 'error',
            });
            that.currentPage=1;
            that.getData();
            that.insertAllDialogVisible=false;
          }else if(response.data.insertExcelAll==2){
            console.log('文件上传失败！');
            ElMessage({
              showClose: true,
              message: '文件上传失败！',
              type: 'error',
            });
          }else if(response.data.insertExcelAll==1){
            console.log('文件不存在！');
            ElMessage({
              showClose: true,
              message: '文件不存在！',
              type: 'error',
            });
          }else if(response.data.insertExcelAll==0){
            console.log('批量添加失败！');
            that.loginfail();
          }
        },function(err){
          ElMessage({
            showClose: true,
            message: '批量添加错误！',
            type: 'error',
          });
          console.log('批量添加错误！');
        });
      },
      /****************************  删除商品  *******************************/
      handleDelete(){
        let that = this;
        axios.post('/api/api/commoditydb/del_commoditydata',{
          phonenumber: that.phonenumber,
          removeCommodityid: that.removeCommodityid
        }).then(function(response) {
           if(response.data.del==1){
            console.log('移除商品成功');
            that.removeCommodityid='';
            that.delDialogVisible=false;
            ElMessage({
                showClose: true,
                message: '移除商品成功',
                type: 'success',
            });
            that.currentPage=1;
            that.getData(); // 刷新列表
          }else if(response.data.del==0){
            console.log('移除商品失败！');
            ElMessage({
                showClose: true,
                message: '移除商品失败！',
                type: 'error',
            });
          }
        },function(err){console.log('移除商品错误！');});
      },
      /****************************  搜索商品  *****************************/
      searchCommodity(){
        this.getData();
      },
      /****************************  登录失效  *******************************/
      loginfail(){
        console.log('登录失效！');
        sessionStorage.removeItem("loginstate");
        this.$router.push("/");
        alert('登录失效！');
      },
      /***********************************************************************/
    }
  }
</script>

<style scoped>
.title {
  font-size: 18px;
  line-height: 35px;
  text-align: left;
  width: 801px;
  height: 35px;
  margin: 0px 0px 15px 0px;
  border-bottom: 1px solid var(--el-color-primary-light-3);
}
.editlist {
  float: left;
  width: 802px;
  height: 35px;
  margin: 8px 0px 8px 0px;
}
.editbutton {
  float: left;
  width: 115px;
  height: 35px;
  padding: 0px;
  margin: 0px 10px 0px 0px;
}
.el-table {
  border: 1px solid;
  border-radius: 5px;
}
.tablebox {
  display: flex;
  align-items: center;
}
.el-form {
  width: 320px;
  margin: 0px auto;
}
.el-form-item {
  width: 320px;
  height: 32px;
  margin: 0px auto 40px auto;
}
.el-input {
  width: 225px;
}
.inputlabel {
  width: 260px;
  height: 32px;
  text-align: left;
  font-size: 10px;
  color: #CDCDCD;
}
.guigebox {
  width: 60px;
  height: 32px;
  text-align: center;
  line-height: 32px;
}
.guigeinput {
  width: 133px;
}
.guigebtn {
  width: 32px;
  height: 32px;
}
.dialog-footer button:first-child {
  margin-right: 20px;
}
.el-pagination {
  margin:30px auto;
}
.el-link {
  display: inline-block;
  width: 50px;
  height: 27px;
  margin: 0px 10px 0px 0px;
  padding-top: 8px;
  font-size: 5px;
  text-align: center;
}
</style>
