export default  {
  path: 'push',
  component: () => import(/* webpackChunkName: "article-list" */ '@/pages/msg_push/onlinePush'),
  meta: {title:'消息推送'}
}