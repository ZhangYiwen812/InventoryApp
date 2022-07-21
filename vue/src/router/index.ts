import { createRouter, createWebHashHistory, createWebHistory } from 'vue-router'

import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import UpdatePassword from '../views/UpdatePassword.vue'
import Delay from '../views/Delay.vue'
import Main from '../views/Main.vue'

import Accountinfo from '../views/user/Accountinfo.vue'
import Submanagement from '../views/user/Submanagement.vue'
import Commoditydata from '../views/user/Commoditydata.vue'
import Inventoryorder from '../views/user/Inventoryorder.vue'
import Orderinfo from '../views/user/Orderinfo.vue'
import NotFound from '../views/NotFound.vue'

import store from '../store';

const routes = [
  {
      path: '/',
      redirect: '/login',
  },
  {
    path: '/login',
    component: Login,
    meta:{
      title:"登录"
    }
  },
  {
    path: '/updatepassword',
    component: UpdatePassword,
    meta:{
      title:"修改密码"
    }
  },
  {
    path: '/register',
    component: Register,
    meta:{
      title:"注册账号"
    }
  },
  {
    path: '/delay',
    component: Delay,
    meta:{
      title:"请您稍等"
    }
  },
  {
      path: '/main/:phonenumber',
      component: Main,
      props: true,
      meta:{
        title:"柳文盘点系统Web管理端"
      },
      children: [
          {
            path:"/user/accountinfo/:phonenumber",
            name:"Accountinfo",
            component:Accountinfo,
            props: true
          },
          {
            path:"/user/submanagement/:phonenumber",
            name:"Submanagement",
            component:Submanagement,
            props: true
          },
          {
            path:"/user/commoditydata/:phonenumber",
            name:"Commoditydata",
            component:Commoditydata,
            props: true
          },
          {
            path:"/user/inventoryorder/:phonenumber",
            name:"Inventoryorder",
            component:Inventoryorder,
            props: true
          },
          {
            path:"/user/orderinfo/:phonenumber/:orderid",
            name:"Orderinfo",
            component:Orderinfo,
            props: true
          }
      ]
  },
  {
      path: '/goHome',
      redirect: '/'
  },
  {
      path:'/:pathMath(.*)',
      component: NotFound
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  // history: createWebHistory(),
  routes
});

router.beforeEach((to, from, next)=>{
  //在没有登录的条件下，进入非登录、非注册、非找回密码的页面，就跳转到登录页
  if(!(sessionStorage.getItem('loginstate')) && to.path!='/login' && to.path!='/updatepassword' && to.path!='/register' 
    // 如果离开了注册页面而且将要进入延时页面则允许跳转
    && (from.path!='/register' || to.path!='/delay')){
    next('/');
  }else{
    next(true);
  }
});

router.afterEach((to, from) => {
  if(typeof(to.meta.title) === 'string'){
    document.title = to.meta.title; //在全局后置守卫中获取路由元信息设置title
  }
})

export default router
