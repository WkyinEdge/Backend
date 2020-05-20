import * as request from '@/utils/request'

export default {
  /**
   * @description 提交登录信息，进行登录
   * @author YM
   * @date 2019-02-12
   * @returns 
   */
  submitLoginInfo(data) {
    return request.post('/admin/LoginAuth/login',data)
  },
  /**
   * @description 提交退出登录
   * @author YM
   * @date 2019-02-13
   * @returns 
   */
  submitLogout() {
    return request.post('/admin/LoginAuth/logout')
  },
  /**
   * 同步服务器时间
   * @returns {*}
   */
  syncTime() {
    return request.post('/admin/time')
  },
}