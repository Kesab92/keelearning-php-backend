<template>
  <div>
    <template v-if="isDone(azureVideo)">
      <video
        ref="video"
        class="azuremediaplayer amp-default-skin amp-big-play-centered" />
      <template v-for="subtitle in subtitles">
        <v-alert
          :key="`${subtitle.id}-info`"
          :value="isProcessing(subtitle)"
          color="info"
          icon="info"
          outline
        >
          Untertitel: {{ statusLabel(subtitle) }} ({{ subtitle.progress }}%)
        </v-alert>
        <v-alert
          :key="`${subtitle.id}-warning`"
          :value="isErroneous(subtitle)"
          color="warning"
          icon="priority_high"
          outline
        >
          Fehler beim Generieren der Untertitel. Bitte wenden Sie sich an den Support.
        </v-alert>
      </template>
    </template>
    <template v-if="isProcessing(azureVideo)">
      <div class="ui active tiny inline loader"/>
      Video: {{ statusLabel(azureVideo) }} ({{ azureVideo.progress }}%)
    </template>
    <template v-if="isErroneous(azureVideo)">
      Video: Fehler beim Verarbeiten. Bitte wenden Sie sich an den Support.
    </template>
  </div>
</template>

<script>
  const REFRESH_DELAY = 3000
  const STATUS_LABELS = [
    'Warte auf Verarbeitung',
    'Verarbeitung wird gestartet',
    'Verarbeitung läuft. Das kann einen Moment dauern. Sie können die Seite auch verlassen und später wieder zurückkehren.',
    'Verarbeitung abgeschlossen',
    'Fehler beim Verarbeite',
    'Verarbeitung wurde abgebrochen',
    'Verarbeitung wird abgebrochen',
  ]

  export default {
    props: ['videoId','coverImage'],
    data() {
      return {
        azureVideo: null,
        isChecking: true,
        subtitles: null,
        videoInstance: null,
      }
    },
    created() {
      this.updateAzureVideo()
    },
    methods: {
      isDone(resource) {
        if(!resource) {
          return false
        }
        return resource.status === 3
      },
      isProcessing(resource) {
        if(!resource) {
          return false
        }
        return [0,1,2].indexOf(resource.status) !== -1
      },
      isErroneous(resource) {
        if(!resource) {
          return false
        }
        return !this.isDone && !this.isProcessing
      },
      statusLabel(resource) {
        if(!resource) {
          return ''
        }
        return STATUS_LABELS[resource.status]
      },
      refresh() {
        if (this.isChecking) {
          return
        }
        this.isChecking = true
        if (this.videoInstance) {
          // disable subtitles until we fetch new ones
          this.videoInstance.disableTextTracks()
        }
        this.updateAzureVideo()
      },
      updateAzureVideo() {
        axios.get('/backend/api/v1/azure-video/' + this.videoId).then(response => {
          this.azureVideo = response.data.azureVideo
          this.subtitles = response.data.subtitles
          // Check if the video is finished processed
          if([3,4,5,6].indexOf(this.azureVideo.status) === -1 || !this.azureVideo.streaming_url) {
            window.setTimeout(this.updateAzureVideo, REFRESH_DELAY)
            return
          }
          if(!this.isDone(this.azureVideo)) {
            return
          }

          // some subtitles still processing?
          if(this.subtitles.some(subtitle => this.isProcessing(subtitle))) {
            window.setTimeout(this.updateAzureVideo, REFRESH_DELAY)
          } else {
            // everything is ready
            this.isChecking = false
          }

          // create video as soon as it's ready to display,
          // even if subtitles are still loading
          if (!this.videoInstance) {
            this.$nextTick(() => {
              this.setupVideoPlayer()
            })
            return
          }

          // video was already displayed, but we still ended up here,
          // which means subtitles were not yet ready
          if (this.subtitles.some(subtitle => !!subtitle.streaming_url)) {
            this.$nextTick(() => {
              this.setupVideoPlayer()
            })
          }
        }).catch(() => {
          alert('Konnte den Status des Videos nicht abrufen')
        })
      },
      setupVideoPlayer() {
        // Listen, I don't like this setTimeout either, but the AMP acts up and it's too unreliable to spend tons of time with
        window.setTimeout(() => {
          const playerOptions = {
            autoplay: false,
            controls: true,
            heuristicProfile: 'HighQuality',
            width: '540',
            height: '337',
            poster: this.coverImage,
            logo: { enabled: false },
          }
          let playerIsPlaying = false
          let playerTime = null
          let playerVolume = null
          if (!this.videoInstance) {
            this.videoInstance = amp(this.$refs.video, playerOptions)
          } else {
            playerIsPlaying = !this.videoInstance.paused()
            playerTime = this.videoInstance.currentTime()
            playerVolume = this.videoInstance.volume()
          }
          const subtitleSources = this.subtitles
            .filter(subtitle => !!subtitle.streaming_url)
            .map((subtitle) => {
              return {
                src: subtitle.streaming_url,
                kind: 'subtitles',
                srclang: subtitle.language,
                label: this.$constants.SUBTITLES.LANGUAGES.find((lang) => lang.value == subtitle.language).text,
              }
            })
          this.videoInstance.src([{ src: this.azureVideo.streaming_url, type: "application/vnd.ms-sstr+xml" }], subtitleSources)
          this.videoInstance.ready(() => {
            if (playerIsPlaying) {
              this.videoInstance.play()
            }
            if (playerTime !== null) {
              this.videoInstance.addEventListener('loadeddata', () => {
                this.videoInstance.currentTime(playerTime)
              })
            }
            if (playerVolume !== null) {
              this.videoInstance.volume(playerVolume)
            }
            const textTracks = this.videoInstance.textTracks()
            if (textTracks.length) {
              this.videoInstance.setActiveTextTrack(textTracks[0])
            }
          })
        }, this.videoInstance ? 1 : 1000) // we don't need to wait if amp is already loaded
      }
    }
  }
</script>

<style lang="scss">
.azuremediaplayer {
  max-width: 100%;
}

.azuremediaplayer.amp-default-skin .vjs-big-play-button {
  width: 80px;
  height: 80px;

  &::before {
    font-size: 40px;
  }
}
.amp-default-skin .vjs-poster img {
  background-color:black;
  height: 100%;
  width: 100%;
  object-fit: contain;
}
</style>
