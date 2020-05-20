//import {Message} from 'element-ui'
import store from '../store/index'

export function WebSocketClient(token = 'no', WSS_URL = `ws://192.168.0.128:9501`) {

    this.WSS_URL = WSS_URL;
    this.token = token;  // 不需要权限验证可设置为 'no'
    this.Socket = '';
    this.setIntervalWesocketPush = null;


    /**建立连接 */
    this.createSocket = () => {
        if (!this.Socket || this.Socket.readyState !== 1) {
            console.log('建立websocket连接')
            this.Socket = new WebSocket(this.WSS_URL + (this.token === 'no' ? '' : this.token))
            this.Socket.onopen = this.onopenWS
            this.Socket.onmessage = this.onmessageWS
            this.Socket.onerror = this.onerrorWS
            this.Socket.onclose = this.oncloseWS
        } else {
            console.log('websocket已连接')
        }
    };
    /**打开WS之后发送心跳 */
    this.onopenWS = () => {
        // vuex改变ws连接状态
        store.commit('changeWsState',1)
        this.sendPing() //发送心跳
        console.log('onOpen')
    };

    /**WS数据接收统一处理 */
    this.onmessageWS = (e) => {
        window.dispatchEvent(new CustomEvent('onmessageWS', {
            detail: {
                res: e
            }
        }))
    };
    /**发送数据
     * @param eventType
     */
    this.sendWsPush = (eventTypeArr) => {
        let obj = eventTypeArr ? eventTypeArr : {
            class: 'Index',
            action: 'ping',
            msg: 'ping'
        }
        if (this.Socket !== null && this.Socket.readyState === 3) {
            this.Socket.close()
            this.createSocket() //重连
        } else if (this.Socket.readyState === 1) {
            this.Socket.send(JSON.stringify(obj))
        } else if (this.Socket.readyState === 0) {
            setTimeout(() => {
                this.Socket.send(JSON.stringify(obj))
            }, 3000)
        }
    };

    /**连接失败重连 */
    this.onerrorWS = (e) => {
        console.log(e)
        clearInterval(this.setIntervalWesocketPush)
        console.log('onerrorWS')
        this.Socket.close()
        this.createSocket() //重连
    };

        /**关闭WS */
    this.oncloseWS = () => {
        clearInterval(this.setIntervalWesocketPush)
        // vuex改变ws连接状态
        store.commit('changeWsState',0)

        console.log('websocket已断开')
    };
    /** 主动关闭 */
    this.closeWS = () => {
        this.Socket.close()
        //console.log('websocket已关闭')
    };
    /**发送心跳 */
    this.sendPing = () => {
        this.setIntervalWesocketPush = setInterval(() => {
            this.Socket.send('')
        }, 10000)
    };
}
