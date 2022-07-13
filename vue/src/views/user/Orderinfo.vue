<template>
  <div>
      <div class="title">
        <el-button 
            class="editbutton" 
            style="margin:0px 10px 0px 0px;width:80px" 
            type="" 
            @click="toBack()">
            <el-icon><ArrowLeft/></el-icon>&nbsp;返回
        </el-button>
        <span style="line-height:35px;float:left;margin-left:10px">订单详情页</span>
        
      </div>

      <div class="editlist">
        <el-button class="editbutton" style="margin:0px 10px 0px 0px" 
            type="primary" @click="verifStatesToDownloadExcel()">
          计算总库存表
        </el-button>
        <el-button class="editbutton" style="margin:0px 10px 0px 0px" 
            type="primary" @click="batchSelect()">
          批量选择已提交
        </el-button>
        <el-button class="editbutton" style="margin:0px 10px 0px 0px" 
            type="danger" @click="clearSelected()">
          清除已选择
        </el-button>
        <el-label style="font-size:13px;line-height:35px;float:left;margin-left:0px">
          请选择盘点表，然后点击“计算总库存表”，系统会将盘点表求和后下载。
        </el-label>
      </div>
      
      <el-table ref="stocktable" style="width:802px" :data="stockPersons"
        stripe='stripe' 
        @selection-change="selectionChange">
        
        <el-table-column type="selection" width="55px" align="center"/>

        <el-table-column label="序号" type="index" width="64px" align="center">
          <template #default="scope" v-slot="{$index}">
            <div class="tablebox">
              <span style="margin:auto;">{{scope.$index+pageSize*(currentPage-1)+1}}</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="手机号" width="200px" align="center">
          <template #default="scope">
            <div class="tablebox">
              <span style="margin:auto;">{{scope.row.phonenumber}}</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="姓名" width="190px" align="center">
          <template #default="scope">
            <div class="tablebox">
              <span style="margin:auto;">{{scope.row.name}}</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="状态" width="190px" align="center">
          <template #default="scope">
            <div class="tablebox">
                <!-- 等待盘点 0 -->
                <span style="margin:auto;color:red;" v-if="scope.row.state==0">
                  {{scope.row.statetext}}
                  <el-icon style="float:right;padding-left:6px;padding-top:3px">
                    <Bell/>
                  </el-icon>
                </span>
                <!-- 正在盘点 1 -->
                <span style="margin:auto;color:orange;" v-if="scope.row.state==1">
                  {{scope.row.statetext}}
                  <el-icon style="float:right;padding-left:6px;padding-top:3px">
                    <AlarmClock/>
                  </el-icon>
                </span>
                <!-- 盘点结束，已提交 2 -->
                <span style="margin:auto;color:blue;" v-if="scope.row.state==2">
                  {{scope.row.statetext}}
                  <el-icon style="float:right;padding-left:6px;padding-top:3px">
                    <DocumentChecked/>
                  </el-icon>
                </span>
                <!-- 已求和 3 -->
                <span style="margin:auto;color:green;" v-if="scope.row.state==3">
                  {{scope.row.statetext}}
                  <el-icon style="float:right;padding-left:6px;padding-top:3px">
                    <CircleCheck/>
                  </el-icon>
                </span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="提交次数" width="100px" align="center">
          <template #default="scope">
            <div class="tablebox">
              <span style="margin:auto;">{{scope.row.subtotal}}</span>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <span style="font-size:15px;float:left;line-height:35px;margin:2px 0px 0px 25px">
          共&nbsp;{{stockPersons.length}}&nbsp;条
      </span>
  </div>
</template>

<script>
  import axios from '../../http';
  import { ElMessage } from 'element-plus';

  export default {
    name: 'Orderinfo',
    props: ['phonenumber','orderid'],
    data() {
      return {
        stockPersons: [],
        sumStockPersons: [],

        totalSize: 0,
        currentPage: 1, // 当前页
        pageSize: 5, // 每分页数量
        small: false, // 显示大小
        totalSize: 0, // 项目总数
        background: true, // 背景是否有框
        disabled: false, // 是否可用
      }
    },
    computed:{},
    mounted: function(){
      /************************  进入页面获取用户数据  ***************************/
      this.getData();
    },
    methods: {
      /*************************  返回到盘点订单页面  ****************************/
      toBack(){
        this.$router.push("/user/inventoryorder/"+this.phonenumber);
      },
      /********************  获取指定订单编号的盘点人员信息  **********************/
      getData(){
        let that = this;
        axios.post('/api/api/orderdb/get_only_order_info',{
          sendphonenumber: that.phonenumber,
          orderid: that.orderid
        }).then(function(response) {
          if(response.data.get==1){
            that.stockPersons=response.data.data;
            that.totalSize=response.data.data.length;
            console.log('获取数据成功');
          }else if(response.data.get==0){
            console.log('获取数据失败！');
            that.loginfail();
          }
        },function(err){console.log('获取数据错误！');});
      },
      /****************************  验证盘点状态  *******************************/
      verifStatesToDownloadExcel(){
        // console.log(this.sumStockPersons);
        if(this.sumStockPersons.length>0){
          let that = this;
          axios.post('/api/api/orderdb/verif_states',{
            sendphonenumber: that.phonenumber,
            orderid: that.orderid,
            sumPhonenumbers: that.sumStockPersons
          }).then(function(response) {
            if(response.data.verif==2){
              that.downloadExcel();
              console.log('验证盘点状态成功');
            }else if(response.data.verif==1){
              ElMessage({
                showClose: true,
                message: '有盘点未提交，无法求和！',
                type: 'error',
              });
              console.log('有盘点未提交，无法求和！');
            }else if(response.data.verif==0){
              console.log('验证盘点状态失败！');
              that.loginfail();
            }
          },function(err){console.log('验证盘点状态失败！');});
        }if(this.sumStockPersons.length==0){
          ElMessage({
            showClose: true,
            message: '请您选择盘点员工！',
            type: 'error',
          });
        }
      },
      /****************************  批量选择已提交  ****************************/
      batchSelect(){
        this.clearSelected();
        let that = this;
        this.stockPersons.forEach(function(element){
          if(element.state==2 || element.state==3){
            that.sumStockPersons.push(element);
            that.$refs.stocktable.toggleRowSelection(element);
          }
        });
      },
      /****************************  批量选择已提交  ****************************/
      clearSelected() {
        this.$refs.stocktable.clearSelection();
      },
      /************************  下载Excel导出库存数据  *************************/
      downloadExcel(){
        let that = this;
        axios.post('/api/api/orderdb/download_stock',{
          sendphonenumber: that.phonenumber,
          orderid: that.orderid,
          sumPhonenumbers: that.sumStockPersons
        },{responseType: 'blob'}).then(function(response) {
          let filename = that.getFormatDateTimeFileName();
          let downloadElement = document.createElement('a'); //创建a标签
          let blob = new Blob([response.data]) //创建二进制大对象
          let href = window.URL.createObjectURL(blob); //创建下载的链接
          downloadElement.href = href;
          downloadElement.download = filename; //下载后文件名
          document.body.appendChild(downloadElement); //将下载元素加入body中
          downloadElement.click(); //点击创建的a标签下载
          document.body.removeChild(downloadElement); //下载完成移除元素
          window.URL.revokeObjectURL(href); //释放掉blob对象
          ElMessage({
            showClose: true,
            message: '创建库存表成功',
            type: 'success',
          });
          that.getData();
          console.log('创建库存表成功');
        },function(err){
          ElMessage({
            showClose: true,
            message: '下载失败！',
            type: 'error',
          });
          console.log('下载失败！');
        });
      },
      /**************************  生成下载文件名  *****************************/
      getFormatDateTimeFileName(){
        let date = new Date();
        let year= date.getFullYear();
        let month = date.getMonth() + 1;
        let day = date.getDate();
        let hour = date.getHours();
        let minute = date.getMinutes();
        let second = date.getSeconds();
        const min = 10000;
        const max = 99999;
        let lastname = Math.floor(Math.random()*(max-min))+min;
        return year+"年"+month+"月"+day+"日"+hour+"时"+minute+"分"+second+"秒"+lastname+"号导出文件.xlsx";
      },
      /*********************  选择需要求和的盘点人员  ***************************/
      selectionChange(selection){
        // console.log(selection)
        this.sumStockPersons=selection;
      },
      /***************************  登录失效  **********************************/
      loginfail(){
        console.log('登录失效！');
        sessionStorage.removeItem("loginstate");
        this.$router.push("/");
        alert('登录失效！');
      },
      /*************************************************************************/
    },
  }
</script>

<style scoped>
.title {
  font-size: 18px;
  line-height: 35px;
  text-align: center;
  width: 801px;
  height: 35px;
  margin: 0px 0px 15px 0px;
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
  margin-right: 5px;
}
.el-pagination {
  margin:30px auto;
}
</style>
