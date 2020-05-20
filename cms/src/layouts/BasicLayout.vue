<template>
  <el-container>
    <el-aside>
      <SiderMenu />
    </el-aside>
    <el-container class="mars-ape-layout">
      <el-header class="mars-ape-layout-header">
        <Header />
      </el-header>
      <el-main>
          <div v-if="isRootRoutePath">
            <h2 style="box-sizing: border-box; margin-bottom: 16px; line-height: 1.25; padding-bottom: 0.3em; border-bottom: 1px solid #eaecef; color: #24292e; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji';  margin-top: 0px !important;">基于Vue和EasySwoole实现的后台权限管理系统</h2>
            <h3 style="box-sizing: border-box; margin-top: 24px; margin-bottom: 16px; font-size: 1.25em; line-height: 1.25; color: #24292e; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'; "><a id="user-content-简介" class="anchor" style="box-sizing: border-box; background-color: initial; color: #0366d6; text-decoration-line: none; float: left; padding-right: 4px; margin-left: -20px; line-height: 1;" href="https://github.com/WkyinEdge/Backend/#%E7%AE%80%E4%BB%8B" aria-hidden="true"></a>简介</h3>
            <blockquote style="box-sizing: border-box; margin: 0px 0px 16px; padding: 0px 1em; color: #6a737d; border-left: 0.25em solid #dfe2e5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'; font-size: 16px; ">
              <p style="box-sizing: border-box; margin-top: 0px; margin-bottom: 0px;">本系统为了满足后台系统的高安全、高可用，前后端通讯所有API接口数据统一进行加密传输，同时加入了一系列的安全验证，保证后台数据的安全性，后续会做介绍；另外后端严格按照面向对象开发原则进行代码结构划分，前端基于Vue组件化开发，保证了项目良好的可扩展性。</p>
            </blockquote>
            <h4 style="box-sizing: border-box; margin-top: 24px; font-size: 16px; line-height: 1.25; color: #24292e; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji';  margin-bottom: 0px !important;"><a id="user-content-GitHub地址httpsgithubcomeasy-swooleeasyswoole" class="anchor" style="box-sizing: border-box; background-color: initial; color: #0366d6; text-decoration-line: none; float: left; padding-right: 4px; margin-left: -20px; line-height: 1;" href="https://github.com/WkyinEdge/Backend/#%E7%BA%BF%E4%B8%8A%E6%BC%94%E7%A4%BA%E5%9C%B0%E5%9D%80httpsgithubcomeasy-swooleeasyswoole" aria-hidden="true"></a>GitHub地址：<a style="box-sizing: border-box; background-color: initial; color: #0366d6; text-decoration-line: none;" href="https://github.com/WkyinEdge/Backend">https://github.com/WkyinEdge/Backend</a></h4>
            <h3 style="box-sizing: border-box; margin-top: 24px; margin-bottom: 16px; font-size: 1.25em; line-height: 1.25; color: #24292e; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'; "><a id="user-content-关于安全性" class="anchor" style="box-sizing: border-box; background-color: initial; color: #0366d6; text-decoration-line: none; float: left; padding-right: 4px; margin-left: -20px; line-height: 1;" href="https://github.com/WkyinEdge/Backend/#%E5%85%B3%E4%BA%8E%E5%AE%89%E5%85%A8%E6%80%A7" aria-hidden="true"></a>关于安全性</h3>
            <h4 style="box-sizing: border-box; margin-top: 24px; margin-bottom: 16px; font-size: 16px; line-height: 1.25; color: #24292e; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'; "><a id="user-content-企业后台管理系统所管理的都是机密数据保证高安全性是最基本的本系统使用的是-aes对称加密传输--动态tokencookie登录校验--sign签名算法等3重保险保证高安全其中" class="anchor" style="box-sizing: border-box; background-color: initial; color: #0366d6; text-decoration-line: none; float: left; padding-right: 4px; margin-left: -20px; line-height: 1;" href="https://github.com/WkyinEdge/Backend/#%E4%BC%81%E4%B8%9A%E5%90%8E%E5%8F%B0%E7%AE%A1%E7%90%86%E7%B3%BB%E7%BB%9F%E6%89%80%E7%AE%A1%E7%90%86%E7%9A%84%E9%83%BD%E6%98%AF%E6%9C%BA%E5%AF%86%E6%95%B0%E6%8D%AE%E4%BF%9D%E8%AF%81%E9%AB%98%E5%AE%89%E5%85%A8%E6%80%A7%E6%98%AF%E6%9C%80%E5%9F%BA%E6%9C%AC%E7%9A%84%E6%9C%AC%E7%B3%BB%E7%BB%9F%E4%BD%BF%E7%94%A8%E7%9A%84%E6%98%AF-aes%E5%AF%B9%E7%A7%B0%E5%8A%A0%E5%AF%86%E4%BC%A0%E8%BE%93--%E5%8A%A8%E6%80%81tokencookie%E7%99%BB%E5%BD%95%E6%A0%A1%E9%AA%8C--sign%E7%AD%BE%E5%90%8D%E7%AE%97%E6%B3%95%E7%AD%893%E9%87%8D%E4%BF%9D%E9%99%A9%E4%BF%9D%E8%AF%81%E9%AB%98%E5%AE%89%E5%85%A8%E5%85%B6%E4%B8%AD" aria-hidden="true"></a>企业后台管理系统所管理的都是机密数据，保证高安全性是最基本的；本系统使用的是：&nbsp;<code style="box-sizing: border-box; font-family: SFMono-Regular, Consolas, 'Liberation Mono', Menlo, monospace; font-size: inherit; padding: 0.2em 0.4em; margin: 0px; background-color: rgba(27, 31, 35, 0.05); border-radius: 3px;">Aes对称加密传输</code>&nbsp;+&nbsp;<code style="box-sizing: border-box; font-family: SFMono-Regular, Consolas, 'Liberation Mono', Menlo, monospace; font-size: inherit; padding: 0.2em 0.4em; margin: 0px; background-color: rgba(27, 31, 35, 0.05); border-radius: 3px;">动态Token/Cookie登录校验</code>&nbsp;+&nbsp;<code style="box-sizing: border-box; font-family: SFMono-Regular, Consolas, 'Liberation Mono', Menlo, monospace; font-size: inherit; padding: 0.2em 0.4em; margin: 0px; background-color: rgba(27, 31, 35, 0.05); border-radius: 3px;">Sign签名算法</code>等3重保险，保证高安全。其中：</h4>
            <ul style="box-sizing: border-box; padding-left: 2em; margin-top: 0px; margin-bottom: 16px; color: #24292e; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'; font-size: 16px; ">
              <li style="box-sizing: border-box;">Aes对称加密是目前最为实用的加密算法之一，有着出色的性能，为数据加密传输提供可行性，配合SSL可防劫持；</li>
              <li style="box-sizing: border-box; margin-top: 0.25em;">登录校验使用Token或Cookie 取决于客户端是否为浏览器，浏览器本身的Cookie功能性是优于Token的，对于像App类的客户端就需要使用Token进行身份验证，服务端再配合登录IP检测，可做到防内又防外...</li>
              <li style="box-sizing: border-box; margin-top: 0.25em;">服务端使用Redis作为Session驱动，可支持分布式服务器会话数据共享，并且同时支持客户端使用Token和Cookie两种会话模式；</li>
              <li style="box-sizing: border-box; margin-top: 0.25em;">Sign 会对对每一个客户端请求做了参数签名，防止中途被恶意篡改！另外还做了时效性和防重放功能，从请求发出到服务器收到超过指定时间视为超时无效（同步服务器时间），并且同一个请求不能使用第二次，可以有效防止抓包或模拟登录风险。</li>
            </ul>
            <h3 style="box-sizing: border-box; margin-top: 24px; margin-bottom: 16px; font-size: 1.25em; line-height: 1.25; color: #24292e; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'; "><a id="user-content-主要功能介绍" class="anchor" style="box-sizing: border-box; background-color: initial; color: #0366d6; text-decoration-line: none; float: left; padding-right: 4px; margin-left: -20px; line-height: 1;" href="https://github.com/WkyinEdge/Backend/#%E4%B8%BB%E8%A6%81%E5%8A%9F%E8%83%BD%E4%BB%8B%E7%BB%8D" aria-hidden="true"></a>主要功能介绍</h3>
            <ul style="box-sizing: border-box; padding-left: 2em; margin-top: 0px; margin-bottom: 16px; color: #24292e; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'; font-size: 16px; ">
              <li style="box-sizing: border-box;">后台权限管理系统</li>
              <li style="box-sizing: border-box; margin-top: 0.25em;">WebSocket在线推送</li>
              <li style="box-sizing: border-box; margin-top: 0.25em;">小视频</li>
              <li style="box-sizing: border-box; margin-top: 0.25em;">......</li>
            </ul>
            <h4 style="box-sizing: border-box; margin-top: 24px; margin-bottom: 16px; font-size: 16px; line-height: 1.25; color: #24292e; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'; "><a id="user-content-权限系统分为4个部分用户管理角色管理权限管理菜单管理" class="anchor" style="box-sizing: border-box; background-color: initial; color: #0366d6; text-decoration-line: none; float: left; padding-right: 4px; margin-left: -20px; line-height: 1;" href="https://github.com/WkyinEdge/Backend/#%E6%9D%83%E9%99%90%E7%B3%BB%E7%BB%9F%E5%88%86%E4%B8%BA4%E4%B8%AA%E9%83%A8%E5%88%86%E7%94%A8%E6%88%B7%E7%AE%A1%E7%90%86%E8%A7%92%E8%89%B2%E7%AE%A1%E7%90%86%E6%9D%83%E9%99%90%E7%AE%A1%E7%90%86%E8%8F%9C%E5%8D%95%E7%AE%A1%E7%90%86" aria-hidden="true"></a>权限系统分为4个部分：<code style="box-sizing: border-box; font-family: SFMono-Regular, Consolas, 'Liberation Mono', Menlo, monospace; font-size: inherit; padding: 0.2em 0.4em; margin: 0px; background-color: rgba(27, 31, 35, 0.05); border-radius: 3px;">用户管理</code>、<code style="box-sizing: border-box; font-family: SFMono-Regular, Consolas, 'Liberation Mono', Menlo, monospace; font-size: inherit; padding: 0.2em 0.4em; margin: 0px; background-color: rgba(27, 31, 35, 0.05); border-radius: 3px;">角色管理</code>、<code style="box-sizing: border-box; font-family: SFMono-Regular, Consolas, 'Liberation Mono', Menlo, monospace; font-size: inherit; padding: 0.2em 0.4em; margin: 0px; background-color: rgba(27, 31, 35, 0.05); border-radius: 3px;">权限管理</code>、<code style="box-sizing: border-box; font-family: SFMono-Regular, Consolas, 'Liberation Mono', Menlo, monospace; font-size: inherit; padding: 0.2em 0.4em; margin: 0px; background-color: rgba(27, 31, 35, 0.05); border-radius: 3px;">菜单管理</code>；</h4>
            <h4 style="box-sizing: border-box; margin-top: 24px; margin-bottom: 16px; font-size: 16px; line-height: 1.25; color: #24292e; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'; "><a id="user-content-各个实体之间的关联关系介绍" class="anchor" style="box-sizing: border-box; background-color: initial; color: #0366d6; text-decoration-line: none; float: left; padding-right: 4px; margin-left: -20px; line-height: 1;" href="https://github.com/WkyinEdge/Backend/#%E5%90%84%E4%B8%AA%E5%AE%9E%E4%BD%93%E4%B9%8B%E9%97%B4%E7%9A%84%E5%85%B3%E8%81%94%E5%85%B3%E7%B3%BB%E4%BB%8B%E7%BB%8D" aria-hidden="true"></a>各个实体之间的关联关系介绍：</h4>
            <blockquote style="box-sizing: border-box; margin: 0px 0px 16px; padding: 0px 1em; color: #6a737d; border-left: 0.25em solid #dfe2e5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'; font-size: 16px; ">
              <p style="box-sizing: border-box; margin-top: 0px; margin-bottom: 0px;">每个后台用户隶属于相应的角色，不同的角色可分配不等的权限，每个权限又管理着不同的路由，每个路由就决定了用户所能访问的 页面地址、以及API接口数据...</p>
            </blockquote>
            <h4 style="box-sizing: border-box; margin-top: 24px; margin-bottom: 16px; font-size: 16px; line-height: 1.25; color: #24292e; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'; "><a id="user-content-websocket推送介绍" class="anchor" style="box-sizing: border-box; background-color: initial; color: #0366d6; text-decoration-line: none; float: left; padding-right: 4px; margin-left: -20px; line-height: 1;" href="https://github.com/WkyinEdge/Backend/#websocket%E6%8E%A8%E9%80%81%E4%BB%8B%E7%BB%8D" aria-hidden="true"></a>WebSocket推送介绍：</h4>
            <blockquote style="box-sizing: border-box; margin: 0px 0px 16px; padding: 0px 1em; color: #6a737d; border-left: 0.25em solid #dfe2e5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'; font-size: 16px; ">
              <p style="box-sizing: border-box; margin-top: 0px; margin-bottom: 0px;">该功能是基于 Swoole 的 webSocket 服务端和 Http 服务端协作完成，Http服务器负责用户登录相关以及提供相应的操作API，WS会对已登录用户先进行token认证，认证成功后 会关联用户id和WS连接id，并对用户进行上下线记录；管理员可通过HTTP提供的操作API调用WS 对当前在线用户进行实时消息推送（包括Media），后续可扩展聊天系统、直播等定向推流功能...</p>
            </blockquote>
          </div>
          <router-view />
      </el-main>
      <el-footer>
        <Footer />
      </el-footer>
    </el-container>
  </el-container>
</template>
<script>
import SiderMenu from './SiderMenu'
import Header from './Header'
import Footer from './Footer'

export default {
  components: {
    SiderMenu,
    Header,
    Footer,
  },
  data(){
    return {

    }
  },
  computed: {
    isRootRoutePath: function() {
      return this.$route.path=='/'
    },
  },
  methods: {
    // cs() {
    //   console.log(this.$ws)
    //   this.$ws.closeWS()
    // }
  }
}
</script>
<style lang="stylus">
.mod-index
  border-radius 5px
  background #fff
  padding 24px
  min-height 800px
  line-height 1.5
  p
    margin 0
  h3
    margin-block-start 0
    margin-block-end 0.5em
  .xm-gt img
    height 240px
    margin-left -16px
  .jx-zz img 
    height 400px
    margin-top 24px
    margin-right 24px
.el-container
  background #f0f2f5
.el-aside
  min-height 100vh
  box-shadow 2px 0 6px rgba(0,21,41,.35)
  position relative
  width auto !important
  z-index 10
  transitionall .3s
  background #001529
.el-header 
  height 64px !important
  padding 0px
  width 100%
.el-main
  padding 0px
  margin 24px
  overflow inherit
  flexnone !important
  min-height 720px
.main-page-content
  border-radius 5px
  background #ffffff
  padding 24px
.el-footer
  height auto !important
</style>


