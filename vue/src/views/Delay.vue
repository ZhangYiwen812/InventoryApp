<template>
  <div>
    <el-form ref="loginForm" label-width="80px" class="login-box">
      <h3 class="login-title">账号注册成功，{{delayedtime}}秒后跳转到登录页面</h3>
      <el-button type="primary" v-on:click="toLogin()">跳转到登录页面</el-button>
    </el-form>
  </div>
</template>

<script>

  export default {
    name: "Delay",
    data() {
      return {
        timer: '',
        delayedtime: 5
      }
    },
    mounted: function(){
      // 进入页面启动定时器
      this.start();
    },
    methods: {
      /***********************  定时器方法  ************************/
      start(){
        // 注意: 第一个参数为方法名的时候不要加括号;
        this.timer = setInterval(this.valChange, 1000); 
      },
      valChange() {
        console.log(this.delayedtime);
        if(this.delayedtime>0){
          this.delayedtime--;
        }else{
          clearInterval(this.timer);
          this.$router.push("/");
        }
      },
      /*********************  去往登陆页面  *************************/
      toLogin() {
        clearInterval(this.timer);
        this.$router.push("/");
      }
      /*************************************************************/
    }
  }
</script>

<style lang="scss" scoped>
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
