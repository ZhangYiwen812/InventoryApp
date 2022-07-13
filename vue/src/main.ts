import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import store from './store';
import locale from 'element-plus/lib/locale/lang/zh-cn';
import ElementPlus from 'element-plus';
import 'element-plus/dist/index.css';
import * as ELIcons from '@element-plus/icons-vue';

// createApp(App).use(store).use(router).use(ElementPlus).mount('#app');

var axios = require('axios');
//设置cors跨域 并设置访问权限 允许跨域携带cookie信息
axios.defaults.withCredentials = true;

const app = createApp(App);
for(const name in ELIcons){
    app.component(name,(ELIcons as any)[name]);
}
app.use(store);
app.use(router);
app.use(ElementPlus,{locale});
app.mount('#app');
// this.$forceUpdate() // 组件使用 重新渲染