<template>
  <div>
    <div class="title">子账号管理</div>
      <div class="editlist">
        <el-button class="editbutton" style="margin:0px 10px 0px 0px" type="primary" @click="insertDialogVisible=true">添加账号</el-button>
        <el-button class="editbutton" style="margin:0px 10px 0px 0px" type="primary" @click="selectionDialogVisible=true">删除所选账号</el-button>
        <el-button class="editbutton" style="margin:0px 10px 0px 0px" type="danger" @click="delAllDialogVisible=true">清除所有账号</el-button>
        <el-button class="editbutton" style="margin:0px 10px 0px 0px" @click="insertAllDialogVisible=true" >
          批量添加账号
        </el-button>

        <el-button class="editbutton"
            style="width:50px;float:right;margin: 0px 0px 0px 10px" 
            type="primary" @click="searchUsers">搜索
        </el-button>
        <el-input 
          class="editbutton" 
          style="width:200px;height:35px;float:right;margin: 0px 0px 0px 0px" 
          type="search" 
          maxlength='30' 
          placeholder="输入手机号或姓名"
          v-model="searchtext"
          @keyup.enter="searchUsers"
        />
      </div>
      
      <el-table :data="adminData" v-loading="loading" style="width: 802px"
              border='border' stripe='stripe' @selection-change="selectionChange">

        <el-table-column type="selection" width="54px" align="center"/>
        
        <el-table-column label="序号" type="index" width="80px" align="center">
          <template #default="scope" v-slot="{$index}">
            <div class="tablebox">
              <span style="margin:auto;">{{scope.$index+pageSize*(currentPage-1)+1}}</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="手机号" width="240px" align="center">
          <template #default="scope">
            <div class="tablebox">
              <span style="margin:auto;">{{scope.row.phonenumber}}</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="姓名" width="200px" align="center">
          <template #default="scope">
            <div class="tablebox">
              <span style="margin:auto;">{{scope.row.name}}</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="操作" width="225px" align="center">
          <template #default="scope">
            <div class="tablebox">
              <el-button style="margin:auto;" size="small" type="" 
              @click="this.removePhonenumber=scope.row.phonenumber;delDialogVisible=true;">
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
          :page-sizes="[5, 10, 25, 50]"
          :total="totalSize"
          :small="small"
          :disabled="disabled"
          :background="background"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="getData()"
          @current-change="getData()">
        </el-pagination>
      </div>

      <el-dialog title="添加账号" v-model="insertDialogVisible" width="380px">
          <div>
            <el-form label-width="75px">
              <el-form-item label="手机号">
                <el-input type="text" maxlength='11' v-model="addPhonenumber"/>
              </el-form-item>
              <el-form-item label="管理密匙">
                <el-input type="text" maxlength='5' v-model="adminkey"/>
              </el-form-item>
            </el-form>
          </div>
          <br>
          <span class="dialog-footer">
            <el-button type="primary" @click="insertDialogVisible = false">取 消</el-button>
            <el-button type="primary" @click="handleInsert()">确 定</el-button>
          </span>
      </el-dialog>

      <el-dialog title="温馨提示" v-model="selectionDialogVisible" width="380px">
          <span>是否删除所选账号？</span><br>
          <br><br>
          <span class="dialog-footer">
            <el-button type="primary" @click="selectionDialogVisible=false">取 消</el-button>
            <el-button type="primary" @click="handleSelectionDelete()">确 定</el-button>
          </span>
      </el-dialog>

      <el-dialog title="温馨提示" v-model="delAllDialogVisible" width="380px">
          <span>是否清除所有账号？</span><br>
          <br><br>
          <span class="dialog-footer">
            <el-button type="primary" @click="delAllDialogVisible = false">取 消</el-button>
            <el-button @click="handleAllDelete()">确 定</el-button>
          </span>
      </el-dialog>

      <el-dialog title="批量添加账号" v-model="insertAllDialogVisible" width="380px">
          <span>使用Excel批量添加账号，请先
          <el-link type="primary" v-bind:href="userlinkurl" target="_blank">
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
          <el-button type="primary" @click="uploadExcel">上 传</el-button>
        </span>
      </el-dialog>

      <el-dialog title="数据不合法或验证未通过" v-model="insertAllErrorsDialogVisible" width="480px">
        <span>请您仔细检查Excel文件，已为您罗列出错项。</span>
        <br><br>
        <div>
          <el-table :data="erroruserinfos" style="overflow-y:scroll;height:200px;font-size:10px" 
            border='border' stripe='stripe'>
            
            <el-table-column label="序号" type="index" width="55px" align="center">
              <template #default="scope" v-slot="{$index}">
                <div class="tablebox">
                  <span style="margin:auto;">{{scope.$index+1}}</span>
                </div>
              </template>
            </el-table-column>

            <el-table-column label="手机号" width="100px" align="center">
              <template #default="scope">
                <div class="tablebox">
                  <span style="margin:auto;">{{scope.row.errorUser.phonenumber}}</span>
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

            <el-table-column label="管理密匙" width="80px" align="center">
              <template #default="scope">
                <div class="tablebox">
                  <span style="margin:auto;">{{scope.row.errorUser.adminkey}}</span>
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

      <el-dialog title="温馨提示" v-model="delDialogVisible" width="380px">
        <span>这个账号将从您的管理列表中移除！</span><br>
        <br><br>
        <span class="dialog-footer">
          <el-button type="primary" @click="delDialogVisible = false">取 消</el-button>
          <el-button type="primary" @click="handleDelete()">确 定</el-button>
        </span>
    </el-dialog>
  </div>
</template>

<script>
  import axios from '../../http';
  import { ElMessage } from 'element-plus';

  export default {
    name: 'Submanagement',
    props: ['phonenumber'],
    data() {
      return {
        loading: false,
        adminData:[], // 管理数据
        currentPage: 1, // 当前页
        pageSize: 10, // 每分页数量
        small: false, // 显示大小
        totalSize: 0, // 项目总数
        background: true, // 背景是否有框
        disabled: false, // 是否可用

        addPhonenumber: '',adminkey: '',
        removePhonenumbers: [],
        removePhonenumber: '',
        searchtext: '',
        erroruserinfos: [], //批量添加出错的用户信息

        insertDialogVisible: false,
        selectionDialogVisible: false,
        delAllDialogVisible: false,
        delDialogVisible: false,
        insertAllDialogVisible: false,userlinkurl: '',
        insertAllErrorsDialogVisible: false
      }
    },
    // mounted() {
    created() {
      // 指定分页数量获取第一页数据
      this.userlinkurl=this.$store.state.url+'/api/userdb/download_user_exampletable';
      this.getData();
    },
    methods: {
      /*************  搜索管理用户的用户名或手机号并分页获取  ****************/
      // (根据手机号、搜索子串、分页数量、页码获取用户列表)
      getData(){
        let that = this;
        axios.get(this.$store.state.url+'/api/userdb/get_search_page_admin_phonedata',{
          params:{
            phonenumber_AdminPhone: that.phonenumber,
            searchtext: that.searchtext,
            pageSize: that.pageSize,
            currentPage: that.currentPage
          }
        }).then(function(response) {
          if(response.data.getdata==1 || response.data.getdata==2){
            that.totalSize = response.data.data.total;
            that.adminData=response.data.data.data;
            console.log('获取数据成功');
          }else if(response.data.getdata==0){
            console.log('获取数据失败！');
            that.loginfail();
          }
        },function(err){console.log('获取数据错误！');});
      },
      /*************************  添加管理账号  ******************************/
      handleInsert(){
        if(this.addPhonenumber=="" || this.adminkey==""){
          ElMessage({
              showClose: true,
              message: '请填写信息！',
              type: 'error',
          });
        }else{
          let that = this;
          axios.post(this.$store.state.url+'/api/userdb/insert_admin_phone',{
            phonenumber: that.phonenumber,
            addPhonenumber: that.addPhonenumber,
            adminkey: that.adminkey,
          }).then(function(response) {
            if(response.data.insert==5){
              that.addPhonenumber='';
              that.adminkey='';
              that.insertDialogVisible = false;
              ElMessage({
                  showClose: true,
                  message: '添加账号成功',
                  type: 'success',
              });
              that.currentPage=1;
              that.getData();
              console.log('添加账号成功');
            }else if(response.data.insert==4){
              console.log('管理密匙错误！');
              ElMessage({
                  showClose: true,
                  message: '管理密匙错误！',
                  type: 'error',
              });
            }else if(response.data.insert==3){
              console.log('此账号已被管理，需要初始化！');
              ElMessage({
                  showClose: true,
                  message: '此账号已被管理，需要初始化！',
                  type: 'error',
              });
            }else if(response.data.insert==2){
              console.log('账号未注册！');
              ElMessage({
                  showClose: true,
                  message: '账号未注册！',
                  type: 'error',
              });
            }else if(response.data.insert==1){
              console.log('输入不合法！');
              ElMessage({
                  showClose: true,
                  message: '输入不合法！',
                  type: 'error',
              });
            }else if(response.data.insert==0){
              console.log('添加账号失败！');
              that.loginfail();
            }
          },function(err){console.log('添加账号错误！');});
        }
      },
      /***********************  选择需要删除的商品  ***************************/
      selectionChange(selection){
        this.removePhonenumbers=selection;
      },
      /*************************  删除所选账号  ******************************/
      handleSelectionDelete(){
          if(this.removePhonenumbers.length==0){
          this.selectionDialogVisible=false;
          ElMessage({
                showClose: true,
                message: '无删除账号！',
                type: 'error'
            });
        }else{
          let that = this;
          axios.post(this.$store.state.url+'/api/userdb/del_selection_admin_phone',{
            phonenumber: that.phonenumber,
            removePhonenumbers: that.removePhonenumbers,
          }).then(function(response) {
            if(response.data.delSele==1){
              console.log('删除所选账号成功');
              that.selectionDialogVisible=false;
              ElMessage({
                  showClose: true,
                  message: '删除所选账号成功',
                  type: 'success',
              });
              that.currentPage=1;
              that.getData();
            }else if(response.data.delSele==0){
              console.log('删除所选账号失败！');
              that.loginfail();
            }
          },function(err){console.log('删除所选账号错误！');});
        }
      },
      /*************************  清除所有账号  ******************************/
      handleAllDelete(){
        let that = this;
        axios.post(this.$store.state.url+'/api/userdb/del_all_admin_phone',{
          phonenumber: that.phonenumber,
        }).then(function(response) {
          if(response.data.delAll==1){
            that.delAllDialogVisible = false;
            ElMessage({
                showClose: true,
                message: '移除所有账号成功',
                type: 'success',
            });
            that.currentPage=1;
            that.getData();
            console.log('移除所有账号成功');
          }else if(response.data.delAll==0){
            console.log('移除所有账号失败！');
            that.loginfail();
          }
        },function(err){console.log('移除所有账号错误！');});
      },
      /******************  获取Excel文件的表单信息  ***************************/
      changefile(event){
        let files = event.target.files[0];
        this.formData = new FormData();
        this.formData.append('phonenumber',this.phonenumber);
        this.formData.append('addUsersfile',files);
      },
      /*************  Excel批量添加用户数据 使用post传数组  ********************/
      uploadExcel() {
        let that = this;
        axios.post(this.$store.state.url+'/api/userdb/insert_excel_all_userdata',
          that.formData,
          {headers: {'Content-Type': 'multipart/form-data'}}
        ).then(function(response) {
          console.log(response.data);
          let insertExcelAll = response.data.insertExcelAll;
          if(insertExcelAll==9){
            console.log('批量添加成功');
            ElMessage({
              showClose: true,
              message: '批量添加成功',
              type: 'success',
            });
            that.currentPage=1;
            that.getData();
            that.insertAllDialogVisible=false;
          }else if(insertExcelAll==8){
            console.log('数据不合法或验证未通过！');
            ElMessage({
              showClose: true,
              message: '数据不合法或验证未通过！',
              type: 'error',
            });
            that.erroruserinfos=response.data.erroruserinfos;
            that.insertAllDialogVisible=false;
            that.currentPage=1;
            that.getData();
            that.insertAllErrorsDialogVisible=true;
          }else if(insertExcelAll==3){
            let whichMemberText = response.data.whichMemberText;
            console.log(whichMemberText);
            ElMessage({
              showClose: true,
              message: whichMemberText,
              type: 'error',
            });
            that.insertAllDialogVisible=false;
          }else if(insertExcelAll==2){
            console.log('文件上传失败！');
            ElMessage({
              showClose: true,
              message: '文件上传失败！',
              type: 'error',
            });
            that.insertAllDialogVisible=false;
          }else if(insertExcelAll==1){
            console.log('文件不存在！');
            ElMessage({
              showClose: true,
              message: '文件不存在！',
              type: 'error',
            });
            that.insertAllDialogVisible=false;
          }else if(insertExcelAll==0){
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
      /*******************  删除指定账号 复位管理账号 **************************/
      handleDelete(){
        let that = this;
        axios.post(this.$store.state.url+'/api/userdb/del_admin_phone',{
          phonenumber: that.phonenumber,
          removePhonenumber: that.removePhonenumber
        }).then(function(response) {
          if(response.data.del==1){
            that.delDialogVisible = false
            ElMessage({
                showClose: true,
                message: '删除账号成功',
                type: 'success',
            });
            that.currentPage=1;
            that.getData();
            console.log('删除账号成功');
          }else if(response.data.del==0){
            console.log('删除账号失败！');
            that.loginfail();
          }
        },function(err){console.log('删除账号错误！');});
      },
      /*****************************  搜索用户  *******************************/
      searchUsers(){
        this.getData();
      },

      /*****************************  登录失效  *******************************/
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
  width: 801px;
  height: 35px;
  margin: 8px 0px 8px 0px;
}
.editbutton {
  float: left;
  width: 115px;
  height: 35px;
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
  width:300px;
  margin:0px auto;
}
.el-input {
  width:180px;
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
