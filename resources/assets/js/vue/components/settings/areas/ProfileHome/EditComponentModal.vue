<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="open">
    <v-card v-if="component">
      <v-toolbar
        card
        dark
        color="primary">
        <v-btn
          icon
          dark
          @click="$emit('close')">
          <v-icon>close</v-icon>
        </v-btn>
        <v-toolbar-title>
          "{{ blueprint.name }}" bearbeiten
        </v-toolbar-title>
        <v-spacer/>
        <v-toolbar-items>
          <v-btn
            dark
            flat
            @click="save">
            Speichern
          </v-btn>
        </v-toolbar-items>
      </v-toolbar>
      <v-card-text>
        <div
          v-for="(settingData, setting) in blueprint.settings"
          :key="setting">
          <v-select
            v-if="settingData.type == 'select'"
            v-model="settings[setting]"
            :items="settingData.options"
            :label="settingData.label" />
          <v-text-field
            v-if="settingData.type == 'number'"
            v-model.number="settings[setting]"
            type="number"
            :hint="settingData.hint"
            :persistent-hint="!!settingData.hint"
            :min="settingData.min"
            :step="settingData.step"
            :label="settingData.label"
            :placeholder="`${settingData.default}`"
          />
        </div>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: {
    blueprints: {
      required: true,
      type: Object,
    },
    components: {
      required: true,
      type: Array,
    },
    open: {
      required: true,
      type: Boolean,
    },
    position: {
      required: true,
    },
  },
  data() {
    return {
      settings: {},
    }
  },
  computed: {
    blueprint() {
      if (this.component === null) {
        return null
      }
      return this.blueprints[this.component.type]
    },
    component() {
      if (this.position === null) {
        return null
      }
      return this.components[this.position]
    },
  },
  watch: {
    position() {
      this.settings = {}
      if (this.position !== null) {
        this.settings = {...this.component.settings}
      }
    },
  },
  methods: {
    save() {
      for (const [key, value] of Object.entries(this.settings)) {
        const blueprintSetting = this.blueprint.settings[key]

        if (blueprintSetting.type === 'number' && blueprintSetting.min) {
          if (value.length && value < blueprintSetting.min) {
            alert(`Der Wert muss mindestens ${blueprintSetting.min} sein`)
            return
          }
        }
      }
      this.$emit('update', this.settings)
    },
  },
}
</script>
