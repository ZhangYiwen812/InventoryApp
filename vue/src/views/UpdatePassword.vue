<template>
  <div>
  <el-form class="updatePassword-box" label-width="80px">
    <h3 class="updatePassword-title">请按步骤修改密码</h3>
    <el-steps :active="activeindex" :space="200" finish-status="success" align-center>
      <el-step title="步骤1" description="输入账号"/>
      <el-step title="步骤2" description="修改密码"/>
      <el-step title="步骤3" description="完成"/>
    </el-steps>

    <el-tabs class="tabs-box" v-model="activeindex" >
      <div v-show="activeindex==1" v-on:keyup.enter="nextStep()">
        <el-form-item label="手机号">
              <el-input type="text" placeholder="请输入手机号" v-model="form.phonenumber"/>
        </el-form-item>
      </div>
      <div v-show="activeindex==2" v-on:keyup.enter="nextStep()">
        <el-form-item label="手机号">
              <el-input type="text" disabled="disabled" v-model="form.phonenumber"/>
        </el-form-item>
        <el-form-item label="邮箱">
              <el-input type="text" disabled="disabled" v-model="form.email"/>
        </el-form-item>
        <el-form-item label="密码">
              <el-input type="password" placeholder="请输入密码" v-model="form.newpassword1"/>
        </el-form-item>
        <el-form-item label="确认密码">
              <el-input type="password" placeholder="请在次输入密码" v-model="form.newpassword2"/>
        </el-form-item>
        <el-form-item class="verifyform" label="邮箱验证">
              <el-input class="verifytext" type="text" placeholder="请输入邮箱验证码" v-model="form.validcode"/>
              <el-button type="primary" :disabled="emailButtondisabled" v-on:click="sendEmailvalid()" >{{emailButtontext}}</el-button>
        </el-form-item>
        <el-form-item class="verifyform" label="验证码">
              <el-input class="verifytext" type="text" placeholder="请输入验证码" v-model="form.captcha"/>
              <a class="verifyimg" @click="updateImgurl()">
                <img class="verifyimg" v-bind:src="form.imgurl" />
              </a>
        </el-form-item>
      </div>
      <div v-show="activeindex==3">
        <el-form ref="loginForm" label-width="80px" class="login-box">
          <h3 class="login-title">密码修改成功，{{delayedtimeComplete}}秒后跳转到登录页面</h3>
          <el-button type="primary" v-on:click="toLogin()">跳转到登录页面</el-button>
        </el-form>
      </div>
    </el-tabs>
    <el-button v-if="compbtn" type="primary" style="margin-bottom:50px;" v-on:click="nextStep()">确认</el-button>
    <el-button v-if="uppassbtn" style="margin-bottom:50px;" v-on:click="toLogin()">返回登录页面</el-button>
  </el-form>

  <el-dialog title="温馨提示" v-model="dialogVisible" width="30%">
        <span>{{dialogtext}}</span><br>
        <span v-show="textVisible">密码不小于8位字符且不大于20位字符。<br></span><br>
        <span class="dialog-footer">
        <el-button type="primary" @click="dialogVisible = false">确 定</el-button>
        </span>
    </el-dialog>
  </div>
</template>

<script>
  import axios from '../http';

  export default {
    name: "UpdatePassword",
    components: {  },
    data() {
      return {
        activeindex: 1,
        form: {
          phonenumber: '', email: '',
          newpassword1: '' ,newpassword2: '',
          validcode: '', captcha: '',
          imgurl: '', key: ''
        },
        emailButtontext: '发送验证码',emailButtondisabled: false,
        timer: '',delayedtimeVerif: 60,delayedtimeComplete: 5,
        dialogtext: '',textVisible: false,dialogVisible: false,
        uppassbtn: true,compbtn: true
      }
    },
    computed: {},
    mounted: function(){
      // 进入页面刷新验证码
      // this.updateImgurl();
    },
    methods: {
      /************************  刷新验证码  **************************/
      updateImgurl(){
        let that = this;
        axios.get("/api/captcha/api/math").then(function(response){
          console.log(response);
          that.form.imgurl = response.data.img;
          that.form.key = response.data.key
        },function(err){console.log('获取验证码失败');});
      },
      /*****************  设置修改密码的用户并返回email  ******************/
      setUpdataPasswordUser(){
        let that = this;
        axios.post("/api/api/set_updata_password_user",{phonenumber: that.form.phonenumber}).then(function(response){
          if(response.data.setcomplete==1){
            that.form.email = response.data.email;
            that.uppassbtn=false;
            that.activeindex++;
            that.updateImgurl();
          }else if(response.data.setcomplete==0){
            that.dialogtext = '输入账号失败！';
            that.textVisible = false;
            that.dialogVisible = true;
          }
        },function(err){console.log('设置账号失败');});
      },
      /************************  发送邮箱验证码  *************************/
      sendEmailvalid(){
        let that = this;
        axios.post("/api/api/send_emailvalidcode",{phonenumber: that.form.phonenumber}).then(function(response){
          that.startTimerVerif();
          that.emailButtondisabled = true;
        },function(err){console.log('发送验证码失败');});
      },
      /********************  验证表单 使用post验证数据  *******************/
      verifForm(){
        let that = this;
        axios.post("/api/api/verif_form",
        {
          phonenumber: this.form.phonenumber,
          newpassword1: this.form.newpassword1,
          newpassword2: this.form.newpassword2,
          validcode: this.form.validcode,
          captcha: this.form.captcha,
          key: this.form.key,
        }).then(function(response){
          console.log(response.data);
          if(response.data.verif==4){
            console.log('修改成功');
            that.stopTimerVerif();
            that.compbtn=false;
            that.activeindex++;
            that.startTimerComplete();
          }else if(response.data.verif==3){
            that.dialogtext = '密码不合法！';
            that.textVisible = true;
            that.dialogVisible = true;
          }else if(response.data.verif==2){
            that.dialogtext = '邮箱验证错误！';
            that.textVisible = false;
            that.dialogVisible = true;
          }else if(response.data.verif==1){
            that.dialogtext = '验证码错误！';
            that.textVisible = false;
            that.dialogVisible = true;
            that.updateImgurl();
          }
        },function(err){console.log('验证失败');});
      },
      /**************************  步骤操作  **************************/
      nextStep(){
        if(this.activeindex==1){
          this.setUpdataPasswordUser();
        }else if(this.activeindex==2){
          this.verifForm();
        }
      },
      /************************  定时器方法  **************************/
      /********************  邮箱验证码 定时器  ***********************/
      // 定时器启动
      startTimerVerif(){
        this.timer = setInterval(this.valChangeVerif, 1000); 
      },
      // 定时器状态变更
      valChangeVerif() {
        console.log(this.delayedtimeVerif);
        if(this.delayedtimeVerif>0){
          this.emailButtontext=this.delayedtimeVerif+'s后获取';
          this.delayedtimeVerif--;
        }else{
          clearInterval(this.timer);
          this.emailButtontext='发送验证码';
          this.delayedtimeVerif=60;
          this.emailButtondisabled = false;
        }
      },
      // 定时器停止
      stopTimerVerif() {
        clearInterval(this.timer);
        this.delayedtimeVerif=60;
      },
      /*********************  完成步骤 定时器  ***********************/
      // 定时器启动
      startTimerComplete(){
        this.timer = setInterval(this.valChangeComplete, 1000); 
      },
      // 定时器状态变更
      valChangeComplete() {
        if(this.delayedtimeComplete>0){
          this.delayedtimeComplete--;
        }else{
          clearInterval(this.timer);
          this.$router.push("/");
        }
      },
      // 定时器停止
      stopTimerComplete() {
        clearInterval(this.timer);
        this.delayedtimeComplete=5;
      },
      /*********************  去往登陆页面  **************************/
      toLogin(){
        this.$router.push("/");
      }
      /**************************************************************/
    }
  }
</script>

<style scoped>
  .verifyform {
    width: 350px;
    height: 32px;
  }
  .verifytext {
    float: left;
    display: inline;
    width: 160px;
    height: 32px;
    margin-right: 5px;
  }
  .verifyimg {
    display: inline;
    width: 100px;
    height: 36px;
  }
  .updatePassword-box {
    border: 1px solid #DCDFE6;
    width: 600px;
    margin: 180px auto;
    padding: 30px;
    border-radius: 5px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    box-shadow: 0 0 25px #909399;
  }
  .updatePassword-title {
    text-align: center;
    margin: 0 auto 40px auto;
    color: #303133;
  }
  .tabs-box {
    width: 350px;
    margin: 40px auto;
  }
</style>