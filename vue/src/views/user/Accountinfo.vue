<template>
  <div class="mainlayout">
    <el-form label-width="80px" >
      <div align="center" style="margin:20px;">账户信息</div>
      <el-form-item label="手机号">
        <el-input type="text" disabled="disabled" v-model="phonenumber"/>
      </el-form-item>
      <el-form-item label="姓名" class="editbox">
        <el-input type="text" disabled="disabled" v-model="name"/>
        <el-button class="editbutton" text="text" v-on:click="updateNamedialogVisible=true">修改</el-button>
      </el-form-item>
      <el-form-item label="邮箱">
        <el-input type="text" disabled="disabled" v-model="email"/>
      </el-form-item>
      <el-form-item label="管理账号">
        <el-input type="text" disabled="disabled" v-model="adminphone"/>
      </el-form-item>
      <el-form-item label="管理密匙" class="editbox">
        <el-input type="text" disabled="disabled" v-model="adminkey"/>
        <el-button class="editbutton" text="text" v-on:click="refreshKeydialogVisible=true">刷新</el-button>
      </el-form-item>
      <el-form-item label="会员状态" class="editbox">
        <el-input type="text" disabled="disabled" v-model="auth"/>
      </el-form-item>
      <el-form-item label="账号操作">
        <el-button type="danger" v-on:click="toDestroy()">销毁账号</el-button>
        <el-button type="danger" v-on:click="toReset()">初始化账号</el-button>
      </el-form-item>
    </el-form>

    <el-dialog title="请输入修改的姓名" v-model="updateNamedialogVisible" width="380px">
      <span>姓名不大于20位字符</span>
      <br><br>
      <el-input maxlength='20' style="width:220px;" type="text" v-model="newname"/>
      <br><br><br>
      <span class="dialog-footer">
        <el-button type="primary" @click="newname='';updateNamedialogVisible=false;">取 消</el-button>
        <el-button type="primary" @click="updateName()">确 定</el-button>
      </span>
    </el-dialog>

    <el-dialog title="确认刷新管理密匙" v-model="refreshKeydialogVisible" width="380px">
      <span>管理密匙刷新后请及时告知管理员！</span>
      <br><br><br>
      <span class="dialog-footer">
        <el-button type="primary" @click="refreshKeydialogVisible=false">取 消</el-button>
        <el-button type="primary" @click="refreshKey()">确 定</el-button>
      </span>
    </el-dialog>

    <el-dialog title="温馨提示" v-model="dialogVisible" width="380px">
        <span>{{dialogtext}}</span>
        <br><br><br>
        <span class="dialog-footer">
          <el-button type="primary" @click="dialogVisible=false">取 消</el-button>
          <el-button @click="Yes()">确 定</el-button>
        </span>
    </el-dialog>
  </div>
</template>

<script>
  import axios from '../../http';
  import { ElMessage } from 'element-plus';

  export default {
    name: 'Accountinfo',
    props: ['phonenumber'],
    data() {
      return {
        name: '',newname: '',
        email: '',adminphone: '',adminkey: '',auth: 0,
        dialogmode: true,//true为销毁账号，false为重置账号
        dialogtext: '',
        dialogVisible: false,
        updateNamedialogVisible: false,
        refreshKeydialogVisible: false
      }
    },
    computed:{},
    mounted: function(){
      /**************************  进入页面获取用户数据  ****************************/
      let that = this;
      axios.get(this.$store.state.url+"/api/userdb/get_only_userdata",{
        params:{
          phonenumber: that.phonenumber
        }
      })
      .then(function(response){
        if(response.data.getdata==1){
          console.log(response);
          that.name = response.data.data.name;
          that.email = response.data.data.email;
          that.adminphone = response.data.data.adminphone;
          that.adminkey = response.data.data.adminkey;
          switch (response.data.data.auth){
            case 0 : that.auth = '初级用户';break;
            case 1 : that.auth = '中级会员';break;
            case 2 : that.auth = '高级会员';break;
          }
        }else if(response.data.getdata==0){
          console.log('获取用户数据失败！');
          that.loginfail();
        }
      },function(err){console.log('获取用户数据错误！');});
    },
    methods: {
      /******************************  修改姓名  *******************************/
      updateName(){
        let that = this;
        axios.post(this.$store.state.url+"/api/userdb/updata_user_name",{phonenumber: that.phonenumber,newname: that.newname})
        .then(function(response){
          if(response.data.updata==2){
            that.name=that.newname;
            that.newname='';
            that.updateNamedialogVisible=false;
            ElMessage({
                showClose: true,
                message: '修改姓名成功',
                type: 'success',
            });
            console.log('修改姓名成功');
          }else if(response.data.updata==1){
            console.log('姓名验证不合法！');
            ElMessage({
                showClose: true,
                message: '姓名验证不合法！',
                type: 'error',
            });
          }else if(response.data.updata==0){
            console.log('修改姓名失败！');
            that.loginfail();
          }
        },function(err){console.log('修改姓名错误！');});
      },
      /*****************************  刷新管理密匙  *******************************/
      refreshKey(){
        let that = this;
        axios.post(this.$store.state.url+"/api/userdb/refresh_key",{phonenumber: that.phonenumber})
        .then(function(response){
          if(response.data.refresh==1){
            that.adminkey=response.data.data.adminkey;
            that.refreshKeydialogVisible=false;
            ElMessage({
                showClose: true,
                message: '刷新管理密匙成功',
                type: 'success',
            });
            console.log('刷新管理密匙成功');
          }else if(response.data.refresh==0){
            console.log('刷新管理密匙失败！');
            that.loginfail();
          }
        },function(err){console.log('刷新管理密匙错误！');});
      },
      /******************************  销毁账号  *******************************/
      onDestroy(){
        let that = this;
        axios.post(this.$store.state.url+'/api/userdb/destroy_userdata',{phonenumber: that.phonenumber}).then(function(response){
          if(response.data.destroy==1){
            ElMessage({
                showClose: true,
                message: '销毁成功！',
                type: 'success',
            });
            sessionStorage.removeItem("loginstate");
            that.$router.push("/");
          }else if(response.data.destroy==0){
            console.log('销毁账号失败！');
            that.loginfail();
          }
        },function(err){console.log('销毁账号错误！');})
      },
      /******************************  初始化账号  ******************************/
      onReset() {
        let that = this;
        axios.post(this.$store.state.url+'/api/userdb/reset_userdata',{phonenumber: that.phonenumber}).then(function(response){
          if(response.data.reset==1){
            ElMessage({
                showClose: true,
                message: '初始化成功，请重新登录！',
                type: 'success',
            });
            sessionStorage.removeItem("loginstate");
            that.$router.push("/");
          }else if(response.data.reset==0){
            console.log('初始化账号失败！');
            that.loginfail();
          }
        },function(err){console.log('初始化账号错误！');});
      },
      /****************************  销毁账号 设置  *****************************/
      toDestroy() {
        this.dialogtext='您正在销毁账号，如需使用本软件，需要再次注册！';
        this.dialogmode=true;
        this.dialogVisible=true;
      },
      /***************************  初始化账号 设置  ****************************/
      toReset() {
        this.dialogtext='您正在初始化账号，初始化将清空账号的历史数据！';
        this.dialogmode=false;
        this.dialogVisible=true;
      },
      /************************  根据对话框模式操作账号  *************************/
      Yes(){
        if(this.dialogmode){
          this.onDestroy();
          this.dialogVisible=false;
        } else {
          this.onReset();
          this.dialogVisible=false;
        }
      },
      /******************************  登录失效  ********************************/
      loginfail(){
        console.log('登录失效！');
        sessionStorage.removeItem("loginstate");
        this.$router.push("/");
        alert('登录失效！');
      },
      /**************************************************************************/
    },
  }
</script>

<style scoped>
.mainlayout {
  width: 360px;
  margin: 20px;
}
.editbox{
  width: 360px;
}
.el-input {
  width:220px;
}
.editbutton {
  width:50px;
}
.dialog-footer button:first-child {
  margin-right: 20px;
}
</style>
