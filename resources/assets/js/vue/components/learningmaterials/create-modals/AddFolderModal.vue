<template>
  <div>
    <v-dialog
      v-model="active"
      persistent
      width="500"
    >
      <v-card>
        <form @submit.prevent="createFolder">
          <v-toolbar>
            <v-toolbar-title>
              Neuen Ordner erstellen
            </v-toolbar-title>
          </v-toolbar>
          <v-card-text>
            <v-text-field
              v-model="folderName"
              ref="folderName"
              outline
              required
              label="Name des Ordners"
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
              Ordner erstellen
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
      folderName: null,
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
        this.folderName = null
        this.$nextTick(() => this.$refs.folderName.$refs.input.select())
      }
    },
  },
  methods: {
    createFolder() {
      this.isCreating = true
      this.$store.dispatch('learningmaterials/createFolder', {
        name: this.folderName,
        parent_id: this.$store.getters['learningmaterials/folderId'],
      }).then((folder) => {
        this.$router.push({
          name: 'learningmaterials.index',
          params: {
            folderId: folder.id,
          },
        })
        this.active = false
      }).catch((e) => {
        console.log(e)
        alert('Der Ordner konnte leider nicht erstellt werden.')
      }).finally(() => {
        this.isCreating = false
      })
    }
  },
}
</script>
