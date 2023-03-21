<template>
  <v-layout
    v-if="selectedType === null"
    row>
    <v-flex
      v-if="enableFile"
      shrink>
      <v-btn
        @click="setType('file')"
        color="white"
        class="s-filetypeButton"
        large>
        <v-icon>insert_drive_file</v-icon>
        Datei
      </v-btn>
    </v-flex>
    <v-flex
      v-if="enableWbt && appSettings.wbt_enabled === '1'"
      shrink>
      <v-btn
        @click="setType('wbt')"
        color="white"
        class="s-filetypeButton"
        large>
        <v-icon>folder_special</v-icon>
        WBT (xAPI)
      </v-btn>
    </v-flex>
    <v-flex
      v-if="enableLink"
      shrink>
      <v-btn
        @click="setType('link')"
        color="white"
        class="s-filetypeButton"
        large>
        <v-icon>link</v-icon>
        Link
      </v-btn>
    </v-flex>
    <v-flex
      v-if="enableYoutube"
      shrink>
      <v-btn
        @click="setType('youtube')"
        color="white"
        class="s-filetypeButton"
        large>
        <v-icon>ondemand_video</v-icon>
        YouTube
      </v-btn>
    </v-flex>
  </v-layout>
</template>

<script>
import {mapGetters} from "vuex"

export default {
  props: {
    value: {
      type: String,
    },
    enableLink: {
      type: Boolean,
      default: true,
    },
    enableWbt: {
      type: Boolean,
      default: true,
    },
    enableYoutube: {
      type: Boolean,
      default: true,
    },
    enableFile: {
      type: Boolean,
      default: true,
    },
  },
  computed: {
    ...mapGetters({
      appSettings: 'app/appSettings',
    }),
    selectedType: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      },
    },
  },
  methods: {
    setType(type) {
      this.selectedType = type
      this.$emit('click', type)
    },
  },
}
</script>

<style lang="scss" scoped>
#app .s-filetypeButton {
  height: 70px;
  width: 150px;

  ::v-deep .v-btn__content {
    flex-direction: column;
  }
}
</style>
