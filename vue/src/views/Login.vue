<template>
  <div>
    <el-form ref="loginForm" label-width="80px" class="login-box" v-on:keyup.enter="onVerifyCaptchtoLogin()">
        <h3 class="login-title">欢迎使用柳文盘点系统Web管理端</h3>
        <el-form-item label="手机号">
        <el-input type="text" placeholder="请输入手机号" v-model="form.phonenumber"/>
        </el-form-item>
        <el-form-item label="密码">
        <el-input type="password" placeholder="请输入密码" v-model="form.password"/>
        </el-form-item>
        <el-form-item class="verifyform" label="验证码">
        <el-input class="verifytext" type="text" placeholder="请输入验证码" v-model="form.captcha"/>
        <a class="verifyimg" @click="updateImgurl()">
          <img class="verifyimg" v-bind:src="form.imgurl" />
        </a>
        </el-form-item>
        <el-form-item>
        <el-button type="primary" v-on:click="onLogin()" >登录</el-button>
        <el-button v-on:click="toRegister()">注册</el-button>
        <el-button v-on:click="toUpdatePassword()">找回密码</el-button>
        </el-form-item>
    </el-form>

    <el-dialog title="温馨提示" v-model="dialogVisible" width="30%">
        <span>{{dialogtext}}</span><br>
        <span v-show="textVisible">手机号11位字符，姓名不大于10位字符，<br>密码不小于8位字符且不大于20位字符。<br></span><br>
        <span class="dialog-footer">
        <el-button type="primary" @click="dialogVisible = false">确 定</el-button>
        </span>
    </el-dialog>
  </div>
</template>

<script>
  import axios from '../http';

  export default {
    name: "Login",
    components: {  },
    data() {
        return {
          form: {
            phonenumber: '15050572717',
            password: '12345678',
            captcha: '',imgurl: '',key: ''
          },
          dialogtext: '',
          textVisible: false,
          dialogVisible: false,
        }
    },
    computed: {},
    mounted(){
      // 进入页面刷新验证码
      this.updateImgurl();
      sessionStorage.removeItem("loginstate");
    },
    methods: {
      /**************************  刷新验证码  **************************/
      updateImgurl(){
        let that = this;
        axios.get(this.$store.state.url+"/captcha/api/math")
          .then(function(response){
              that.form.imgurl = response.data.img;
              that.form.key = response.data.key
          },function(err){console.log('获取验证码错误！');}
        );
      },
      /**************************  验证后登陆  **************************/
      onVerifyCaptchtoLogin(){
        let that = this;
        axios.get(this.$store.state.url+"/webapi/verif_captcha",
        {
          params:{
            captcha: that.form.captcha,
            key: that.form.key,
          }
        }).then(function(response){
              if(response.data.verif==1){
                that.onLogin();
              }else if(response.data.verif==0){
                that.dialogtext = '验证码错误！';
                that.textVisible = false;
                that.dialogVisible = true;
                that.updateImgurl();
              }
          },function(err){console.log('验证错误！');}
        );
      },
      /**************************  登陆账号  **************************/
      onLogin() {
        // console.log(this.form.phonenumber);
        // console.log(this.form.password);
        let that = this;
        axios.get(this.$store.state.url+"/webapi/verif_login",
        {
          params:{
            phonenumber: that.form.phonenumber,
            password: that.form.password
          }
        }).then(function(response){
            if(response.data.verif==-1){
              // 表单输入不合法
              if(that.form.phonenumber!="" && that.form.password!="") {
                that.dialogtext = '手机号或密码不合法！';
                that.textVisible = true;
                that.dialogVisible = true;
              } else {
                that.dialogtext = '请输入手机号和密码！';
                that.textVisible = false;
                that.dialogVisible = true;
              }
            }else if(response.data.verif==0){
              that.dialogtext = '该账号未注册！';
              that.textVisible = false;
              that.dialogVisible = true;
            }else if(response.data.verif==1){
              that.dialogtext = '密码错误！';
              that.textVisible = false;
              that.dialogVisible = true;
            }else if(response.data.verif==2){
              sessionStorage.setItem('loginstate','ok')
              that.$router.push("/main/"+that.form.phonenumber);
            }
          },function(err){console.log("登录错误！");}
        );
      },
      /***********************  去往注册页面  ************************/
      toRegister() {
        this.$router.push("/register");
      },
      /*********************  去往修改密码页面  ***********************/
      toUpdatePassword() {
        this.$router.push("/updatepassword");
      }
      /***************************************************************/
    }
  }
</script>

<style lang="scss" scoped>
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
  .login-box {
    border: 1px solid #DCDFE6;
    width: 350px;
    margin: 180px auto;
    padding: 35px 35px 15px 35px;
    border-radius: 5px;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    box-shadow: 0 0 25px #909399;
  }
  .login-title {
    text-align: center;
    margin: 0 auto 40px auto;
    color: #303133;
  }
</style>
