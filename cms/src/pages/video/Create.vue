<template>
  <div>
      <div class="container">
            <Upload
                    type="drag"
                    action="/api/upload/file"
                    name="video"
                    accept="video/mp4"
                    :show-upload-list="false"
                    :on-success="videoUploadSuccess"
                    :before-upload="videoBeforeUp"
            >
                <div style="padding: 20px 0">
                    <Icon type="ios-cloud-upload" size="52" style="color: #3399ff"></Icon>
                    <p>点击或拖动视频文件上传</p>
                </div>
            </Upload>
            <div style="margin-bottom: 20px">{{videoName}}</div>
            <Form :model="formValidate" :label-width="80" :rules="ruleValidate" ref="formValidate">
                <FormItem label="标题" prop="title">
                    <Input v-model="formValidate.title" placeholder="请输入视频标题" />
                </FormItem>
                <FormItem label="视频简介" prop="content">
                    <Input v-model="formValidate.content" type="textarea" :autosize="{minRows: 2,maxRows: 5}" placeholder="视频简介" />
                </FormItem>
                <FormItem label="视频封面" prop="image">
                    <ImgUpload :uploadCallback="imgUploadSuccess"/>
                </FormItem>
                <!--分类-->
                <FormItem label="视频分类" prop="cat_id">
                    <Select v-model="formValidate.category_id" style="width:200px">
                        <Option v-for="(item,index) in channels" :value="item.id" :key="index">
                            {{item.name}}
                        </Option>
                    </Select>
                </FormItem>
                <FormItem>
                    <Button type="primary" @click="handleSubmit('formValidate')">Submit</Button>
                    <Button @click="handleReset('formValidate')" style="margin-left: 8px">Reset</Button>
                </FormItem>
            </Form>
        </div>
  </div>
</template>
<script>
import 'iview/dist/styles/iview.css';
import {Upload, Icon, Form, FormItem, Button, Input, Select, Option} from 'iview'
import ImgUpload from '../../components/ImgUpload'

export default{
  components: {
    ImgUpload,
    Upload,
    Icon,
    Form,
    FormItem,
    Button,
    Input,
    Select,
    Option
  },
  data () {
    return {
      channels: {},
      videoName: '',
      formValidate: {
        name: ''
      },
      ruleValidate: {
         title: [
          {required: true, message: 'The name cannot be empty', trigger: 'blur'}
        ],
        category_id: [
          {required: true, message: 'type cannot be empty', trigger: 'change'}
        ],
        image: [
          {required: true, message: 'image cannort be empty', trigger: 'blur'}
        ],
        url: [
          {required: true, message: 'video cannort be empty', trigger: 'blur'}
        ]
      }
    }
  },
  created () {
    this.getChannels()
  },
  computed: {
    channelData () {
      let tmpArr = Object.keys(this.channels).map(key => ({key, name: this.channels[key]}))
      // 将推荐项清除
      tmpArr.shift()
      return tmpArr
    }
  },
  methods: {
    async getChannels () {
      /*this.axios.get('/api/category').then(response => response.data).then(json => {
        if (json.message === 'OK') {
          this.channels = json.result
        }
      })*/
    let list = await this.$api.getVideoCategoryList()
    this.channels = list
    },
    async handleSubmit (name) {
        this.$refs[name].validate((valid) => {
        if (!valid) {
            this.$message.error('请校验表单是否合理')
            return
        }})
        let userInfo = JSON.parse(localStorage.getItem('user_info'))
        /** 通过后端上传的视频统一用0标识，，，，，后期记得改回来 */
        this.formValidate.uploader_id = userInfo.id;
        this.formValidate.uploader_name = userInfo.username;
        console.log(this.formValidate)

        let res = await this.$api.saveVideo(this.formValidate)
        if (res){
            this.$message.success('视频提交成功')
            this.$router.push('/video')
        } else {
            this.$message.error('视频提交失败')
        }


            /*this.$axios({
            method: 'post',
            url: '/api/video/add',
            params: {
              image: this.formValidate.image,
              url: this.formValidate.url,
              name: this.formValidate.name,
              content: this.formValidate.desc,
              cat_id: this.formValidate.cat_id
            }
          }).then(res => {
            if (res.data.code === 200) {
              this.$message.success('视频提交成功')
              setTimeout(() => {
                this.$router.push({path: 'my'})
              }, 500)
            } else {
              this.$message.error(res.message)
            }
          })*/


    },
    handleReset (name) {
      this.$refs[name].resetFields()
    },
    videoUploadSuccess (res, file) {
      let result = res.result
      if (res.code === 200) {
        this.formValidate.url = result.url
        this.videoName = file.name
      } else {
        this.$message.error('视频上传失败： ' + res.message)
      }
    },
    videoBeforeUp () {
      this.videoName = '视频上传中，请等待...'
    },
    videoError () {
      this.videoName = ''
      this.$message.error('视频上传失败')
    },
    imgUploadSuccess (url) {
      this.formValidate.image = url
    }
  }
}
</script>
<style>
.container {
  width: 660px;
  margin: 0 auto;
}
</style>
