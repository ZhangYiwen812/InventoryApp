<template>
  <div>
    <el-header>
      <span class="headh">柳文盘点系统会员支付页面</span>
      <div class="toolbar">
        <div align="center" style="width:175px;height:60px;line-height:60px">
          <span>亲爱的 {{name}} 您好！</span>
        </div>
        
        <el-button class="headbutton" @click="toMain()">回到主页</el-button>
        <el-button class="headbutton" @click="Loginout()">退出登录</el-button>
      </div>
    </el-header>

    <div>
      <table class="tableclas" align="center">
        <tr>
          <th>项目\权限</th>
          <th>初级用户</th>
          <th>中级会员</th>
          <th>高级会员</th>
        </tr>

        <tr>
          <td>子账号上限人数5人</td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
          <td style="color:red"><el-icon><Close /></el-icon></td>
          <td style="color:red"><el-icon><Close /></el-icon></td>
        </tr>
        <tr>
          <td>子账号上限人数20人</td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
          <td style="color:red"><el-icon><Close /></el-icon></td>
        </tr>
        <tr>
          <td>子账号上限人数50人</td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
        </tr>

        <tr>
          <td>商品上限数量50个</td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
          <td style="color:red"><el-icon><Close /></el-icon></td>
          <td style="color:red"><el-icon><Close /></el-icon></td>
        </tr>
        <tr>
          <td>商品上限数量400个</td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
          <td style="color:red"><el-icon><Close /></el-icon></td>
        </tr>
        <tr>
          <td>商品上限数量1000个</td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
        </tr>

        <tr>
          <td>订单上限数量1个</td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
          <td style="color:red"><el-icon><Close /></el-icon></td>
          <td style="color:red"><el-icon><Close /></el-icon></td>
        </tr>
        <tr>
          <td>订单上限数量4个</td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
          <td style="color:red"><el-icon><Close /></el-icon></td>
        </tr>
        <tr>
          <td>订单上限数量10个</td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
          <td style="color:green"><el-icon><Check /></el-icon></td>
        </tr>

        <tr>
          <td>价格</td><td>无</td><td>49元</td><td>89元</td>
        </tr>
        <tr>
          <td>支付状态</td>
          <td>已拥有</td>
          <td v-if="!hasmidAuth">
            <el-button type="primary" @click="becomeMemberPay(1)">成为中级会员</el-button>
          </td>
          <td v-if="hasmidAuth">已拥有</td>

          <td v-if="!hassenAuth">
            <el-button type="primary" @click="becomeMemberPay(2)">成为高级会员</el-button>
          </td>
          <td v-if="hassenAuth">已拥有</td>

        </tr>
      </table>
    </div>

  </div>
</template>

<script>
import axios from '../../http';
import { ElMessage } from 'element-plus';

export default {
    name: "Payformember",
    props: ['phonenumber'],
    data() {
      return {
        name: '',
        userAuth: 0,
        hasmidAuth: false,
        hassenAuth: false
      }
    },
    created() {
      this.getUserAuth();
    },
    methods: {
      /************************  获取用户的权限  **************************/
      getUserAuth(){
        let that = this;
        axios.get("/api/api/userdb/get_only_userdata",{
          params:{
            phonenumber: that.phonenumber
          }
        })
        .then(function(response){
          if(response.data.getdata==1){
            console.log('获取用户的权限成功');
            that.name=response.data.data.name;
            that.userAuth=response.data.data.auth;
            switch(that.userAuth){
              case 0:that.hasmidAuth=false;that.hassenAuth=false;break;
              case 1:that.hasmidAuth=true;that.hassenAuth=false;break;
              case 2:that.hasmidAuth=true;that.hassenAuth=true;break;
              default:that.hasmidAuth=false;that.hassenAuth=false;break;
            }
          }else if(response.data.getdata==0){
            console.log('获取用户的权限失败！');
            that.loginfail();
          }
        },function(err){console.log('获取用户数据错误！');});
      },
      /************************  *去往会员支付页面  *************************/
      becomeMemberPay(authnum){
        let that = this;
        axios.post("/api/api/userdb/pay/",{
          phonenumber: that.phonenumber,
          auth: authnum
        }).then(function(response){
          if(response.data.ispay==1){


            console.log('支付成功');
          }else if(response.data.ispay==0){
            console.log('支付失败！');
            that.loginfail();
          }
        },function(err){console.log('支付错误！');});
      },
      /***************************  回到主页  *******************************/
      toMain(){
        this.$router.push("/main/"+this.phonenumber);
      },
      /***************************  退出登录  *******************************/
      Loginout(){
        let that = this;
        axios.post('/api/api/Log_out',{
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
  width: 220px;
  height: 26px;
  margin: 17px 0px 17px 0px;
  float: left;
}
.toolbar {
  display: inline-flex;
  width: 337px;
  height: 60px;
  align-items: center;
}
.headbutton {
  width: 75px;
  height: 55px;
  padding: 0px;
  margin: 0px;
}
table {
  width: 840px;
  height: 480px;
  margin: 85px auto;
  border-collapse: collapse;
  text-align: center;
}
table td, table th {
  width: 210px;
  height: 40px;
  border: 1px solid #cad9ea;
  color: #666;
}
table tr:nth-child(odd) {
  background: #fff;
}
table tr:nth-child(even) {
  background: #F5FAFA;
}
</style>