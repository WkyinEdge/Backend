<template>
  <div>
    <div class="demo-upload-list" v-for="item in uploadList" :key="item.name">
      <template v-if="item.status === 'finished'">
        <img :src="item.url">
        <div class="demo-upload-list-cover">
          <Icon type="ios-eye-outline" @click.native="handleView(item.name)"></Icon>
          <Icon type="ios-trash-outline" @click.native="handleRemove(item)"></Icon>
        </div>
      </template>
      <template v-else>
        <Progress v-if="item.showProgress" :percent="item.percentage" hide-info></Progress>
      </template>
    </div>
    <Upload
        v-show="uploadList.length === 0"
        ref="upload"
        :show-upload-list="false"
        :default-file-list="defaultList"
        :on-success="handleSuccess"
        :format="['jpg','jpeg','png']"
        :max-size="2048"
        :on-format-error="handleFormatError"
        :on-exceeded-size="handleMaxSize"
        :before-upload="handleBeforeUpload"
        type="drag"
        action="/api/upload/file"
        name="image"
        accept="image/jpeg, image/png, image/jpg"
        style="display: inline-block;width:58px;">
        <div style="width: 58px;height:58px;line-height: 58px;">
            <Icon type="ios-camera" size="20"></Icon>
        </div>
    </Upload>
    <Modal title="View Image" v-model="visible">
      <img :src="imgName" v-if="visible" style="width: 100%">
    </Modal>
  </div>
</template>
<script>
import {Modal, Icon, Upload, Progress} from 'iview'

export default {
  props: {
    uploadCallback: {
      type: Function
    }
  },
  components: {
    Modal, Icon, Upload, Progress
  },
  data () {
    return {
      defaultList: [],
      imgName: '',
      visible: false,
      uploadList: []
    }
  },
  methods: {
    handleView (name, ...rest) {
      console.log(rest)
      this.imgName = name
      this.visible = true
    },
    handleRemove (file) {
      const fileList = this.$refs.upload.fileList
      this.$refs.upload.fileList.splice(fileList.indexOf(file), 1)
    },
    handleSuccess (res, file) {
      console.log(res.result)
      let result = res.result
      file.url = result.url
      file.name = result.url
      this.uploadCallback(result.url)
    },
    handleFormatError (file) {
      this.$Notice.warning({
        title: 'The file format is incorrect',
        desc: 'File format of ' + file.name + ' is incorrect, please select jpg or png.'
      })
    },
    handleMaxSize (file) {
      this.$Notice.warning({
        title: 'Exceeding file size limit',
        desc: 'File  ' + file.name + ' is too large, no more than 2M.'
      })
    },
    handleBeforeUpload () {
      const check = this.uploadList.length < 5
      if (!check) {
        this.$Notice.warning({
          title: 'Up to five pictures can be uploaded.'
        })
      }
      return check
    }
  },
  mounted () {
    this.uploadList = this.$refs.upload.fileList
  }
}
</script>
<style>
  .demo-upload-list{
    display: inline-block;
    width: 60px;
    height: 60px;
    text-align: center;
    line-height: 60px;
    border: 1px solid transparent;
    border-radius: 4px;
    overflow: hidden;
    background: #fff;
    position: relative;
    box-shadow: 0 1px 1px rgba(0,0,0,.2);
    margin-right: 4px;
  }
  .demo-upload-list img{
    width: 100%;
    height: 100%;
  }
  .demo-upload-list-cover{
    display: none;
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,.6);
  }
  .demo-upload-list:hover .demo-upload-list-cover{
    display: block;
  }
  .demo-upload-list-cover i{
    color: #fff;
    font-size: 20px;
    cursor: pointer;
    margin: 0 2px;
  }
</style>
