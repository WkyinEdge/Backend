import * as request from '@/utils/request'

export default {
  /**
   * @description 提交推送信息
   * @param {*} data
   * @returns 
   */
  onlinePush(data) {
    return request.post('/admin/msgPush/sendWsMsg', data)
  },
  getUsers() {
    return request.post('/admin/msgPush/userList');
  }
}