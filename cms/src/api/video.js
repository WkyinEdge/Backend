import * as request from '@/utils/request'

export default {
  /**
   * @description 获取视频列表
   * @author YM
   * @date 2019-03-04
   * @param {*} data
   * @returns 
   */
  getVideoList(data) {
    return request.post('/admin/video/list', data)
  },
  /**
   * @description 获取视频分类列表
   * @author YM
   * @date 2019-03-04
   * @returns 
   */
  getVideoCategoryList() {
    return request.post('/admin/video/category_list')
  },
  /**
   * @description 视频保存
   * @author YM
   * @date 2019-02-06
   * @param {*} data
   * @returns 
   */
  saveVideo(data) {
    return request.post('/admin/video/store', data)
  },
  /**
   * @description 获取视频详情
   * @author YM
   * @date 2019-02-11
   * @param {*} id
   * @returns 
   */
  getVideoInfo(id) {
    let data = {id:id}
    return request.post('/admin/video/get_info', data)
  },
  /**
   * @description 根据id删除单条信息
   * @author YM
   * @date 2019-01-19
   * @param {*} data
   * @returns
   */
  deleteVideo(id) {
    let data = {id:id}
    return request.post('/admin/video/delete',data)
  },
  /**
   * @description 获取视频附件列表
   * @author YM
   * @date 2019-12-05
   * @param {*} id
   * @returns 
   */
  getVideoAttachment(id) {
    let data = {id:id}
    return request.post('/admin/video/get_attachment', data)
  },
  /**
   * @description 保存文章附件
   * @author YM
   * @date 2019-12-05
   * @param {*} id
   * @returns 
   */
  saveVideoAttachment(data) {
    return request.post('/admin/video/save_attachment', data)
  },
  /**
   * @description 上
   * @author YM
   * @date 2019-12-05
   * @param {*} id
   * @returns 
   */
  deleteVideoAttachment(id) {
    let data = {id:id}
    return request.post('/admin/video/delete_attachment',data)
  },

}