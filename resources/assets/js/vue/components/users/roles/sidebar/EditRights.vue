<template>
  <div v-if="userRoleData">
    <Toolbar
      :user-role-data="userRoleData"
      :is-saving="isSaving"
      @save="save" />
    <div class="pa-4">
      <v-alert
        v-if="userRoleData.is_main_admin"
        :value="true"
        color="info"
        icon="priority_high"
        outline
        class="mt-4">
        Die Hauptadmin-Rolle verfügt über alle Berechtigungen.<br>
        Bei Fragen wenden Sie sich bitte an <a href="mailto:support@keeunit.de">support@keeunit.de</a>
      </v-alert>
      <template v-else>
        <h4 class="sectionHeader">
          Welche Rechte soll die Rolle haben?
        </h4>
        <div
          v-for="rightsGroup in filteredRightGroups"
          :key="rightsGroup.title">
          <v-layout
            row
            class="mb-2 mt-4">
            <v-flex>
              <h5>
                {{ rightsGroup.title }}
              </h5>
            </v-flex>
            <template v-if="rightsGroup.hints">
              <v-spacer />
              <v-flex class="text-xs-right">
                <div
                  v-for="hint in rightsGroup.hints"
                  :key="hint">
                {{ hint }}
                </div>
              </v-flex>
            </template>
          </v-layout>
          <Toggle
            v-for="right in rightsGroup.rights"
            :key="right.type"
            :label="right.title"
            :disabled="isRightDisabled(right.type)"
            :hint="right.hint"
            :value="hasRight(right.type)"
            @input="setRight(right.type, $event)"
            class="mb-2" />
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import rightDefinitions from './rights.js'
import UserRoleSidebarMixin from './mixin.js'

const keyedRightDefinitions = {}
rightDefinitions.forEach(rightsGroup => {
  rightsGroup.rights.forEach(right => {
    keyedRightDefinitions[right.type] = right
  })
})

export default {
  mixins: [UserRoleSidebarMixin],
  computed: {
    filteredRightGroups() {
      return rightDefinitions.map(rightsGroup => {
        const rights = rightsGroup.rights.filter((right) => {
          if (right.requiresOneOf && !this.hasOneOfRights(right.requiresOneOf)) {
            return false
          }
          if (right.preventedBySetting && this.appSettings[right.preventedBySetting] == 1) {
            return false
          }
          return true
        })
        return {
          ...rightsGroup,
          rights,
        }
      }).filter(rightsGroup => {
        if (!rightsGroup.rights.length) {
          return false
        }
        if (rightsGroup.module && this.appSettings[rightsGroup.module] != "1") {
          return false
        }
        return true
      })
    }
  },
  methods: {
    hasRight(type) {
      const rightDefinition = keyedRightDefinitions[type]
      if (!rightDefinition) {
        return false
      }
      const requiresOneOf = rightDefinition.requiresOneOf
      if (requiresOneOf && !this.hasOneOfRights(requiresOneOf)) {
        return false
      }
      const preventedBySetting = rightDefinition.preventedBySetting
      if (preventedBySetting && this.appSettings[preventedBySetting] == 1) {
        return false
      }
      const impliedBy = rightDefinition.impliedBy
      if (impliedBy && this.hasRight(impliedBy)) {
        return true
      }
      return this.userRoleData.rights.includes(type)
    },
    hasOneOfRights(types) {
      return types.some((type) => this.hasRight(type))
    },
    isRightDisabled(type) {
      const impliedBy = keyedRightDefinitions[type].impliedBy
      if (impliedBy && this.hasRight(impliedBy)) {
        return true
      }
      return false
    },
    setRight(type, value) {
      if (value) {
        if (!this.userRoleData.rights.includes(type)) {
          this.userRoleData.rights.push(type)
        }
      } else {
        if (this.userRoleData.rights.includes(type)) {
          this.userRoleData.rights.splice(this.userRoleData.rights.indexOf(type), 1)
        }
      }
    },
    async save() {
      if (this.isSaving) {
        return
      }
      this.isSaving = true
      const rights = this.userRoleData.rights.filter(this.hasRight)
      await this.$store.dispatch('userRoles/saveUserRole', {
        id: this.userRoleData.id,
        rights,
      })
      this.isSaving = false
    },
  },
}
</script>
