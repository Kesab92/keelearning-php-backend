<template>
  <div>
    <v-dialog
      v-model="active"
      persistent
      width="500"
    >
      <v-card>
        <form @submit.prevent="createMaterial">
          <v-toolbar>
            <v-toolbar-title>
              Neue Datei erstellen
            </v-toolbar-title>
          </v-toolbar>
          <v-card-text>
            <v-text-field
              v-model="materialName"
              ref="materialName"
              outline
              required
              label="Name der Datei"
            />
          </v-card-text>
          <v-divider/>
          <v-card-actions>
            <v-btn
              color="primary"
              type="submit"
              :loading="isCreating"
              :disabled="isCreating"
            >
              Datei erstellen
            </v-btn>
            <v-spacer/>
            <v-btn
              flat
              @click="active = false"
            >
              Abbrechen
            </v-btn>
          </v-card-actions>
        </form>
      </v-card>
    </v-dialog>
  </div>
</template>

<script>
export default {
  props: ['value'],
  data() {
    return {
      materialName: null,
      isCreating: false,
    }
  },
  computed: {
    active: {
      get() {
        return this.value
      },
      set(value) {
        this.$emit('input', value)
      },
    },
  },
  watch: {
    active() {
      if (this.active) {
        this.materialName = null
        this.$nextTick(() => this.$refs.materialName.$refs.input.select())
      }
    },
  },
  methods: {
    createMaterial() {
      this.isCreating = true
      this.$store.dispatch('learningmaterials/createMaterial', {
        title: this.materialName,
        folder_id: this.$store.getters['learningmaterials/folderId'],
      }).then((material) => {
        this.$router.push({
          name: 'learningmaterials.edit.general',
          params: {
            learningmaterialId: material.id,
            folderId: material.learning_material_folder_id,
          },
        })
        this.active = false
      }).catch((e) => {
        console.log(e)
        alert('Die Datei konnte leider nicht erstellt werden.')
      }).finally(() => {
        this.isCreating = false
      })
    }
  },
}
</script>
