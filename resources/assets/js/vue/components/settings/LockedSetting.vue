<template>
  <v-layout
    row
    class="c-toggle -disabled">
    <v-flex style="flex:0 0 56px">
      <v-tooltip top>
        <v-icon slot="activator">
          lock
        </v-icon>
        <span>
          Diese Notification ist systemrelevant und kann nicht deaktiviert werden
        </span>
      </v-tooltip>
    </v-flex>
    <v-flex
      align-self-center>
      <div
        class="c-toggle__label"
      >
        {{ label }}
        <slot name="append_label"></slot>
      </div>
      <div
        v-if="hint"
        class="c-toggle__hint">
        {{ hint }}
      </div>
    </v-flex>
    <v-flex
      v-if="$slots['append']"
      shrink>
      <slot name="append" />
    </v-flex>
  </v-layout>
</template>

<script>
export default {
  props: {
    label: {
      type: String,
      required: true
    },
    hint: {
      type: String,
      required: false,
    },
  },
}
</script>
