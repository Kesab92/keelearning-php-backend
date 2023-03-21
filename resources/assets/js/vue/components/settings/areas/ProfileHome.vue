<template>
  <div>
    <v-layout
      row
      align-center
      justify-space-between>
      <div class="headline">Homepage-Konfiguration</div>
      <v-btn
        :disabled="isSaving || !hasVisibleComponents"
        :loading="isSaving"
        color="primary"
        @click="save">
        Speichern
        <template
          v-if="savingSuccess"
          v-slot:loader>
          <v-icon light>done</v-icon>
        </template>
      </v-btn>
    </v-layout>
    <div class="mb-2">
      Definieren Sie, in welcher Reihenfolge die Elemente auf der Startseite angezeigt werden sollen.
    </div>
    <Draggable
      v-model="components"
      @change="handleReorder">
      <div
        v-for="component in components"
        :key="component.position"
        class="s-component"
        :class="{
          '-hidden': !component.visible,
        }">
        <v-layout
          row
          align-center
          class="pa-2 s-wrapper">
          <div
            class="s-position"
            :class="{
              'primary': !moduleIsDisabled(component.type),
              'grey': moduleIsDisabled(component.type),
            }">
            <div
              v-if="blueprints[component.type].icon && !moduleIsDisabled(component.type)"
              v-html="blueprints[component.type].icon"
              class="s-icon" />
            <v-icon
              v-if="moduleIsDisabled(component.type)"
              dark
              class="s-icon">
              hide_source
            </v-icon>
            #{{ (component.position + 1) }}
          </div>
          <v-flex class="s-name pl-2 subheading">
            {{ blueprints[component.type].name }}
            <v-chip
              v-for="(value, setting) in blueprints[component.type].settings"
              :key="`${component.position}-${setting}`"
              disabled
              small>
              {{ blueprints[component.type].settings[setting].label }}:
              {{ getSettingPreview(component, setting) }}
            </v-chip>
          </v-flex>
          <v-spacer />
          <v-flex
            align-self-center
            shrink>
            <v-btn
              v-if="hasSettings(component.type)"
              @click="editComponent(component.position)"
              color="info"
              flat
              icon>
              <v-icon>edit</v-icon>
            </v-btn>
            <v-btn
              v-if="!blueprints[component.type].unique"
              @click="removeComponent(component.position)"
              color="error"
              flat
              icon>
              <v-icon>delete</v-icon>
            </v-btn>
            <v-btn
              v-else
              @click="component.visible = !component.visible"
              color="info"
              flat
              icon>
              <v-icon v-text="component.visible ? 'visibility' : 'visibility_off'" />
            </v-btn>
          </v-flex>
        </v-layout>
      </div>
    </Draggable>
    <div class="text-xs-center">
      <v-menu offset-y>
        <v-btn slot="activator">
          Bereich hinzufügen
        </v-btn>
        <v-list>
          <v-list-tile
            v-for="blueprint in availableBlueprints"
            :key="blueprint.type"
            :disabled="blueprint.disabled"
            @click="addComponent(blueprint.type)"
          >
            <v-list-tile-title>{{ blueprint.name }}</v-list-tile-title>
          </v-list-tile>
        </v-list>
      </v-menu>
    </div>
    <edit-component-modal
      :blueprints="blueprints"
      :components="components"
      :position="editPosition"
      :open="editModalOpen"
      @close="closeEditModal"
      @update="updateComponentSettings" />
  </div>
</template>

<script>
  import Draggable from 'vuedraggable'
  import EditComponentModal from './ProfileHome/EditComponentModal.vue'

  export default {
    props: [
      'availableModules',
      'profileId',
      'profileSettings',
    ],
    data() {
      return {
        blueprints: {},
        components: [],
        editModalOpen: false,
        editPosition: null,
        isSaving: false,
        savingSuccess: false,
      }
    },
    created() {
      this.loadComponents()
    },
    computed: {
      availableBlueprints() {
        let availableBlueprints = []
        for (const type in this.blueprints) {
          const blueprint = this.blueprints[type]
          if (blueprint.module && !this.availableModules.includes(blueprint.module)) {
            continue
          }
          availableBlueprints.push({
            type: type,
            name: blueprint.name,
            disabled: blueprint.unique && this.components.some((component) => component.type == type),
          })
        }
        return availableBlueprints
      },
      hasVisibleComponents() {
        return !!this.components.filter(component => !!component.visible).length
      },
    },
    methods: {
      addComponent(type) {
        const component = {type}
        const blueprint = this.blueprints[type]
        component.visible = true
        if (blueprint.settings) {
          component.settings = null
        }
        this.components.push(component)
        this.handleReorder()
      },
      closeEditModal() {
        this.editModalOpen = false
        this.editPosition = null
      },
      editComponent(position) {
        this.editPosition = position
        this.editModalOpen = true
      },
      getSettingPreview(component, setting) {
        const blueprintSetting = this.blueprints[component.type].settings[setting]
        let value = null
        let isDefault = false

        if(component.settings && component.settings[setting]) {
          value = component.settings[setting]
        }

        if(value === null) {
          if(!blueprintSetting.default) {
            return null
          }
          isDefault = true
          value = blueprintSetting.default
        }

        switch (blueprintSetting.type) {
          case 'select':
            value =  blueprintSetting.options.find((option) => option.value == value).text
        }
        if(isDefault) {
          value = `${value} (Default)`
        }
        return value
      },
      handleReorder() {
        this.components.forEach((component, idx) => {
          component.position = idx
        })
      },
      hasSettings(type) {
        return !!this.blueprints[type].settings
      },
      loadComponents() {
        axios.get(`/backend/api/v1/settings/profile/${this.profileId}/homeComponents`).then((response) => {
          this.blueprints = response.data.blueprints
          this.components = response.data.components
          this.handleReorder()
        })
      },
      moduleIsDisabled(type) {
        const blueprint = this.blueprints[type]
        if (!blueprint.module) {
          return false
        }
        return !this.profileSettings[blueprint.module].value
      },
      removeComponent(position) {
        this.components.splice(position, 1)
        this.handleReorder()
      },
      save() {
        if (this.isSaving) {
          return
        }
        this.savingSuccess = false
        this.isSaving = true
        axios.post(`/backend/api/v1/settings/profile/${this.profileId}/homeComponents`, {
          components: this.components,
        }).then(() => {
          this.loadComponents()
          this.savingSuccess = true
          setTimeout(() => (this.isSaving = false), 1000)
        }).catch((error) => {
          if(error.response.data.error !== undefined) {
            alert(error.response.data.error)
          } else {
            alert('Die Einstellung konnte leider nicht gespeichert werden. Bitte probieren Sie es später erneut.')
          }
          this.isSaving = false
        })
      },
      updateComponentSettings(settings) {
        this.components[this.editPosition].settings = settings
        this.closeEditModal()
      },
    },
    components: {
      Draggable,
      EditComponentModal,
    },
  }
</script>


<style lang="scss" scoped>
  #app {
    .s-component {
      background: #ffffff;
      cursor: move;

      &:nth-child(even) {
        background: #edeff1;
      }

      &:hover {
        background: #dfe1e2;
      }

      &.-hidden {
        opacity: 0.5;
      }
    }

    .s-position {
      border-radius: 3px;
      color: white;
      margin-top: -1px;
      padding: 2px 5px;
      text-align: right;
      width: 55px;
    }

    .s-icon {
      float: left;

      &.v-icon {
        font-size: 18px;
      }

      ::v-deep .c-icon {
        height: 18px;
        width: 18px;
      }
    }

    .sortable-ghost {
      background: #616161 !important;
      color: white;
      box-shadow: inset 0 5px 5px -5px rgba(0, 0, 0, 0.42);
    }
  }
</style>
