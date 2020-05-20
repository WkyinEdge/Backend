<template>
  <div>
    <PageHeaderLayout>
      <div class="main-page-content"
        v-loading="loadingStaus"
        :element-loading-text="loadingText">
        <el-row :gutter="48">
          <el-form ref="articleForm" :model="formData" :rules="rules" label-width="80px">
            <el-col :span="18" class="content-left">

              <el-form-item label="分类" prop="selectedList" ref="categoryItem">
                <el-cascader
                  placeholder="选择分类"
                  :props="cascaderProps"
                  :options="categoryList"
                  v-model="selectedList"
                  @change="cascaderChange"
                  show-all-levels
                  clearable
                  filterable>
                </el-cascader>
              </el-form-item>

              <el-form-item label="标题" prop="title">
                <el-input v-model="formData.title"></el-input>
              </el-form-item>
              <el-form-item label="作者" prop="uploader_name">
                <el-input v-model="formData.uploader_name" :disabled="true"></el-input>
              </el-form-item>
              <el-form-item label="内容" prop="content">
                <el-input type="textarea" v-model="formData.content"></el-input>
              </el-form-item>

              <div class="checkbox-g">
                <el-form-item label="审核发布" prop="status">
                  <el-checkbox  v-model="status" border>发布</el-checkbox>
                </el-form-item>
                <el-form-item label="是否置顶" prop="is_top">
                  <el-checkbox v-model="is_top" border>置顶</el-checkbox>
                </el-form-item>
                <el-form-item label="是否推荐" prop="is_recommend">
                  <el-checkbox v-model="is_recommend" border>推荐</el-checkbox>
                </el-form-item>
              </div>

              <div class="left-button" >
                <el-button size="medium" type="primary" :disabled="loadingStaus" @click="saveConfirm">保存</el-button>
                <el-button size="medium" @click="returnList">返回</el-button>
              </div>

            </el-col>
            <el-col class="content-right" :span="6">
              <!--<el-form-item label="发布状态" prop="status">
                <el-checkbox  v-model="status" border>发布</el-checkbox>
              </el-form-item>
              <el-form-item label="是否置顶" prop="is_top">
                <el-checkbox v-model="is_top" border>置顶</el-checkbox>
              </el-form-item>
              <el-form-item label="是否推荐" prop="is_recommend">
                <el-checkbox v-model="is_recommend" border>推荐</el-checkbox>
              </el-form-item>


              <div class="right-button" >
                <el-button size="medium" type="primary" :disabled="loadingStaus" @click="saveConfirm">保存</el-button>
                <el-button size="medium" @click="returnList">返回</el-button>
              </div>-->
            </el-col>
          </el-form>
        </el-row>
      </div>
    </PageHeaderLayout>
  </div >
</template>

<script>
import PageHeaderLayout from '@/layouts/PageHeaderLayout'
import ApeUploader from '@/components/ApeUploader'
import ApeEditor from '@/components/ApeEditor'
import { mapGetters } from 'vuex'

export default {
  components: {
    PageHeaderLayout,
  },
  data() {
    return {
      cs:1,
      hearTitle: '基本设置',
      loadingStaus: true,
      loadingText: '玩命提交中……',
      articleId:0, // 文章id，0表示创建，非零为编辑
      // element的cascader的props值
      cascaderProps:{
        label:'name',
        value:'id',
      },
      // 分类列表
      categoryList:[],
      // 已选中的分类列表，用于cascader组件选中效果
      selectedList:[],
      // 文章基本信息表单结构
      formData:{article_status:[]},
      // 已上传图片列表
      uploadFileList:[],
      // 文章基本信息规则
      rules: {
        title: [
          {required: true, message: '输入文章名称', trigger: 'blur' },
        ],
        selectedList: [{required: true, message: '选择分类', trigger: 'change', validator:this.cascaderVerify}],
      },
      // 附件类型
      //attachmentType:['application/pdf','application/caj','','application/zip','application/rar','application/doc'],
      // 已上传附件列表
      //attachmentUploadFileList:[]
    }
  },
  computed: {
    ...mapGetters(['userPermissions']),
    status:{
      get: function () {
        return this.formData.status === 1
      },
      set: function (newValue) {
        this.formData.status = newValue ? 1 : 0
      }
    },
    is_top:{
      get: function () {
        return this.formData.is_top === 1
      },
      set: function (newValue) {
        this.formData.is_top = newValue ? 1 : 0
      }
    },
    is_recommend:{
      get: function () {
        return this.formData.is_recommend === 1
      },
      set: function (newValue) {
        this.formData.is_recommend = newValue ? 1 : 0
      }
    }
  },
  methods: {
    // 返回列表
    returnList() {
      this.$router.push('/video')
    },
    // 级联菜单组件change事件
    cascaderChange(v) {
      if (v.length) {
        this.formData.category_id = v[(v.length-1)]
        this.formData.category_ids = v
      }
    },
    // 级联菜单自定义验证，选择分类
    cascaderVerify(rule, value, callback) {
      if (rule.required && !this.selectedList.length) {
        callback(new Error(rule.message))
      }
      callback()
    },
    // 确认保存按钮
    async saveConfirm() {
      // 调用组件的数据验证方法
      this.$refs['articleForm'].validate((valid) => {
        if (valid) {
          this.formData.update_time = parseInt(Date.now() / 1000)
          console.log(this.formData.update_time)
          this.formSubmit()
        } else {
          this.$message.error('数据验证失败，请检查必填项数据！')
        }
      })
      return true
    },
    // 文章相关信息的保存处理
    async formSubmit() {
      this.loadingStaus = true
      let id = await this.$api.saveVideo(this.formData)
      if (id) {
        this.$notify.success('保存成功！')
      }
        // this.$router.push(this.$route.matched[1].path+'/'+id+'/edit')
        this.$router.push(this.$route.matched[1].path)
        // this.initArticleForm()

      this.$nextTick(() => {
        this.loadingStaus = false
      }) 
    },
    // 初始化文章基本信息
    async initArticleForm() {
      // 获取文章分类列表
      let list = await this.$api.getVideoCategoryList()
      this.categoryList = list

      if (this.$route.params.video_id) {
        this.video_id = this.$route.params.video_id
      }
      if (this.video_id) {
        let info = await this.$api.getVideoInfo(this.video_id)
        this.formData = info
        this.selectedList = [info.category_id]
      }
    },
  },
  async mounted() {
    this.loadingStaus = true
    this.initArticleForm()
    this.$nextTick(() => {
      this.loadingStaus = false
    }) 
  },
}
</script>

<style lang="stylus">
  .content-left
    min-height 640px
    border-right 1px solid  #e8e8e8
    .el-cascader
      display block
      width 320px
    .checkbox-g
      display: flex;
      .el-form-item
        margin-right: 40px;
  .content-right
    .right-button
      border-top 1px solid #e8e8e8e8
      padding 12px 12px
    .el-form-item__label
      float none
      display inline-block
      text-align left
      padding 0 0 10px 0
    .el-form-item__content
      margin-left 0px !important
      .checkbox-g
        display: flex;
        .el-form-item
          margin-right: 30px;
  .left-button
    border-top 1px solid #e8e8e8e8
    padding 12px 12px
    width: 70%;
    display: flex;
    justify-content: space-evenly;
  .el-input,.el-textarea
    width: 70%;
</style>
