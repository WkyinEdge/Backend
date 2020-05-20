<template>
  <div>
    <router-view v-show="$route.matched.length==3"></router-view>
    <PageHeaderLayout v-show="$route.matched.length==2">
      <div class="main-page-content">
        <el-row class="table-header">
          <el-col>
            <el-tooltip effect="dark" content="添加文章" placement="top-start"  v-if="userPermissions.indexOf('video_create') != -1 && buttonType=='icon'" >
              <el-button type="primary" size="medium" icon="iconfont icon-tianjiacaidan2" @click="addButton(0)"></el-button>
            </el-tooltip>
            <el-button type="primary" size="medium" icon="iconfont"  v-if="userPermissions.indexOf('video_create') != -1 && buttonType=='text'"  @click="addButton(0)">添加文章</el-button>
          </el-col>
        </el-row>
        <el-row class="table-search">
          <el-form size="medium" :inline="true" :model="searchCondition" class="demo-form-inline">
            <el-form-item label="分类">
              <el-cascader
                placeholder="选择分类"
                :props="cascaderProps"
                :options="searchCategory"
                v-model="searchCondition.selected_category"
                show-all-levels
                clearable
                filterabl
              ></el-cascader>
            </el-form-item>
            <el-form-item label="标题">
              <el-input v-model="searchCondition.title" placeholder="输入标题" clearable></el-input>
            </el-form-item>
            <el-form-item label="发布时间">
              <el-date-picker
                v-model="searchCondition.time_value"
                type="daterange"
                align="right"
                unlink-panels
                range-separator="至"
                start-placeholder="开始日期"
                end-placeholder="结束日期"
                value-format="yyyy-MM-dd">
              </el-date-picker>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="searchButton">查询</el-button>
            </el-form-item>
          </el-form>
        </el-row>

        <!--<ApeTable ref="apeTable" :data="videoList" :columns="columns" :loading="loadingStaus" :pagingData="pagingData" @switchPaging="switchPaging" highlight-current-row border>
          <el-table-column
            slot="second-column"
            width="64"
            label="序号">
            <template slot-scope="scope">
              <span>{{offset+scope.$index+1}}</span>
            </template>
          </el-table-column>
          <el-table-column
            v-if="buttonType=='icon'"
            width="160"
            label="操作">
            <template slot-scope="scope">
              <span>
                <el-tooltip effect="dark" content="查看" placement="top-start"  v-if="userPermissions.indexOf('video_list') != -1" >
                  <el-button size="mini" icon="iconfont icon-fujian2" @click="videoAttachment(scope.row)"></el-button>
                </el-tooltip>
                <el-tooltip effect="dark" content="编辑" placement="top-start"  v-if="userPermissions.indexOf('video_edit') != -1" >
                  <el-button size="mini" icon="el-icon-edit" @click="editButton(scope.row.id)"></el-button>
                </el-tooltip>
                <el-tooltip effect="dark" content="删除" placement="top-start">
                  <span>
                    <el-popover
                      v-if="userPermissions.indexOf('video_delete') != -1" 
                      placement="top"
                      width="150"
                      v-model="scope.row.visible">
                      <p>确定要删除记录吗？</p>
                      <div style="text-align: right; margin: 0;">
                        <el-button type="text" size="mini" @click="scope.row.visible=false">取消</el-button>
                        <el-button type="danger" size="mini" @click="deleteButton(scope.row.id)">确定</el-button>
                      </div>
                      <el-button slot="reference" type="danger" size="mini" icon="el-icon-delete"></el-button>
                    </el-popover>
                  </span>
                </el-tooltip>
              </span>
            </template>
          </el-table-column>
          <el-table-column
            v-if="buttonType=='text'"
            width="160"
            label="操作">
            <template slot-scope="scope">
              <span>
                <el-button size="mini" v-if="userPermissions.indexOf('video_list') != -1"  @click="videoAttachment(scope.row)">查看</el-button>
                <el-button size="mini" v-if="userPermissions.indexOf('video_edit') != -1"  @click="editButton(scope.row.id)">编辑</el-button>
                <el-popover
                  v-if="userPermissions.indexOf('video_delete') != -1" 
                  placement="top"
                  width="150"
                  v-model="scope.row.visible">
                  <p>确定要删除记录吗？</p>
                  <div style="text-align: right; margin: 0;">
                    <el-button type="text" size="mini" @click="scope.row.visible=false">取消</el-button>
                    <el-button type="danger" size="mini" @click="deleteButton(scope.row.id)">确定</el-button>
                  </div>
                  <el-button slot="reference" type="danger" size="mini">删除</el-button>
                </el-popover>
              </span>
            </template>
          </el-table-column>
        </ApeTable>-->

        <VideoTable ref="apeTable" :data="videoList" :loading="loadingStaus" :pagingData="pagingData" @switchPaging="switchPaging" highlight-current-row border
        >
          <el-table-column slot="second-column" width="64" label="序号" header-align="center" align="center">
            <template slot-scope="scope">
              <span>{{offset+scope.$index+1}}</span>
            </template>
          </el-table-column>

          <el-table-column slot="second-column" label="封面" width="130" header-align="center" align="center">
            <template slot-scope="scope">
              <span>
                <span> <img :src="wanUrl + scope.row.image" :alt="scope.row.title" width="100%"></span>
              </span>
            </template>
          </el-table-column>

          <el-table-column slot="third-column" label="标题" width="160" header-align="center" align="center">
            <template slot-scope="scope">
                    <span class="more-info-display">
                      <span class="is-label">ID号：</span>
                      <span class="is-value">{{scope.row.id}}</span>
                    </span>
                    <span class="more-info-display">
                      <span class="is-label">标题：</span>
                      <span class="is-value">{{scope.row.title}}</span>
                    </span>
            </template>
          </el-table-column>

          <el-table-column slot="fourth-column" label="发布者" width="250" header-align="center" align="center">
            <template slot-scope="scope">
                    <span class="more-info-display">
                      <span class="is-label">分类：</span>
                      <span class="is-value">{{categoryList[scope.row.category_id]}}</span>
                    </span>
                    <span class="more-info-display">
                      <span class="is-label">作者：</span>
                      <span class="is-value">{{scope.row.uploader_name}}</span>
                    </span>
                    <span class="more-info-display">
                      <span class="is-label">时间：</span>
                      <span class="is-value">{{scope.row.create_time}}</span>
                    </span>
            </template>
          </el-table-column>

          <el-table-column slot="fifth-column" label="内容" header-align="center" align="center">
            <template slot-scope="scope">
              <div class="cell">
                <span>
                  <span>{{scope.row.content}}</span>
                </span>
              </div>
            </template>
          </el-table-column>

          <el-table-column slot="sixth-column" label="状态" width="160" header-align="center" align="center">
            <template slot-scope="scope">
              <div class="cell">
                <span>
                  <span>
                    <span class="more-info-display">
                      <span class="is-label">发布：</span>
                      <span class="is-value">{{scope.row.status === 1 ? '已发布': '未发布'}}</span>
                    </span>
                  </span>
                  <span>
                    <span class="more-info-display">
                      <span class="is-label">置顶：</span>
                      <span class="is-value">{{scope.row.is_top === 1 ? '已置顶': '未置顶'}}</span>
                    </span>
                  </span>
                  <span>
                    <span class="more-info-display">
                      <span class="is-label">推荐：</span>
                      <span class="is-value">{{scope.row.is_recommend === 1 ? '已推荐': '未推荐'}}</span>
                    </span>
                  </span>
                </span>
              </div>
            </template>
          </el-table-column>

          <el-table-column
                  v-if="buttonType=='icon'"
                  width="160"
                  label="操作">
            <template slot-scope="scope">
              <span>
                <el-tooltip effect="dark" content="查看" placement="top-start"  v-if="userPermissions.indexOf('video_list') != -1" >
                  <el-button size="mini" icon="iconfont icon-fujian2" @click="videoAttachment(scope.row)"></el-button>
                </el-tooltip>
                <el-tooltip effect="dark" content="编辑" placement="top-start"  v-if="userPermissions.indexOf('video_edit') != -1" >
                  <el-button size="mini" icon="el-icon-edit" @click="editButton(scope.row.id)"></el-button>
                </el-tooltip>
                <el-tooltip effect="dark" content="删除" placement="top-start">
                  <span>
                    <el-popover
                            v-if="userPermissions.indexOf('video_delete') != -1"
                            placement="top"
                            width="150"
                            v-model="scope.row.visible">
                      <p>确定要删除记录吗？</p>
                      <div style="text-align: right; margin: 0;">
                        <el-button type="text" size="mini" @click="scope.row.visible=false">取消</el-button>
                        <el-button type="danger" size="mini" @click="deleteButton(scope.row.id)">确定</el-button>
                      </div>
                      <el-button slot="reference" type="danger" size="mini" icon="el-icon-delete"></el-button>
                    </el-popover>
                  </span>
                </el-tooltip>
              </span>
            </template>
          </el-table-column>
          <el-table-column
                  v-if="buttonType=='text'"
                  width="160"
                  label="操作">
            <template slot-scope="scope">
              <span>
                <el-button size="mini" v-if="userPermissions.indexOf('video_list') != -1"  @click="videoAttachment(scope.row)">查看</el-button>
                <el-button size="mini" v-if="userPermissions.indexOf('video_edit') != -1"  @click="editButton(scope.row.id)">编辑</el-button>
                <el-popover
                        v-if="userPermissions.indexOf('video_delete') != -1"
                        placement="top"
                        width="150"
                        v-model="scope.row.visible">
                  <p>确定要删除记录吗？</p>
                  <div style="text-align: right; margin: 0;">
                    <el-button type="text" size="mini" @click="scope.row.visible=false">取消</el-button>
                    <el-button type="danger" size="mini" @click="deleteButton(scope.row.id)">确定</el-button>
                  </div>
                  <el-button slot="reference" type="danger" size="mini">删除</el-button>
                </el-popover>
              </span>
            </template>
          </el-table-column>

        </VideoTable>

      </div>
    </PageHeaderLayout>
    <ApeDrawer :drawerData="drawerData"  @drawerClose="drawerClose" >
      <template slot="ape-drawer">
        <el-row class="table-header">
          <el-col>
            <el-tooltip effect="dark" content="添加附件" placement="top-start"  v-if="userPermissions.indexOf('video_attachment') != -1 && buttonType=='icon'" >
              <el-button type="primary" size="medium" icon="iconfont icon-tianjiacaidan2" @click="addAttachment()"></el-button>
            </el-tooltip>
            <el-button type="primary" size="medium" icon="iconfont"  v-if="userPermissions.indexOf('video_attachment') != -1 && buttonType=='text'"  @click="addAttachment()">添加附件</el-button>
          </el-col>
        </el-row>
        <ApeTable :data="attachmentList" :columns="attachmentColumns" :loading="loadingStaus" highlight-current-row border>
          <el-table-column
            slot="second-column"
            width="60"
            label="序号">
            <template slot-scope="scope">
              <span>{{offset+scope.$index+1}}</span>
            </template>
          </el-table-column>
          <el-table-column
            v-if="buttonType=='icon'"
            label="操作"
            width="80">
            <template slot-scope="scope">
              <el-tooltip effect="dark" content="删除" placement="top-start">
                <span>
                  <el-popover
                    v-if="userPermissions.indexOf('video_attachment') != -1" 
                    placement="top"
                    width="150"
                    v-model="scope.row.visible">
                    <p>确定要删除记录吗？</p>
                    <div style="text-align: right; margin: 0;">
                      <el-button type="text" size="mini" @click="scope.row.visible=false">取消</el-button>
                      <el-button type="danger" size="mini" @click="deleteVideoAttachmentButton(scope.row.id)">确定</el-button>
                    </div>
                    <el-button slot="reference" type="danger" size="mini" icon="el-icon-delete"></el-button>
                  </el-popover>
                </span>
              </el-tooltip>
            </template>
          </el-table-column>
          <el-table-column
            v-if="buttonType=='text'"
            label="操作"
            width="80">
            <template slot-scope="scope">
              <el-popover
                v-if="userPermissions.indexOf('video_attachment') != -1" 
                placement="top"
                width="150"
                v-model="scope.row.visible">
                <p>确定要删除记录吗？</p>
                <div style="text-align: right; margin: 0;">
                  <el-button type="text" size="mini" @click="scope.row.visible=false">取消</el-button>
                  <el-button type="danger" size="mini" @click="deleteVideoAttachmentButton(scope.row.id)">确定</el-button>
                </div>
                <el-button slot="reference" type="danger" size="mini" v-if="buttonType=='text'">删除</el-button>
              </el-popover>
            </template>
          </el-table-column>
        </ApeTable>
      </template>
    </ApeDrawer>
    <ModalDialog :dialogData="dialogData" @dialogConfirm="handleConfirm" @dialogClose="dialogClose">
      <template slot="content">

          <video-player v-if="dialogData.visible" :videoSrc="video.url" :poster="video.cover"></video-player>

      </template>
    </ModalDialog>
<!--    <video-player videoSrc="qs" sources02="http://192.168.0.102:8080/admin/upload/video/2020Apr/e66aa1971ad19dc4.mp4" poster="https://i.loli.net/2019/06/06/5cf8c5d9c57b510947.png"></video-player>-->

  </div >
</template>

<script>
import PageHeaderLayout from '@/layouts/PageHeaderLayout'
import ApeTable from '@/components/ApeTable'
import ApeDrawer from '@/components/ApeDrawer'
import ApeUploader from '@/components/ApeUploader'
import ModalDialog from '@/components/ModalDialog'
import { mapGetters } from 'vuex'
import VideoPlayer from "@/components/VideoPlayer";
import VideoTable from '@/components/VideoTable'


export default {
  components: {
    PageHeaderLayout,
    ApeDrawer,
    ApeTable,
    ApeUploader,
    ModalDialog,
    VideoPlayer,
    VideoTable
  },
  data() {
    return {
      wanUrl: 'http://210oe79321.51mypc.cn',
      video: {
        url: 'https://api.dogecloud.com/player/get.mp4?vcode=5ac682e6f8231991&userId=17&ext=.mp4',
        cover: 'https://i.loli.net/2019/06/06/5cf8c5d9c57b510947.png'
      },
      // element的cascader的props值
      cascaderProps:{
        label:'name',
        value:'id',
      },
      searchCategory: [],
      // 搜索条件
      searchCondition:{
        selected_category:[]
      },
      loadingStaus: true,
      columns: [
        {
          title: '封面',
          type: 'image',
          value: 'image',
          width: 130
        },
        {
          title: '标题',
          value: [
            {label:'ID号：',value:'id'},
            {label:'标题：',value:'name',value_alias:'title'},
          ],
          width: 160
        },
        {
          title: '发布者',
          value: [
            {label:'分类：',value:'category_id'},
            {label:'作者：',value:'uploader'},
            {label:'时间：',value:'create_time'},
          ],
          width: 250
        },
        {
          title: '内容',
          value: 'content',
        },
        {
          title: '状态',
          value: [
            {label:'发布：',value:'status'},
            {label:'置顶：',value:'is_top'},
            {label:'推荐：',value:'is_recommend'},
          ],
          width:160
        }


      ],
      // 表格列表数据
      videoList:[],
      // 分页信息
      pagingData:{
        is_show: true,
        layout: 'total, sizes, prev, pager, next, jumper',
        total: 0
      },
      // 分页的offset,序号列使用
      offset:0,
      categoryList : [],
      // 已上传文件列表
      uploadFileList:[],
      // 表单验证
      rules: {
        title: [
          {required: true, message: '输入标题', trigger: 'blur' },
        ],
        type: [
          {required: true, message: '选择类型', trigger: 'blur' },
        ],
        is_new_win: [
          {required: true, message: '选择打开发方式', trigger: 'blur' },
        ],
        is_show: [
          {required: true, message: '选择状态', trigger: 'blur' },
        ],
        image: [
          {required: true, message: '上传图片', trigger: 'blur', validator:this.apeUploaderVerify},
        ],
      },
      // 抽屉数据，附件列表
      drawerData: {
        visible: false,
        loading: true,
        loading_text: '玩命加载中……',
        // direction: 'right',
        title: '',
        width_height: '640px',
        show_footer:false,
        // mask: false,
        // close_name: '关 闭',
        // confirm_name: '打 印',
      },
      // 附件列表
      attachmentList:[],
      // 附件列表字段定义
      attachmentColumns: [
        {
          title: '标题',
          value: 'title',
        }
      ],
      // 添加附件
      dialogData:{
        visible: false,
        title: '',
        //width: '24%',
        loading: true,
        modal: false
      },
      // 附件表单内容
      formData:{},
      // 附件添加表单验证
      dialogRules: {
        title: [{required: true, message: '输入标题', trigger: 'blur' }],
      },
      // 附件类型
      attachmentType:['application/pdf','application/caj','','application/zip','application/rar','application/doc'],
      // 已上传附件列表
      attachmentUploadFileList:[],
      // 当前需要操作附件的文章id
      currentVideoId:0,
    }
  },
  computed: {
    ...mapGetters(['userPermissions','buttonType']),
    /*handleCategory(category_id){
      //let name;
      for (let [k,v] of this.searchCategory) {
        if (category_id === v.id){
          return v.name
        }
      }
    },*/
  },
  watch: {
    "$route.matched" : function(n,o) {
      console.log(o)
      if (n.length === 2) {
        this.initVideoList()
      }
    }
  },
  methods: {
    // 搜索
    searchButton() {
      this.initVideoList()
    },
    // 切换页码操作
    async switchPaging() {
      this.initVideoList()
    },
    // 响应添加按钮
    async addButton() {
      this.$router.push(this.$route.path+'/create')
    },
    // 响应编辑按钮
    async editButton(id) {
      this.$router.push(this.$route.path+'/'+id+'/edit')
    },
    // 相应删除按钮
    async deleteButton(id) {
      let info = await this.$api.deleteVideo(id)
      if (info) {
        this.$nextTick(() => {
          this.initVideoList('del')
        })
      } else {
        this.$message.error(info)
      }

    },
    // 弹窗播放
    videoAttachment(row) {

      this.dialogData.visible = true
      this.dialogData.loading = false
      this.video.url = this.wanUrl + row.url
      this.video.cover = this.wanUrl + row.image
      //console.log(this.video)
    },
    // 处理抽屉关闭
    drawerClose() {
      this.drawerData.visible = false
      this.drawerData.loading = true
    },
    // 初始化视频列表
    async initVideoList(type) {
      this.loadingStaus=true
      let pagingInfo = this.$refs['apeTable'].getPagingInfo(type)
      let searchCondition = this.handleSearchCondition()
      Object.assign(searchCondition,pagingInfo)
      let {list,pages}= await this.$api.getVideoList(searchCondition)
      if (list) {
        this.videoList = []
        this.$nextTick(() => {
          this.videoList=list
        })
      }
      if (pages) {
        this.pagingData.total = pages.total
        this.offset = pages.offset
      }
      this.loadingStaus=false
    },
    // 处理搜索条件
    handleSearchCondition() {
      let condition = {}
      if (this.searchCondition.selected_category.length) {
        condition.category_id = this.searchCondition.selected_category[this.searchCondition.selected_category.length-1]
      }
      if (this.searchCondition.title) {
        condition.title = this.searchCondition.title
      }
      if (this.searchCondition.time_value) {
        condition.start_time = this.searchCondition.time_value[0]
        condition.end_time = this.searchCondition.time_value[1]
      }

      return condition
    },
    // 获取文章分类列表
    async getCategoryList() {
      // 获取文章分类列表
      let list = await this.$api.getVideoCategoryList()
      this.searchCategory = list
      if (list){
        let arr = Object.entries(list)
        for (let [k,v] of arr){
          this.categoryList[v.id] = v.name
        }
      }

    },
    // 初始化文件附件列表
    async initVideoAttachmentList() {
      let list = await this.$api.getVideoAttachment(this.currentVideoId)
      this.attachmentList = list
    },
    // 附件上传成功回调
    handleAttachmentUploadSuccess(file, fileList) {
      this.formData.attachment_id = file.id
      this.attachmentUploadFileList = fileList
    },
    // 附件删除回调
    handleAttachmentUploadRemove(file, fileList) {
      this.formData.attachment_id = 0
      this.attachmentUploadFileList = fileList
    },
    // form数据提交，请求接口,文章附件
    async formSubmit() {
      this.dialogData.loading_text = '玩命提交中……'
      this.dialogData.loading = true
      this.formData.video_id = this.currentVideoId
      let id = await this.$api.saveVideoAttachment(this.formData)
      this.$nextTick(() => {
        this.dialogClose()
      })
      this.$nextTick(() => {
        if (id) {
          this.initVideoAttachmentList()
        }
      })
      this.$nextTick(() => {
        this.$message.success('保存成功!')
      })
    },
    // 处理模态框，确定事件
    handleConfirm() {
      // 调用组件的数据验证方法
      this.dialogData.visible = false
      this.dialogData.loading = true
      this.video.url = ''
      this.video.cover = ''
    },
    // 处理模态框，关闭事件
    dialogClose() {
      this.dialogData.visible = false
      this.dialogData.loading = true
      this.video.url = ''
      this.video.cover = ''
    },
    // 响应添加附件按钮
    async addAttachment() {
      this.dialogData.visible = true
      this.dialogData.loading = false
      this.dialogData.title = '添加附件'
    },
    // 相应删除文章附件按钮
    async deleteVideoAttachmentButton(id) {
      let info = await this.$api.deleteVideoAttachment(id)
      if (info == 'ok') {
        this.$nextTick(() => {
          this.initVideoAttachmentList()
        })
      } else {
        this.$message.error(info)
      }

    },
  },

  mounted() {
    this.getCategoryList()
    this.initVideoList()
  },
}
</script>

<style lang="stylus">
  .el-button
    margin-right 4px
    margin-bottom 4px
  .table-header
    margin-bottom 12px
  .el-input-group__prepend, .el-input-group__append
    background #ffffff
    padding 0 12px
  .el-input-group__append
    color #ffffff
    background #1890ff
  .el-popover .el-checkbox-group .el-checkbox
    margin-left 0px
    margin-right 12px
</style>
