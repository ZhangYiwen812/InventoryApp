<template>
  <div>
    <el-form ref="loginForm" label-width="80px" class="login-box" v-on:keyup.enter="onVerifyCaptchtoRegister()">
      <h3 class="login-title">注册账号</h3>
      <el-form-item label="手机号">
        <el-input type="text" placeholder="请输入手机号" v-model="form.phonenumber"/>
      </el-form-item>
      <el-form-item label="姓名">
        <el-input type="text" placeholder="请输入姓名" v-model="form.name"/>
      </el-form-item>
      <el-form-item label="邮箱">
        <el-input type="text" placeholder="请输入邮箱" v-model="form.email"/>
      </el-form-item>
      <el-form-item label="密码" class="itemcl">
        <el-input type="password" placeholder="请输入密码" v-model="form.password1"/>
        <label class="inputlabel">密码是字母、数字等，范围在8到20个字符之间。</label>
      </el-form-item>
      <el-form-item label="确认密码">
        <el-input type="password" placeholder="请再次输入密码" v-model="form.password2"/>
      </el-form-item>
      <el-form-item class="verifyform" label="验证码">
        <el-input class="verifytext" type="text" placeholder="请输入验证码" v-model="form.captcha"/>
        <a class="verifyimg" @click="updateImgurl()">
          <img class="verifyimg" v-bind:src="form.imgurl" />
        </a>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" v-on:click="onVerifyCaptchtoRegister()">注册</el-button>
        <el-button v-on:click="toLogin()">返回登录页面</el-button>
      </el-form-item>
    </el-form>

    <el-dialog title="温馨提示" v-model="dialogVisible" width="30%">
      <span>{{dialogtext}}</span><br>
      <span v-show="textVisible">手机号11位字符；姓名不大于10位字符；<br>密码是字母、数字等，范围在8到20个字符之间。<br></span><br>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="dialogVisible = false">确 定</el-button>
      </span>
    </el-dialog>
  </div>
</template>

<script>
  import axios from '../http';

  export default {
    name: "Register",
    data() {
      return {
        form: {
          phonenumber: '15050572717',
          name: '张译文',
          email: '812901456@qq.com',
          password1: '12345678',
          password2: '12345678',
          captcha: '',
          imgurl: '',
          key: ''
        },
        textVisible: false,
        dialogVisible: false
      }
    },

    mounted: function(){
      // 进入页面刷新验证码
      this.updateImgurl();
    },
    methods: {
      /*************************  刷新验证码  **************************/
      updateImgurl(){
        let that = this;
        axios.get("/api/captcha/api/math")
          .then(function(response){
            console.log(response);
            that.form.imgurl = response.data.img;
            that.form.key = response.data.key
          },function(err){console.log('获取验证码失败');}
        );
      },
      /*************************  验证后注册  **************************/
      onVerifyCaptchtoRegister(){
        let that = this;
        axios.get("/api/api/verif_captcha",
        {
          params:{
            captcha: that.form.captcha,
            key: that.form.key,
          }
        }).then(function(response){
            console.log(response.data);
            if(response.data.verif==1){
              that.onRegister();
            }else if(response.data.verif==0){
              that.dialogtext = '验证码错误！';
              that.textVisible = false;
              that.dialogVisible = true;
              that.updateImgurl();
            }
          },function(err){console.log('验证失败');}
        );
      },
      /*************************  注册账号  **************************/
      onRegister() {
        let that = this;
        axios.post("/api/api/verif_register"
          ,{
            phonenumber: that.form.phonenumber,
            name: that.form.name,
            email: that.form.email,
            password1: that.form.password1,
            password2: that.form.password2,
          }).then(function(response){
            if(response.data.insert==2){
              that.$router.push("/delay");
            }else if(response.data.insert==1){
              that.dialogtext = '该账号已注册！';
              that.textVisible = false;
              that.dialogVisible = true;
            }else if(response.data.insert==0){
              if(that.form.phonenumber!=""&&that.form.name!=""&&that.form.email!=""&&that.form.password1!=""&&that.form.password2!="") {
                that.dialogtext = '账号信息不合法！';
                that.textVisible = true;
                that.dialogVisible = true;
              } else{
                that.dialogtext = '请输入账号和密码';
                that.textVisible = false;
                that.dialogVisible = true;
              }
            }
          },function(err){console.log("err");}
        );
      },
      /***********************  去往登陆页面  *************************/
      toLogin(){
        this.$router.push("/");
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
  .itemcl {
    width: 350px;
    height: 32px;
    margin: 0px 0px 35px 0px;
  }
  .inputlabel {
    width: 280px;
    height: 32px;
    text-align: left;
    font-size: 10px;
    color: #CDCDCD;
  }
</style>
