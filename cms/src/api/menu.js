import * as request from '@/utils/request'

export default {
  /**
   * @description 获取菜单列表
   * @author YM
   * @date 2019-01-17
   * @returns promise
   */
  getMenuList(data) {
    return request.post('/admin/menu/list',data)
  },
  /**
   * @description 获取权限列表
   * @author YM
   * @date 2019-01-19
   * @returns promise
   */
  getMenuPermissionsList() {
    return request.post('/admin/menu/permissions_list')
  },
  /**
   * @description 保存菜单，新建、编辑的保存都走此方法，却别是有没有主键id
   * @author YM
   * @date 2019-01-19
   * @param {*} data
   * @returns
   */
  saveMenu(data) {
    return request.post('/admin/menu/store',data)
  },
  /**
   * @description 根据id获取单条信息，编辑使用
   * @author YM
   * @date 2019-01-19
   * @param {*} data
   * @returns
   */
  getMenuInfo(id) {
    let data = {id:id}
    return request.post('/admin/menu/get_info',data)
  },
  /**
   * 获取一级菜单信息
   */
  getParentMenu() {
    return request.post('/admin/menu/getParentMenu')
  },
  /**
   * @description 根据id删除单条信息
   * @author YM
   * @date 2019-01-19
   * @param {*} data
   * @returns
   */
  deleteMenu(id) {
    let data = {id:id}
    return request.post('/admin/menu/delete',data)
  },
  /**
   * @description 相同层级菜单排序
   * @author YM
   * @date 2019-01-19
   * @param {*} data
   * @returns
   */
  orderMenu(ids) {
    let data = {ids:ids}
    return request.post('/admin/menu/order',data)
  },

}