<template>
  <div class="video-js">
    <video-player class="video-player vjs-custom-skin"
                  ref="videoPlayer"
                  :playsinline="true"
                  :options="playerOptions">
    </video-player>
  </div>
</template>

<script>
  //import videojs from 'video.js'
  import 'video.js/dist/video-js.css'
  import 'vue-video-player/src/custom-theme.css'
  import { videoPlayer } from 'vue-video-player'
  import 'videojs-contrib-hls.js/src/videojs.hlsjs'

  //videojs.options.flash.swf = SWF_URL // 设置flash路径，Video.js会在不支持html5的浏览中使用flash播放视频文件
  export default {
    components: {
      videoPlayer
    },
    props: {
      videoSrc: {
        type: String,
        required: true
      },
      /*sources02: {
        type: String,
        required: true
      },*/
      poster: {
        type: String,
        required: true
      }
    },
    data () {
      return {
        playerOptions: {
          playbackRates: [0.7, 1.0, 1.5, 2.0], //播放速度
          live: true,
          autoplay: false, // 如果true，浏览器准备好时开始播放
          muted: false, // 默认情况下将会消除任何音频
          loop: false, // 是否视频一结束就重新开始
          preload: 'auto', // 建议浏览器在<video>加载元素后是否应该开始下载视频数据。auto浏览器选择最佳行为,立即开始加载视频（如果浏览器支持）
          aspectRatio: '16:9', // 将播放器置于流畅模式，并在计算播放器的动态大小时使用该值。值应该代表一个比例 - 用冒号分隔的两个数字（例如"16:9"或"4:3"）
          fluid: true, // 当true时，Video.js player将拥有流体大小。换句话说，它将按比例缩放以适应其容器。
          language: 'zh-CN',
          controlBar: {
            timeDivider: false,
            durationDisplay: false,
            remainingTimeDisplay: false,
            currentTimeDisplay: true, // 当前时间
            volumeControl: false, // 声音控制键
            playToggle: true, // 暂停和播放键
            progressControl: true, // 进度条
            fullscreenToggle: true // 全屏按钮

          },
          //techOrder: ['html5'], // 兼容顺序
          sources: [
            {
              withCredentials: false,
              //type: 'application/x-mpegURL',
              type: 'video/mp4',// 这里的种类支持很多种：基本视频格式、直播、流媒体等，具体可以参看git网址项目
              src: this.videoSrc
            }
          ],
          poster: this.poster,
          notSupportedMessage: '此视频暂无法播放，请稍后再试' // 允许覆盖Video.js无法播放媒体源时显示的默认信息。
        }
      }
    }
  }
</script>

<style scoped lang="scss">
  .video-js{
    width:100%;
    height:auto;
    .no-video{
      display:flex;
      color: #fff;
      height:150px;
      width: 100%;
      font-size:14px;
      text-align:center;
      justify-content: center;
      align-items:center;
    }
  }
</style>