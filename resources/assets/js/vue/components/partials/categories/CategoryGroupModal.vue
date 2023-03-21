<template>
  <span>
    <v-snackbar
      :top="true"
      color="success"
      v-model="successResponse">
      Die Oberkategorie wurde erfolgreich gespeichert.
    </v-snackbar>
    <v-snackbar
      :top="true"
      color="error"
      v-model="errorResponse">
      {{ message }}
    </v-snackbar>
    <v-dialog
      max-width="500px"
      v-model="dialog"
    >
      <template slot="activator">
        <v-btn
          v-if="categoryGroup.id"
          icon
          ripple
        >
          <v-icon color="blue lighten-1">
            edit
          </v-icon>
        </v-btn>
        <v-btn
          color="success"
          ripple
          v-else
        >
          <v-icon
            dark
            left>
            add
          </v-icon>
          Neue Oberkategorie
        </v-btn>
      </template>
      <v-card>
        <v-toolbar
          card
          color="primary"
          dark>
          <v-btn
            @click.native="dialog = false"
            dark
            icon
          >
            <v-icon>close</v-icon>
          </v-btn>
          <v-toolbar-title v-if="categoryGroup.id">
            Oberkategorie bearbeiten
          </v-toolbar-title>
          <v-toolbar-title v-else>
            Oberkategorie erstellen
          </v-toolbar-title>
        </v-toolbar>

        <v-card-text>
          <reusable-clone-warning v-if="categoryGroup.is_reusable_clone" class="mb-4" />
          <v-form
            lazy-validation
            ref="form"
          >
            <v-text-field
              :rules="rules.name"
              label="Name"
              required
              v-model="categoryGroup.name"
            />
            <tag-select
              multiple
              label="Sichtbar für folgende User"
              placeholder="Alle"
              v-model="categoryGroup.tags"
            />
            <template v-if="categoryGroup.id">
              <span
                v-if="hasCategories"
                class="grey--text lighten-1"
              >
                Es können nur Oberkategorien ohne Unterkategorien gelöscht werden.
              </span>
              <v-btn
                v-else
                :disabled="isLoading || hasCategories"
                :loading="isLoading"
                @click.native="deleteGroup"
                color="red"
                outline
              >
                Oberkategorie löschen
              </v-btn>
            </template>
          </v-form>
        </v-card-text>
        <v-divider/>
        <v-card-actions>
          <v-btn
            :disabled="isLoading"
            :loading="isLoading"
            @click.native="save"
            block
            outline
          >
            Speichern
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </span>
</template>

<script>
  import ReusableCloneWarning from "../global/ReusableCloneWarning"
  import TagSelect from "../global/TagSelect"

  const rules = {
    name: [
      entry => !!entry && entry.length > 0 || "Die Oberkategorie benötigt einen Namen.",
    ],
  }
  export default {
    props: {
      categoryGroupData: {
        type: Object,
        default() {
          return {
            name: "",
            tags: [],
          }
        },
        required: false,
      },
      hasCategories: {
        default: false,
        required: false,
        type: Boolean,
      },
      tagGroups: {
        required: true,
        type: Array,
      },
    },
    data() {
      return {
        errorResponse: false,
        categoryGroup: {},
        dialog: false,
        isLoading: false,
        message: null,
        rules,
        successResponse: false,
      }
    },
    watch: {
      categoryGroupData: {
        handler() {
          this.updateData()
        },
        deep: true,
        immediate: true,
      },
      dialog: {
        handler() {
          if (this.dialog) {
            this.updateData()
          }
        },
      },
    },
    methods: {
      updateData() {
        this.categoryGroup = JSON.parse(JSON.stringify(this.categoryGroupData))
      },
      save() {
        if (this.isLoading) {
          return
        }
        this.isLoading = true
        let apiUrl = "/backend/api/v1/categories/groups/"
        if (this.categoryGroup.id) {
          apiUrl += this.categoryGroup.id
        }
        axios.post(apiUrl, this.categoryGroup).then(response => {
          if (response.data.success) {
            this.successResponse = true
            if (!this.categoryGroup.id) {
              this.$refs.form.reset()
            }
            this.$emit("update", response.data.categoryGroup)
            this.dialog = false
          } else {
            this.message = response.data.error
            this.errorResponse = true
          }
          this.isLoading = false
        }).catch(error => {
          this.message = "Es ist ein unerwarteter Fehler aufgetreten."
          this.errorResponse = true
          this.isLoading = false
        })
      },
      deleteGroup() {
        if (this.isLoading || this.hasCategories) {
          return
        }
        if(!confirm('Möchten Sie diese Oberkategorie löschen?')) {
          return
        }
        this.isLoading = true
        axios.post(`/backend/api/v1/categories/groups/${this.categoryGroup.id}/delete`).then(response => {
          if (response.data.success) {
            this.$emit("delete", this.categoryGroup.id)
            this.dialog = false
          } else {
            this.message = response.data.error
            this.errorResponse = true
          }
          this.isLoading = false
        }).catch(error => {
          this.message = "Es ist ein unerwarteter Fehler aufgetreten."
          this.errorResponse = true
          this.isLoading = false
        })
      },
    },
    components: {
      ReusableCloneWarning,
      TagSelect,
    },
  }
</script>
