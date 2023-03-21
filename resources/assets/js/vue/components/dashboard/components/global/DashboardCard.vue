<template>
  <v-card
    class="body-2 layout column s-dashboardCard"
    :class="{
      '-centered': !this.$slots.title,
    }"
    elevation="1">
    <v-card-title
      v-if="$slots.title"
      class="s-title pb-0 text-uppercase">
      <slot name="title" />
    </v-card-title>
    <v-card-text
      :class="{
        'pa-0': noPaddingContent,
      }">
      <slot />
    </v-card-text>
    <v-card-actions
      v-if="$slots.actions"
      class="px-3">
      <slot name="actions" />
    </v-card-actions>

    <v-icon
      v-if="$slots.help"
      color="grey lighten-1"
      class="s-tooltipIcon clickable"
      @click="helpModalOpen = true">
      help
    </v-icon>
    <v-tooltip v-else-if="tooltip" top>
      <v-icon
        slot="activator"
        color="grey lighten-1"
        class="s-tooltipIcon">
        info
      </v-icon>
      <span>{{ tooltip }}</span>
    </v-tooltip>
    <v-dialog
      v-if="$slots.help"
      v-model="helpModalOpen"
      max-width="640px"
      width="80%">
      <v-card>
        <v-toolbar v-if="$slots.title">
          <v-toolbar-title>
            <slot name="title" />
          </v-toolbar-title>
        </v-toolbar>
        <v-card-text class="body-1">
          <slot name="help" />
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            color="primary"
            flat
            @click="helpModalOpen = false">
            Schließen
          </v-btn>
          <v-spacer/>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>

<script>
export default {
  props: {
    noPaddingContent: {
      type: Boolean,
      default: false,
    },
    tooltip: {
      default: null,
      type: String,
    },
  },
  data() {
    return {
      helpModalOpen: false,
    }
  },
}
</script>

<style lang="scss" scoped>
.s-dashboardCard {
  height: 100%;

  &.-centered {
    justify-content: center;

    .v-card__text {
      flex: initial;
    }
  }

  .v-card__text {
    flex: 1;
  }
}

.s-title {
  padding-right: 40px; // 16px default + 24px for info link icon
  position: relative;
}

.s-tooltipIcon {
  cursor: help;
  position: absolute;
  right: 12px;
  top: 12px;
  z-index: 1;
}
</style>
