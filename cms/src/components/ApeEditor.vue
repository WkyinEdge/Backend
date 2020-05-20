<template>
  <div>
    <Editor id="tinymce" v-model="tinymceHtml" :init="tinymceData"></Editor>
  </div>
</template>

<script>
import 'tinymce/tinymce'
import Editor from '@tinymce/tinymce-vue'

import 'tinymce/themes/silver/theme'
import 'tinymce/plugins/contextmenu'
import 'tinymce/plugins/wordcount'
import 'tinymce/plugins/image'
import 'tinymce/plugins/media'
import 'tinymce/plugins/lists'
import 'tinymce/plugins/link'
// import 'tinymce/plugins/autolink'
import 'tinymce/plugins/table'
import 'tinymce/plugins/hr'
import 'tinymce/plugins/preview'
import 'tinymce/plugins/code'
import 'tinymce/plugins/fullscreen'
// import 'tinymce/plugins/insertdatetime'
// import 'tinymce/plugins/template'
// import 'tinymce/plugins/paste'
import 'tinymce/plugins/textcolor'
import 'tinymce/plugins/anchor'
import 'tinymce/plugins/print'
//import 'tinymce/plugins/indent2em/plugin'
//import 'tinymce/plugins/lineheight/plugin'
import '@/../public/tinymce/plugins/indent2em/plugin'
import '@/../public/tinymce/plugins/lineheight/plugin'
import axios from 'axios'

export default {
  components: {
    Editor
  },
  props:{
    plugins:{
      type: String,
      //default:'link lists image table colorpicker textcolor wordcount ',
      default:'link lists image media table textcolor wordcount contextmenu hr preview fullscreen anchor code indent2em lineheight insertdatetime',
    },
    toolbar:{
      type: String,
      //default: 'undo redo | bold italic underline strikethrough | fontsizeselect | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote | table link unlink image | removeformat',
      //default: 'undo redo paste | bold italic underline strikethrough | fontselect fontsizeselect | indent2em lineheight | forecolor backcolor | ltr rtl | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent blockquote | link unlink image media table | removeformat anchor hr | code preview fullscreen insertdatetime',
      default: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect | indent2em lineheight | forecolor backcolor | ltr rtl | alignleft aligncenter alignright alignjustify | bullist numlist | outdent indent | link unlink image media table | removeformat hr insertdatetime | code preview fullscreen',

    },
    showMenu:{
      type: Boolean,
      default: false
    },
    editorHeight: {
      type: Number,
      default: 300,
    },
    initHtml:{
      type: String,
      default: ''
    },
    fontFormats: {
      type: String,
      default: '宋体=simsun,serif;仿宋体=FangSong,serif;楷体=KaiTi,serif;黑体=SimHei,sans-serif;微软雅黑=Microsoft YaHei,Helvetica Neue,PingFang SC,sans-serif;苹果苹方=PingFang SC,Microsoft YaHei,sans-serif;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Verdana=verdana,geneva;'
    }
  },
  data() {
    return {
      video_url:'',
      poster_url:'',
      tinymceHtml: '',
      tinymceData: {
        language_url: '/tinymce/zh_CN.js',
        language: 'zh_CN',
        skin_url: '/tinymce/skins/ui/oxide',
        file_picker_types: 'image media', // file
        media_live_embeds: true,
        //media_poster:false,
        //images_upload_url: '/api/upload/file',
        height: this.editorHeight,
        plugins: this.plugins,
        toolbar: this.toolbar,
        branding: false,
        menubar: this.showMenu,
        //images_upload_handler: this.imagesUploadHandler,
        //file_picker_callback: this.file_picker_callback,
        convert_urls:false,
        font_formats: this.fontFormats,
        toolbar_drawer: false,
        /*video_template_callback: function(data) {
          return '<video width="' + data.width + '" height="' + data.height
                  + '"' + (data.poster ? ' poster="' + data.poster + '"' : '')
                  + ' controls="controls">\n' + '<source src="' + data.source1 + '"'
                  + (data.source1mime ? ' type="' + data.source1mime + '"' : '') + ' />\n'
                  + (data.source2 ? '<source src="' + data.source2 + '"'
                          + (data.source2mime ? ' type="' + data.source2mime + '"' : '') + ' />\n' : '')
                  + '</video>';
        }*/
      }
    }
  },
  created(){
  },
  computed: {
  },
  watch: {
    // 初始化编辑器内容
    "initHtml": function(val) {
      this.tinymceHtml = val
    },
    // 处理编辑器内容变化
    "tinymceHtml" : function(val) {
      this.$emit('handleTinymceInput',val)
    }
  },
  methods: {
    // 自定义文件上传
    file_picker_callback(callback, value, meta){
      //文件分类
      var filetype = '.pdf, .txt, .zip, .rar, .7z, .doc, .docx, .xls, .xlsx, .ppt, .pptx, .mp3, .mp4';
      //后端接收上传文件的地址
      var upurl = '/api/upload/file';
      //为不同插件指定文件类型及后端地址
      switch (meta.filetype) {
        case 'image':
          filetype = '.jpg, .jpeg, .png, .gif';
          upurl = '/api/upload/file';
          break;
        case 'media':
          filetype = '.mp3, .mp4';
          upurl = '/api/upload/file';
          break;
        case 'file':
        default:
      }
      //模拟出一个input用于添加本地文件
      var input = document.createElement('input');
      input.setAttribute('type', 'file');
      input.setAttribute('accept', filetype);
      input.click();
      let that = this
      input.onchange = function () {
        var file = this.files[0];

        var formData;

        formData = new FormData();

        formData.append(meta.filetype, file, file.name);

        let wanUrl = 'http://210oe79321.51mypc.cn'
        axios.post(wanUrl + upurl, formData).then(response => response.data).then(json => {
          if (json.code === 200) {
            callback(json.result.url, { title: 'cs' });
            // 自定义 的，不需要可删除
            that.editVideo(meta.filetype,json.result.url)
          }
        })
    }
    },
    //自定义高级媒体上传选项，插件自带有bug
    editVideo(type,url){
      if (type == 'image'){
        this.poster_url = url
      }
      if (type == 'media'){
        this.video_url = url
      }
      if ( this.poster_url && this.video_url ) {
        let res = '<video poster="' + this.poster_url + '" controls="controls"><source src="' + this.video_url + '" type="' + 'video/mp4' + '" /></video>';
        console.log(res)
        this.tinymceHtml = res
        this.poster_url = ''
        this.video_url = ''
      }

    },
    // 自定义图片上传
    imagesUploadHandler(blobInfo, success, failure){

      var file = blobInfo.blob();//转化为易于理解的file对象
      let formData = new FormData()

      formData.append('image', file)

      let upurl = '/api/upload/file';
      axios.post(upurl, formData).then(response => response.data).then(json => {
        if (json.code === 200) {
          success(json.result.url)
        }else {
          failure('上传失败')
        }
      })
      }
  }
}
</script>
<style>
  .tox-form .tox-form__group:first-child{
    //display: none;
  }
</style>
