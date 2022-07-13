import { createRouter, createWebHashHistory, createWebHistory } from 'vue-router'

import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import UpdatePassword from '../views/UpdatePassword.vue'
import Delay from '../views/Delay.vue'
import Main from '../views/Main.vue'

import Payformember from '../views/user/Payformember.vue'
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
    component: Login
  },
  {
    path: '/updatepassword',
    component: UpdatePassword
  },
  {
    path: '/register',
    component: Register
  },
  {
    path: '/delay',
    component: Delay
  },
  {
    path: '/user/payformember/:phonenumber',
    name:"Payformember",
    component:Payformember,
    props: true
  },
  {
      path: '/main/:phonenumber',
      component: Main,
      props: true,
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

export default router
