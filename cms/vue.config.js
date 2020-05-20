module.exports = {
    publicPath: process.env.NODE_ENV === 'production' ? process.env.VUE_APP_BASE_URL : '/',
    configureWebpack: {
        resolve: {
            alias: {
                '@public': '@/../public'
            }
        }
    },
    css: {
        loaderOptions: {}
    },

    devServer: {
        proxy: {
            // 表示以 "/admin" 开头的请求路径 才会去代理请求服务器，不带 /admin的就视为 本地路由跳转，
            // 如果填 "/" 就会混乱 不管是不是api地址都去请求服务器，导致vue路由失效
            '/admin': {
                target: process.env.VUE_APP_POROXY_TARGET,
                changeOrigin: false,  //设置为true, 本地就会虚拟一个服务器接收你的请求并代你发送该请求
                pathRewrite: {
                    //'^/admin/upload': '/upload',   // 替换路径中指定字段为新字段，避免图片、视频地址错误
                    //'^/admin/api': '/api'   // 替换路径中指定字段为新字段，避免图片、视频地址错误
                }
            },
            '/upload': {
                target: process.env.VUE_APP_POROXY_TARGET,
                changeOrigin: false,
            },
            '/api': {
                target: process.env.VUE_APP_POROXY_TARGET,
                changeOrigin: false,
            }
        }

    }
}
/*
proxy: {
      // 表示以 "/admin" 开头的请求路径 才会去代理请求服务器，不带 /admin的就视为 本地路由跳转，
      // 如果填 "/" 就会混乱 不管是不是api地址都去请求服务器，导致vue路由失效
      '/admin': {
        target: process.env.VUE_APP_POROXY_TARGET,
        changeOrigin: false,
        pathRewrite: {
          //'^/admin/upload': '/upload',   // 替换路径中指定字段为新字段，避免图片、视频地址错误
          '^/admin/api': '/api'   // 替换路径中指定字段为新字段，避免图片、视频地址错误
        }
      },
      '/upload': {
        target: process.env.VUE_APP_POROXY_TARGET,
        changeOrigin: false,
      },
      '/api': {
        target: process.env.VUE_APP_POROXY_TARGET,
        changeOrigin: false,
      }
    }
 */
