const { defineConfig } = require('@vue/cli-service')
const { resolveComponent } = require('vue')
module.exports = defineConfig({
  transpileDependencies: true,
  lintOnSave: false,

  configureWebpack: config => {
    // 为生产环境修改配置...
    if (process.env.NODE_ENV === 'production') {
        config.mode = 'production';
        // 打包文件大小配置
        config.performance = {
          maxEntrypointSize: 10000000,
          maxAssetSize: 30000000
        }
    }
  },

  // Server: {
  devServer: {
    port: 8080,
    proxy: {
      // 代理 前端解决跨域问题 可以书写多个被代理的服务器
      '/api': {
          target: 'http://1223.com',
          // target: 'http://liuweninventory.cloud',
          // target: 'http://1.116.151.135',
          changeOrigin: true,
          pathRewrite: {
          //   //这里理解成用‘/api’代替target里面的地址，后面组件中我们掉接口时直接用api代替 
          //   //比如我要调用'http://40.00.100.100:3002/user/add'，直接写‘/api/user/add’即可
              '^/api': '' 
          }
      }
    },
  },
})
