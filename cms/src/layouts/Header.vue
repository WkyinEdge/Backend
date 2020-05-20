<template>
  <div class="mars-ape-layout-header">
    <div class="header-left">
      <i class="iconfont header-index-trigger" :class="[isCollapse?'icon-menu-fold':'icon-menu-unfold']" @click="handleClick"></i>
    </div>
    <div class="header-middle">
      <span style="color: green;" v-if="wsState">WS-ON / ID:{{wsFd}}</span><span style="color: red;" v-else>WS-OFF</span>
      <el-button v-show="!wsState" type="primary" size="mini" @click="wsReStart">重连</el-button>
    </div>
    <div class="header-right">
      <!-- <span class="right-notice">
        <el-badge :value="12" class="item">
          <i class="iconfont icon-lingdang"></i>
        </el-badge>
      </span> -->
      <el-dropdown @command="handleCommand">
        <span class="right-account">
          <span class="right-account-avatar">
            <img src="@/assets/avatar.png" alt="avatar">
          </span>
          <span class="right-account-name">{{userInfo['nickname']}}</span>
        </span>
        <el-dropdown-menu slot="dropdown">
          <el-dropdown-item disabled command="setting"><i class="iconfont icon-touxiang"></i><span>个人设置</span></el-dropdown-item>
          <el-dropdown-item divided command="offWS"><i class="iconfont icon-tuichudenglu1"></i><span>断开WebSocket</span></el-dropdown-item>
          <el-dropdown-item divided command="logout"><i class="iconfont icon-tuichudenglu2"></i><span>退出登录</span></el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
    </div>
  </div>
</template>

<script>
import { mapGetters,mapMutations } from 'vuex'

import { Message ,MessageBox } from 'element-ui'
import { WebSocketClient }  from '../utils/WebSocket'
import Vue from 'vue'

export default {
  data() {
    return {
      wsFd:0
    }
  },
  methods: {
    ...mapMutations([
      'handleUserInfo'
    ]),
    // 左侧菜单的展开折叠
    handleClick () {
      this.$store.commit('changeCollapse')
    },
    // 处理下拉菜单指令
    handleCommand(command) {
      if (command === 'logout') {
        this.handleLogout()
      }else if (command === 'offWS') {
        /** 关闭 webSocket 服务 **/
        this.websocket.closeWS()
        /********* end ***********/
      }
    },
    // 处理退出
    async handleLogout() {
        await this.$api.submitLogout()
        localStorage.removeItem('user_info')
        localStorage.removeItem('token')

        /** 关闭 webSocket 服务 **/
        this.websocket.closeWS()
        /********* end ***********/

        this.$message.success('退出成功！')
        this.$router.push('/login')
    },
    // 建立 webSocket连接
    webSocketConnect()
    {
      let token = '?client-type=app&token=' + localStorage.getItem('token')
      this.websocket = new WebSocketClient(token)

      this.websocket.createSocket() //创建webSocket

      /*this.websocket.sendWsPush({   //发送数据
        class: 'Index',
        action: 'ping',
        msg: 'ping'
      })*/
      // 添加监听事件
      window.addEventListener('onmessageWS', this.WsMessage)
    },
    // message事件 回调函数
    WsMessage(e) {
      let res = JSON.parse(e.detail.res.data)
      console.log(res)
      if (res.code && res.code != 200){
        Message.error('后台链接异常！请刷新页面 || 重新登录')
        this.wsReStart() //重连
      }
      if (res.fd){
        this.wsFd = res.fd
      }
      if(!res.msg)
        MessageBox.alert(res.content,'Author：'+ res.author + '  标题：' + res.title, {
          dangerouslyUseHTMLString: true,
          confirmButtonText: '已读',
          type: 'success',
          center: false,
          callback: () => {

          }
        });
      else
        Message.success(res.msg)
    },
    /** 重启 ws 服务 **/
    wsReStart() {
      this.websocket.closeWS()
      this.websocket.createSocket()
    },
  },
  computed: {
    ...mapGetters(['isCollapse', 'userInfo', 'wsState']),
  },
  watch: {
  },
  mounted()
  {
    let userInfo = JSON.parse(localStorage.getItem('user_info'))
    this.handleUserInfo(userInfo)
  },
  created() {
    /***************************************** 开启 webSocket 服务 ****************************************/
    if ( !this.websocket || !this.websocket.Socket.readyState || this.websocket.Socket.readyState === 3 ){
      if(!window.WebSocket || typeof(WebSocket) != "function" || typeof WebSocket === 'undefined' ) {
        Message.error("您的浏览器不支持Websocket通信协议！")
        return
      }
      this.webSocketConnect()

      // 挂载ws到全局使用
      Vue.prototype.$ws = this.websocket
    }
    /********************************************* end ************************************************/
  },
  beforeDestroy()
  {
    /** 离开时 关闭 webSocket 服务 **/
    this.websocket.closeWS()
    // 页面关闭时 记得销毁事件监听，否则会造成多次触发onmessageWS()
    window.removeEventListener('onmessageWS', this.WsMessage)
    /**************************** end *****************************/
  },
  destroyed(){
  },
}
</script>

<style lang="stylus" scoped>
.mars-ape-layout-header .iconfont
  display inline-block
  box-sizing border-box
  font-size 24px
.mars-ape-layout-header
  height 64px
  background #fff
  box-shadow 0 1px 4px rgba(0,21,41,.08)
  position relative
  overflow hidden
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  .header-middle
    width: 130px;
    display: inline-flex;
    justify-content: space-around;
    align-items: center;
.mars-ape-layout-header>.header-left,.mars-ape-layout-header>.header-middle
  float left
  color #595959
.mars-ape-layout-header>.header-right
  float right
  line-height 64px
  overflow hidden
.mars-ape-layout-header>.header-left>.header-index-trigger
  font-size 20px
  height 64px
  cursor pointer
  transition all .3s,padding 0s
  padding 22px 24px
i.header-index-trigger:hover
  background rgba(0,0,0,.025)
.header-right .right-account,.header-right .right-notice
  padding 0 12px
  display inline-block
  transition all .3s
  cursor pointer
  color:rgba(0,0,0,.65)
.right-notice .el-badge
  line-height 24px
.right-account-avatar>img
  width 100%
  display block
.right-account-avatar
  width 24px
  line-height 24px
  border-radius 50%
  display inline-block
  margin 20px 8px 20px 0
  vertical-align top
.right-account-name
  font-size 14px
.right-notice:hover,.right-account:hover
  background rgba(0,0,0,.025)
.el-dropdown-menu__item
  padding 5px 12px
  line-height 22px
  font-weight 400
  color rgba(0,0,0,0.65)
.el-dropdown-menu__item > .iconfont
  margin-right 8px
.el-dropdown-menu
  padding 4px 0
  margin 0px
.el-dropdown-menu__item--divided
  padding-top 0px
  line-height 28px
.el-dropdown-menu__item--divided::before
  margin 0 -12px
.el-dropdown-menu.el-popper
  min-width 160px
  top 56px !important
.el-popper .popper__arrow
  border-width 0px
.el-popper .popper__arrow::after
  border-width 0px

.el-message-box--center
  padding-bottom: 10px;

</style>