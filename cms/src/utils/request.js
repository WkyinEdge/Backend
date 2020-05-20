import axios from 'axios'
import router from '@/router/router'
import { Message,MessageBox } from 'element-ui'
import store from '@/store'
import {/*decrypt,*/ encrypt, encryptParams, decryptParams } from './utils.js';
//import { getCookie } from './utils.js';

// 前端自定义错误信息
// const codeMessage = {
//   200: '服务器成功返回请求的数据。',
//   201: '新建或修改数据成功。',
//   202: '一个请求已经进入后台排队（异步任务）。',
//   204: '删除数据成功。',
//   400: '发出的请求有错误，服务器没有进行新建或修改数据的操作。',
//   401: '用户没有权限（令牌、用户名、密码错误）。',
//   403: '用户得到授权，但是访问是被禁止的。',
//   404: '发出的请求针对的是不存在的记录，服务器没有进行操作。',
//   406: '请求的格式不可得。',
//   410: '请求的资源被永久删除，且不会再得到的。',
//   422: '当创建一个对象时，发生一个验证错误。',
//   500: '服务器发生错误，请检查服务器。',
//   502: '网关错误。',
//   503: '服务不可用，服务器暂时过载或维护。',
//   504: '网关超时。',
// }

const CancelToken = axios.CancelToken
const source = CancelToken.source();
const baseURL = process.env.VUE_APP_AXIOS_BASE_URL;

// create an axios instance实例
const service = axios.create({
  baseURL: baseURL, // api 的 base_url
  timeout: 1000*30 // request timeout
})

// request interceptor 请求拦截器
service.interceptors.request.use(
  config => {

    // cookie 方式验证登录：
    //config.headers['client-type'] = 'admin'   // 标识这是后台管理系统，用于区分 前台|后台客户端，这决定了是用cookie 还是token
    //let tid = getCookie('esw_session');

    // token 方式验证登录：
    config.headers['client-type'] = 'app'
    let tid = localStorage.getItem( 'token' )

    tid = tid ? tid : ''

    // 同步服务器时间差
    let syncTime = parseInt( localStorage.getItem('syncTime') )
    syncTime = syncTime ? syncTime : 0

    // 组建 sign原始数据
    let signData = { tid: tid, time: Date.now() + syncTime }

    // 为所有请求增加 tokenId 和 sign 配合后端进行 api安全验证
    config.headers['tid'] = tid
    config.headers['sign'] = encrypt( JSON.stringify(signData) )

    // 加密请求参数
    config.data = encryptParams(config.data)

    return config;
  },
  error => {
   return Promise.reject(error);
  }
)

// response interceptor 响应拦截器
service.interceptors.response.use(
  // response => response,
  /**
   * 下面的注释为通过在response里，自定义code来标示请求状态
   * 当code返回如下情况则说明权限有问题，登出并返回到登录页
   * 如想通过 xmlhttprequest 来状态码标识 逻辑可写在下面error中
   * 以下代码均为样例，请结合自生需求加以修改，若不需要，则可删除
   */
  response => {
    const res = response.data
    if (res.code === 200) {

      return decryptParams(res)

    } else {
      Message.error(res.msg)
      if ( /*res.code === 1001 || */res.code === 1002 || res.code === 1003 || res.code === 1004 || res.code === 2001) {
        localStorage.removeItem('user_info')
        localStorage.removeItem('token')
        router.push('/login')
      }
      // 改变全局loading状态，解决业务错误引起按钮不可用
      store.commit('changeLoadingStatus',false)
      return Promise.reject(res)
    }
  },
  error => {
    // 移除本地用户数据,开发时请注释
    localStorage.removeItem('user_info')
    localStorage.removeItem('token')
    // 判断标志，防止出发多次弹窗
    if (store.getters.respErrMsgBoxMark === false) {
      let errMsg = 'Code：' + error.response.data.code + ' Msg：' + error.response.data.msg
      MessageBox.alert(errMsg,'ERROR',{
        confirmButtonText: '确定',
        type: 'error',
        callback: () => {
          router.push({path:'/login'})
        }
      })
      // 修改标志
      store.commit('changeRespErrMsgBoxMark',true)
    }
    Promise.reject(error.response)
    return []
  }
)
/**
 * 封装get请求
 * @param {*} url 请求路径
 * @param {*} params 参数
 * @returns {Promise}
 */
export function get(url,params){
  return new Promise((resolve,reject) => {
    service({
      method: 'get',
      url,
      params:params,
      cancelToken: source.token
    }).then(response => {
      resolve(response);
    }).catch(err => {
      //if (typeof(err.code)==='undefined') {
        reject(err)
      //}
    })
  })
}

/**
 * 封装get请求 alias
 * @param {*} url 请求路径
 * @param {*} params 参数
 * @returns {Promise}
 */
export function fetch(url,params){
  return new Promise((resolve,reject) => {
    service({
      method: 'get',
      url,
      params:params,
      cancelToken: source.token
    }).then(response => {
      resolve(response);
    }).catch(err => {
      //if (typeof(err.code)==='undefined') {
        reject(err)
      //}
    })
  })
}
/**
 * 封装post请求
 * @param {*} url 请求路径
 * @param {*} params 参数
 * @returns {Promise}
 */
export function post(url,params){

  /*let Params = null;
  if (params){
    Params = new URLSearchParams()
    let arr = Object.entries(params)
    for (let [k,v] of arr){
      //console.log(k + '--' + v)
      Params.append(k,v)
    }
  }*/

  return new Promise((resolve,reject) => {
    service({
      method: 'post',
      url,
      data:params,
      cancelToken: source.token
    }).then(response => {
      resolve(response);
    }).catch(err => {
      //if (typeof(err.code)==='undefined') {
        reject(err)
      //}
    })
  })
}

// export default service
