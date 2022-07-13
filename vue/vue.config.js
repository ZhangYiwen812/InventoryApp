const { defineConfig } = require('@vue/cli-service')
const { resolveComponent } = require('vue')
module.exports = defineConfig({
  transpileDependencies: true,
  lintOnSave: false,

  // resolve: {
  //   extensions: ['.ts', '.tsx', '.js', '.jsx','.vue', '.json']
  // },

  // Server: {
  devServer: {
    proxy: { 
      // 代理 前端解决跨域问题 可以书写多个被代理的服务器
      '/api': {
          target: 'http://1223.com',
          changeOrigin: true,
          pathRewrite: {
          //   //这里理解成用‘/api’代替target里面的地址，后面组件中我们掉接口时直接用api代替 
          //   //比如我要调用'http://40.00.100.100:3002/user/add'，直接写‘/api/user/add’即可
              '^/api': '' 
          }
      }
    },
    // open: true,
  },
})
