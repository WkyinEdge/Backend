export default  {
  path: 'video',
  component: () => import(/* webpackChunkName: "article-list" */ '@/pages/video/VideoList'),
  meta: {title:'视频管理'},
  children: [
    {
      path: 'create',
      component: () => import( '@/pages/video/Create'),
      meta: {title:'添加视频'},
    },
    {
      path: ':video_id/edit',
      component: () => import( '@/pages/video/CreateEdit'),
      meta: {title:'编辑视频'},
    }
  ]
}