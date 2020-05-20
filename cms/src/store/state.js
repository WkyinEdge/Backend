// 跟级别的sate
const state = {
  isCollapse : false,
  menuList : [],
  routePath : null,
  userInfo: {},
  userPermissions : [],
  // 错误响应弹窗标志，避免重复弹出
  responseErrorMessageBoxMark:false,
  // 按钮类型
  buttonType : process.env.VUE_APP_BUTTON_TYPE,
  // 全局的loading状态控制
  loadingStatus: false,
  // 全局webSocket状态控制
  wsState:0,
}

export default state