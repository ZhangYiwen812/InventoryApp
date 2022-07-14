<template>
  <div>
    <div class="title">盘点订单</div>
    <div class="editlist">
      <el-button class="editbutton" type="primary" @click="openCreateOrderDialog()">新建订单</el-button>
    </div>

    <div class="cardlist">
      <el-card class="boxcard" v-for="carditem in orderList">
        <template #header>
          <div class="card-header">
            <span>盘点编号：{{carditem.orderid}}</span>

            <el-dropdown>
              <span>
                <el-icon style="padding-top:3px">
                  <arrow-down />
                </el-icon>
              </span>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item @click="openDelDialog(carditem.orderid,true)">
                    强制删除
                  </el-dropdown-item>
                  <!-- <el-dropdown-item></el-dropdown-item> -->
                </el-dropdown-menu>
              </template>
            </el-dropdown>

            <el-button class="cardbutton" type="primary" @click="toOrderinfo(carditem.orderid)">订单详情</el-button>
          </div>
        </template>
        
        <div class="carditem">
          <span style="float:left">建立时间：{{carditem.subtime}}</span>
        </div>

        <div class="carditem">
          <span style="float:left">状态：{{carditem.statetext}}&nbsp;&nbsp;</span>
          <el-icon style="float:left;padding-top:8px">
            <!-- 等待盘点 0 -->
            <Bell v-if="carditem.state==0"/>
            <!-- 正在盘点 1 -->
            <AlarmClock  v-if="carditem.state==1"/>
            <!-- 盘点完成，等待求和 2 -->
            <Cpu  v-if="carditem.state==2"/>
            <!-- 正在求和 3 -->
            <Loading  v-if="carditem.state==3"/>
            <!-- 订单已完成 4 -->
            <Finished v-if="carditem.state==4"/>
          </el-icon>
          <el-button class="delbtn" type="danger" @click="openDelDialog(carditem.orderid,false)"><el-icon><Delete/></el-icon></el-button>
        </div>
      </el-card>
    </div>

    <el-dialog title="新建订单" v-model="newOrderdialogVisible" width="800px" destroy-on-close>
      <span>系统将按照商品数据的内容创建盘点订单，</span><br>
      <span>请选择需要加入的盘点人员。</span>
      <br><br>
        <el-transfer
          style="text-align: left; display: inline-block"
          :data="users"
          v-model="rightValue"
          filterable
          filter-placeholder="搜索员工"
          target-order="unshift"
          :titles="['所有子账号', '已加入的盘点人员']"
          :button-texts="['移出盘点', '加入盘点']"
          :format="{
            noChecked: '${total}',
            hasChecked: '${checked}/${total}',
          }"
          @change="transferChange"
        >
        </el-transfer>
      <br><br>
      <span class="dialog-footer">
        <el-button type="primary" @click="checkAll=true;newOrderdialogVisible=false">取 消</el-button>
        <el-button type="primary" @click="createOrder()">确 定</el-button>
      </span>
    </el-dialog>

    <el-dialog title="温馨提示" v-model="delDialogVisible" width="380px">
        <span v-if="!Manddelmode">您要删除这个订单吗？</span>
        <span v-if="Manddelmode" style="color:red">您要强制删除这个订单吗？</span>
        <br><br><br>
        <span class="dialog-footer">
          <el-button type="primary" @click="delDialogVisible=false">取 消</el-button>
          <el-button v-if="!Manddelmode" @click="deleteOrder()">确 认</el-button>
          <el-button v-if="Manddelmode" @click="delOrderMandatoryCode()">确 认</el-button>
        </span>
    </el-dialog>
    
  </div>
</template>

<script>
  import axios from '../../http';
  import { ElMessage } from 'element-plus';

  export default {
    name: 'Inventoryorder',
    props: ['phonenumber'],
    data() {
      return {
        // 订单列表
        orderList: [],
        // 创建订单对话框数据
        users: [],
        rightValue: [],
        // 删除订单对话框数据
        delorderid: '',
        // 对话框显示
        newOrderdialogVisible: false,
        Manddelmode: false, // false普通删除，true强制删除
        delDialogVisible: false,
      }
    },
    // mounted() {
    created() {
      this.getData();
    },
    methods: {
      /***************************  获取订单列表  *******************************/
      getData(){
        let that = this;
        axios.get('/api/api/orderdb/get_order_list',{
          params:{
            sendphonenumber: that.phonenumber
          }
        }).then(function(response) {
          if(response.data.get==1){
            console.log(response);
            that.orderList=response.data.data;
            console.log('获取数据成功');
          }else if(response.data.get==0){
            console.log('获取数据失败！');
            that.loginfail();
          }
        },function(err){console.log('获取数据错误！');});
      },
      /**************************  选择所有的用户  *******************************/
      checkAllUsers() {
        this.checkedUsers = this.checkAll ? this.users : [];
        this.isIndeterminate = false;
      },
      /****************************  选择用户  **********************************/
      checkedUsersChange() {
        let checkedCount = this.checkedUsers.length;
        this.checkAll = checkedCount === this.users.length;
        this.isIndeterminate = checkedCount > 0 && checkedCount < this.users.length;
      },
      /************************  打开创建订单的对话框  ***************************/
      openCreateOrderDialog() {
        let that = this;
        axios.get('/api/api/userdb/get_only_admin_phonedata_totransfer',{
          params:{
            phonenumber: that.phonenumber
          }
        }).then(function(response) {
          if(response.data.get==1){
            console.log(response.data.data);
            that.rightValue=[];
            that.users=response.data.data;
            that.users.forEach(item => {
              that.rightValue.push(item.key);
            });
            that.newOrderdialogVisible=true;
            console.log('获取用户数据成功');
          }else if(response.data.get==0){
            console.log('获取用户数据失败！');
            that.loginfail();
          }
        },function(err){console.log('获取用户数据错误！');});
      },
      /**************************  穿梭框改变方法  ******************************/
      transferChange(){
        // console.log('进入transferChange:'+this.rightValue);
      },
      /****************************  新建订单  **********************************/
      createOrder(){
        this.newOrderdialogVisible=false;
        let that = this;
        axios.post('/api/api/orderdb/create_order',{
          sendphonenumber: that.phonenumber,
          recphonenumbers: that.rightValue,
        }).then(function(response) {
          let create = response.data.create;
          if(create==4){
            that.rightValue=[];
            ElMessage({
              showClose: true,
              message: '创建订单成功',
              type: 'success',
            });
            that.getData();
            console.log('创建订单成功');
          }else if(create==1 || create==2 || create==3){
            // 数据超额
            let whichMemberText = response.data.whichMemberText;
            console.log(whichMemberText);
            ElMessage({
              showClose: true,
              message: whichMemberText,
              type: 'error',
            });
          }else if(create==0){
            console.log('创建订单失败！');
            that.loginfail();
          }
        },function(err){that.getData();console.log('创建订单错误！');});
      },
      /*************************  去往订单详情页  *******************************/
      toOrderinfo(orderid){
        this.$router.push("/user/orderinfo/"+this.phonenumber+'/'+orderid);
      },
      /***********************  打开删除订单对话框  ******************************/
      openDelDialog(orderid,Manddelmode){
        this.delorderid=orderid;
        this.Manddelmode=Manddelmode;
        this.delDialogVisible=true;
      },
      /***************************  删除订单  ***********************************/
      deleteOrder(){
        let that = this;
        axios.post('/api/api/orderdb/del_order',{
          phonenumber: that.phonenumber,
          orderid: that.delorderid
        }).then(function(response) {
          if(response.data.del==2){
            that.delorderid='';
            that.delDialogVisible=false;
            ElMessage({
              showClose: true,
              message: '删除订单成功',
              type: 'success',
            });
            that.getData();
            console.log('删除订单成功');
          }else if(response.data.del==1){
            that.delorderid='';
            that.delDialogVisible=false;
            ElMessage({
              showClose: true,
              message: '删除失败，订单未完成！',
              type: 'error',
            });
            console.log('删除失败，订单未完成！');
          }else if(response.data.del==0){
            console.log('删除订单失败！');
            that.loginfail();
          }
        },function(err){console.log('删除订单错误！');});
      },
      /*************************  强制删除订单  ***********************************/
      delOrderMandatoryCode(){
        let that = this;
        axios.post('/api/api/orderdb/del_order_mandatory_code',{
          phonenumber: that.phonenumber,
          orderid: that.delorderid
        }).then(function(response) {
          if(response.data.del==1){
            that.delorderid='';
            that.delDialogVisible=false;
            ElMessage({
              showClose: true,
              message: '强制删除订单成功',
              type: 'success',
            });
            that.getData();
            console.log('强制删除订单成功');
          }else if(response.data.del==0){
            console.log('强制删除订单失败！');
            that.loginfail();
          }
        },function(err){console.log('强制删除订单错误！');});
      },
      /***************************  登录失效  ***********************************/
      loginfail(){
        console.log('登录失效！');
        sessionStorage.removeItem("loginstate");
        this.$router.push("/");
        alert('登录失效！');
      },
      /**************************************************************************/
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

.cardlist {
  display: block;
  width: 834px;
  align-items: center;
}
.boxcard {
  float: left;
  width: 400px;
  height: 203px;
  margin: 0px 15px 15px 0px;
}
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.cardbutton {
  font-size: 14px;
}
.carditem {
  width: 360px;
  height: 32px;
  margin: 2px 0px 18px 0px;
  line-height:32px;
}
.delbtn {
  float:right;
}
.dialog-footer button:first-child {
  margin-right: 20px;
}
</style>
