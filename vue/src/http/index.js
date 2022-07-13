import axios from 'axios';
import { ElLoading } from 'element-plus'

// 使用loading遮罩
var loadingInstance = {};
// 创建axios实例
const serviceAxios = axios.create({
    baseUrl: '/api', // 基础请求地址
    timeout: 20000, // 请求超时设置
    withCredentials: false, // 跨域请求是否携带cookie
});

// 请求拦截
serviceAxios.interceptors.request.use(
    (config)=>{
        // console.log('请求拦截请求配置',config);
        loadingInstance = ElLoading.service({ 
            fullscreen: true,
            text: '请您耐心等待，系统正在加载......' 
        })
        return config;
    },(error)=>{
        // console.log('请求拦截错误',error);
        return Promise.reject(error);
    }
)

// 响应拦截
serviceAxios.interceptors.response.use(
    (res)=>{
        // console.log('响应拦截响应结果',res);
        loadingInstance.close();
        return res;
    },(error)=>{
        // console.log('响应拦截错误',error);
        loadingInstance.close();
        return Promise.reject(error);
    }
)

export default serviceAxios;