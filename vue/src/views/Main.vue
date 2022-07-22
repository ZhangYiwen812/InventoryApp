<template>
  <div>
    <el-header>
      <span class="headh">欢迎使用柳文盘点系统Web管理端</span>
      <div class="toolbar">
        <div align="center" style="width:175px;height:60px;line-height:60px">
          <span>亲爱的 {{name}} 您好！</span>
        </div>
        
        <el-button class="headbutton" @click="Loginout()">退出登录</el-button>
      </div>
    </el-header>

    <el-container>
      <el-aside width="200px">
        <el-menu :default-openeds="['1','2']">
          <el-sub-menu index="1" >
            <template #title>
              <el-icon><message /></el-icon>用户信息详情
            </template>

              <el-menu-item index="1-1" >
                <router-link :to="{name:'Accountinfo',params:{phonenumber: phonenumber}}">
                  账户信息
                </router-link>
              </el-menu-item>
              <el-menu-item index="1-2" v-if="isAdmin">
                <router-link :to="{name:'Submanagement',params:{phonenumber: phonenumber }}">
                  子账号管理
                </router-link>
              </el-menu-item>

          </el-sub-menu>

          <el-sub-menu index="2" v-if="isAdmin">
            <template #title>
              <el-icon><setting /></el-icon>盘点流程管理
            </template>

              <el-menu-item index="2-1">
                <router-link :to="{name:'Commoditydata',params:{phonenumber: phonenumber}}">
                  商品数据
                </router-link>
              </el-menu-item>

              <el-menu-item index="2-2">
                <router-link :to="{name:'Inventoryorder',params:{phonenumber: phonenumber}}">
                  盘点订单
                </router-link>
              </el-menu-item>

          </el-sub-menu>

          <!-- <el-menu-item>
            <router-link>关于我们</router-link>
          </el-menu-item> -->

        </el-menu>
      </el-aside>

      <el-main>
          <router-view></router-view>
      </el-main>
    </el-container>

    <el-dialog title="温馨提示" v-model="tipdialogVisible" width="30%">
        <span>此账号已被手机尾号为{{adminphone}}的账号管理，<br>如需恢复请点击“</span>
          <el-button @click="tipdialogVisible=false;resetdialogVisible=true">初始化账号</el-button>
        <span>”。</span>
        <br><br><br>
        <span class="dialog-footer">
          <el-button type="primary" @click="tipdialogVisible=false">确 认</el-button>
        </span>
    </el-dialog>

    <el-dialog title="温馨提示" v-model="resetdialogVisible" width="30%">
        <span>您正在初始化账号，初始化将脱离管理账号，<br>并清空账号的历史数据！</span>
        <br><br><br>
        <span class="dialog-footer">
          <el-button @click="onReset()">确 定</el-button>
          <el-button type="primary" @click="resetdialogVisible=false">取 消</el-button>
        </span>
    </el-dialog>
  </div>
</template>

<script>
  import { Menu as IconMenu, Message, Setting } from '@element-plus/icons-vue';
  import axios from '../http';
  import { ElMessage } from 'element-plus';

  export default {
    name: 'Main',
    props: ['phonenumber'],
    components: { IconMenu,Message,Setting },
    data() {
      return {
        name: '',
        isAdmin: false,
        adminphone: '',
        tipdialogVisible: false,
        resetdialogVisible: false,
      }
    },
    computed: {//计算属性什么时候执行：初始化显示 相关的data属性发生变化
    },
    // mounted() {
    created() {
      this.isAdminUser();
    },
    methods: {
      /********************  进入页面确认是否是管理用户  ************************/
      isAdminUser(){
        let that = this;
        axios.get(this.$store.state.url+"/webapi/userdb/get_is_admin_user",{
          params:{
            phonenumber: that.phonenumber
          }
        })
        .then(function(response){
          if(response.data.is==2){
            console.log('用户是管理用户');
            that.isAdmin=true;
            that.name=response.data.name;
          }else if(response.data.is==1){
            console.log('用户是被管理用户');
            that.isAdmin=false;
            that.adminphone=response.data.adminphone;
            that.tipdialogVisible=true;
          }else if(response.data.is==0){
            console.log('获取用户数据失败！');
            that.loginfail();
          }
        },function(err){console.log('获取用户数据错误！');});
      },
      /******************************  初始化账号  ******************************/
      onReset() {
        let that = this;
        axios.post(this.$store.state.url+'/webapi/userdb/reset_userdata',{phonenumber: that.phonenumber}).then(function(response){
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
        },function(err){console.log('初始化账号错误！');})
      },
      /***************************  退出登录  ******************************/
      Loginout(){
        // 删除cookie
        let that = this;
        axios.post(this.$store.state.url+'/webapi/Log_out',{
          phonenumber: that.phonenumber
        }).then(function(response){
          if(response.data.Logout==1){
            ElMessage({
                showClose: true,
                message: '已退出登录',
                type: 'success',
            });
            console.log('已退出登录');
          }else if(response.data.Logout==0){
            console.log('退出登录失败！');
            that.loginfail();
          }
        },function(err){console.log('退出登录错误！');})
        localStorage.removeItem("loginstate");
        this.$router.push("/");
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
.el-header {
  height: 61px;
  padding: 0px 20px 0px 20px;
  border-bottom: 1px solid var(--el-color-primary-light-3);
  text-align: right;
  font-size: 18px;
}
.headh {
  width: 275px;
  height: 26px;
  margin: 17px 0px 17px 0px;
  float: left;
}
.toolbar {
  display: inline-flex;
  width: 260px;
  height: 60px;
  align-items: center;
}
.headbutton {
  width: 75px;
  height: 55px;
  padding: 0px;
  margin: 0px;
}
.el-container {
  float: left;
  min-height: 1000px;
}
.el-aside {
  float: left;
  width: 221px;
  overflow: visible;
}
.el-main {
  width: 862px;
  padding: 30px;
  border-left: 1px solid var(--el-color-primary-light-3);
}

</style>
