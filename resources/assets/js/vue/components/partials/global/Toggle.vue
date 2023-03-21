<template>
  <v-layout
    row
    class="c-toggle"
    :class="{'-disabled': disabled}">
    <v-flex shrink>
      <v-switch
        :disabled="disabled"
        hide-details
        height="30"
        v-model="isActive" />
    </v-flex>
    <v-flex
      v-if="superAdminOnly"
      shrink>
      <v-tooltip bottom>
        <v-icon slot="activator">admin_panel_settings</v-icon>
        Nur für Superadmins
      </v-tooltip>
    </v-flex>
    <v-flex
      @click="toggle"
      align-self-center>
      <div
        class="c-toggle__label"
      >
        {{ label }}
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
    value: [Boolean, Number],
    label: {
      type: String,
      required: true
    },
    hint: {
      type: String,
      required: false,
    },
    disabled: {
      type: Boolean,
      required: false,
      default: false,
    },
    superAdminOnly: {
      type: Boolean,
      required: false,
      default: false,
    },
  },
  computed: {
    isActive: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      },
    },
  },
  methods: {
    toggle() {
      if(!this.disabled){
        this.isActive = !this.isActive
      }
    },
  },
}
</script>
