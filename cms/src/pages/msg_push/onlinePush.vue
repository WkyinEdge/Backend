<template>
  <div>
    <PageHeaderLayout>
      <div class="main-page-content"
        v-loading="loadingStaus"
        :element-loading-text="loadingText">
        <el-row :gutter="48">
          <el-form ref="articleForm" :model="formData" :rules="rules" label-width="80px">
            <el-col :span="18" class="content-left">
              <el-form-item label="标题" prop="title">
                <el-input v-model="formData.title"></el-input>
              </el-form-item>
              <!--<el-form-item label="内容" prop="content">
                <el-input type="textarea" v-model="formData.content"></el-input>
              </el-form-item>-->
              <!--<el-form-item label="附加内容" prop="otherContent">
                <el-input type="textarea" v-model="formData.otherContent"></el-input>
              </el-form-item>-->
              <el-form-item label="推送给谁" prop="selectedList" ref="categoryItem">
                <el-cascader
                        placeholder="选择"
                        :props="cascaderProps"
                        :options="categoryList"
                        v-model="selectedList"
                        @change="cascaderChange"
                        show-all-levels
                        clearable
                        filterable>
                </el-cascader>
                <div class="right-button" >
                  <el-button type="primary" :disabled="loadingStaus" @click="send">保存</el-button>
                  <el-button @click="returnList">返回</el-button>
                </div>
              </el-form-item>

              <el-form-item v-if="showUserTable"  label="请选择：" prop="user_list">

                <el-table ref="multipleTable" :data="userList" tooltip-effect="dark" align="center" style="width: 100%"
                        @selection-change="handleSelectionChange">
                  <el-table-column type="selection" width="30">
                  </el-table-column>

                  <el-table-column prop="is_online" label="状态" align="center" width="120">
                    <template slot-scope="scope">
                      <span v-if="scope.row.is_online === 1" style="color: green">✓ 在线</span>
                      <span v-else style="color: red;">× 离线</span>
                    </template>
                  </el-table-column>
                  <el-table-column prop="username" label="用户名" align="center" width="120">

                  </el-table-column>
                  <el-table-column prop="nickname" label="昵称" align="center" width="120">

                  </el-table-column>
                  <el-table-column prop="mobile" label="手机号" align="center" width="120" show-overflow-tooltip>

                  </el-table-column>
                  <el-table-column prop="email" label="邮箱" align="center"  width="120"  show-overflow-tooltip>

                  </el-table-column>
                </el-table>


              </el-form-item>

              <el-form-item label="内容" prop="content">
                <ApeEditor :init-html="formData.content" :editorHeight="480" @handleTinymceInput="handleTinymceInput"></ApeEditor>
              </el-form-item>
              <!--<div class="right-button" >
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
import ApeEditor from '@/components/ApeEditor'
import ApeTable from '@/components/ApeTable'

export default {
  components: {
    PageHeaderLayout,
    ApeEditor,
    ApeTable
  },
  data() {
    return {
      showUserTable: false,
      // 表格列表数据
      userList:[
        // {id: "1", is_online:1, username: "ceshi199",nickname:'武', mobile: "13999999999", email: '157@qq.com'},
      ],

      loadingStaus: true,
      loadingText: '玩命提交中……',
      articleId:0, // 文章id，0表示创建，非零为编辑

      // 定义格式
      cascaderProps:{
        label:'text',
        value:'category_id',
      },
      // 分类列表
      categoryList:[
        {text: '所有人', category_id: 1},
        {text: '仅在线', category_id: 2},
        {text: '指定用户', category_id: 3}],

      // 已选中的分类列表，用于cascader组件选中效果
      selectedList:[],
      // 表单内容
      formData:{uids:[]},
      // 文章基本信息规则
      rules: {
        title: [
          {required: true, message: '请输入名称', trigger: 'blur' },
        ],
        content: [
          {required: true, message: '输入内容', trigger: 'blur' },
        ],
        selectedList: [
          {required: true, message: '选择分类', trigger: 'change', validator:this.cascaderVerify}
        ],
      },
    }
  },
  computed: {
  },
  methods: {
    // 选中用户信息
    handleSelectionChange(val) {
      let arr = []
      for (let v of val)
        arr.push( v.id )
      this.formData.uids = arr;
    },

    // 级联菜单组件change事件
    async cascaderChange(v) {
      if (v.length) {
        let category_id = v[(v.length-1)]
        this.formData.category_id = category_id
        //console.log(category_id)
        // 如果是指定用户 显示用户列表供选择
        if (category_id === 3){
          this.userList = await this.$api.getUsers()
          this.showUserTable = true
        }
        else
          this.showUserTable = false
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

    async send() {


      // 调用组件的数据验证方法
      this.$refs['articleForm'].validate((valid) => {
        if (valid) {

          let userInfo = JSON.parse(localStorage.getItem('user_info'))
          this.formData.author = userInfo.username;

          console.log(this.formData)
          this.formSubmit()

        } else {
          this.$message.error('数据验证失败，请检查必填项数据！')
        }
      })
      return true

    },

    async formSubmit() {
      //this.loadingStaus = true

      await this.$api.onlinePush(this.formData)

      this.$notify.success('已成功推送！')

      //this.initArticleForm()

      this.$nextTick(() => {
        //this.loadingStaus = false
      })
      // this.$router.push(this.$route.matched[1].path+'/'+id+'/edit')
      // this.$router.push(this.$route.matched[1].path)
    },
    // 返回列表
    returnList() {
      //this.$router.push('/article')
    },
    // 处理编辑器内容输入
    handleTinymceInput(val) {
      this.formData.content = val
    },
  },
  async mounted() {
    this.loadingStaus = true
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
    .right-button
     padding: 0px 12px;
     width: 40%;
     text-align: center;
     display: inline-flex;
     justify-content: space-around;
    .el-cascader
      display inline-block
      width 50%
  .content-right
    .right-button
      display inline-block
      border-top 1px solid #e8e8e8e8
      padding 12px 12px
    .el-form-item__label
      float none
      display inline-block
      text-align left
      padding 0 0 10px 0
    .el-form-item__content
      margin-left 0px !important
      .el-checkbox-group
        margin-top -12px

</style>
