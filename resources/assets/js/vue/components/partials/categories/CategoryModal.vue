<template>
  <div>
    <v-snackbar
      :top="true"
      color="success"
      v-model="successResponse">
      Die Kategorie wurde erfolgreich gespeichert.
    </v-snackbar>
    <v-snackbar
      :top="true"
      color="error"
      v-model="errorResponse">
      {{ message }}
    </v-snackbar>
    <v-dialog
      max-width="50%"
      width="50%"
      scrollable
      v-model="isOpen"
    >
      <template slot="activator">
        <v-btn
          v-if="!categoryData.id"
          color="success"
          ripple
        >
          <v-icon
            dark
            left>
            add
          </v-icon>
          <template v-if="categoryGroups.length">
            Neue Unterkategorie
          </template>
          <template v-else>
            Neue Kategorie
          </template>
        </v-btn>
      </template>
      <v-card v-if="isOpen">
        <v-toolbar
          card
          color="primary"
          dark>
          <v-btn
            @click.native="isOpen = false"
            dark
            icon
          >
            <v-icon>close</v-icon>
          </v-btn>
          <v-toolbar-title v-if="categoryData.id">
            Kategorie bearbeiten
          </v-toolbar-title>
          <v-toolbar-title v-else>
            Kategorie erstellen
          </v-toolbar-title>
          <v-spacer/>
        </v-toolbar>
        <v-tabs
          v-if="category.id"
          color="primary"
          dark
          grow
          v-model="activeTab"
        >
          <v-tab>
            Einstellungen
          </v-tab>
          <v-tab>
            Layout
          </v-tab>
        </v-tabs>
        <v-card-text>
          <reusable-clone-warning v-if="category.is_reusable_clone" class="mb-4" />
          <v-tabs-items v-model="activeTab">
            <v-tab-item>
              <v-form
                lazy-validation
                ref="form"
              >
                <v-text-field
                  :rules="rules.name"
                  label="Name"
                  placeholder="Name"
                  required
                  v-model="category.name"
                />
                <v-select
                  :items="categoryGroups"
                  item-text="name"
                  item-value="id"
                  label="Oberkategorie"
                  v-if="categoryGroups && categoryGroups.length"
                  v-model="category.categorygroup_id"
                />
                <v-select
                  :items="tags"
                  :value="inheritedTags"
                  chips
                  item-text="label"
                  item-value="id"
                  label="TAG-Einschränkungen aus der Oberkategorie"
                  multiple
                  readonly
                  v-if="inheritedTags && inheritedTags.length"
                />

                <tag-select
                  v-model="category.tags"
                  label="Sichtbar für folgende User"
                  placeholder="Alle"
                  multiple />

                <v-text-field
                  placeholder="1"
                  label="Standardpunktwert für Fragen"
                  type="number"
                  v-model.number="category.points"
                >
                  <div
                    slot="append-outer"
                    style="padding-top: 8px;"
                    class="text-no-wrap">
                    Bezieht sich auf <a href="/tests">Tests</a>
                  </div>
                </v-text-field>
                <p>
                  Sichtbarkeit
                </p>
                <v-switch
                  label="Quiz-Battle"
                  v-model="category.visibility.quiz"
                />
                <v-switch
                  label="Powerlearning"
                  v-model="category.visibility.training"
                />
                <p>
                  Aktivierung
                </p>
                <v-switch
                  label="Aktiv"
                  v-model="category.active"
                />
                <template v-if="category.id">
                  <v-spacer/>
                  <v-btn
                    :disabled="isLoading"
                    :loading="isLoading"
                    @click="deleteCategory"
                    color="red"
                    outline
                  >
                    Kategorie löschen
                  </v-btn>
                </template>
              </v-form>
            </v-tab-item>
            <v-tab-item>
              <file-upload
                :default-preview="category.icon_url"
                :max-size="4"
                @load="fileUploadLoad('icon', $event)"
                @size-exceeded="fileUploadSizeExceeded"
                accept="image/png,image/jpeg,image/svg+xml"
                class="file-upload"
                image-class="file-upload-image"
                input-class="file-upload-input"
                placeholder="Icon hochladen (50x50 Pixel)"
                v-if="isOpen"
              />
              <file-upload
                :default-preview="category.cover_image_url"
                :max-size="4"
                @load="fileUploadLoad('cover_image', $event)"
                @size-exceeded="fileUploadSizeExceeded"
                accept="image/png,image/jpeg,image/svg+xml"
                class="file-upload"
                image-class="file-upload-image"
                input-class="file-upload-input"
                placeholder="Coverbild hochladen (400x240 Pixel)"
                v-if="isOpen"
              />
            </v-tab-item>
          </v-tabs-items>
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
    <DeleteDialog
      v-if="deleteDialogOpen"
      v-model="deleteDialogOpen"
      :dependency-url="`/backend/api/v1/categories/${category.id}/delete-information`"
      :deletion-url="`/backend/api/v1/categories/${category.id}`"
      :redirect-url="`/categories`"
      type-label="Kategorie"
      :entry-name="category.name">
      <v-alert
        slot="info"
        :value="true"
        type="info"
        class="mb-4"
      >
        Beim Löschen dieser Kategorie werden deren Fragen unsichtbar geschalten.
      </v-alert>
    </DeleteDialog>
  </div>
</template>

<script>
  import FileUpload from 'vue-base64-file-upload'
  import TagSelect from "../global/TagSelect"
  import { mapGetters } from 'vuex'
  import DeleteDialog from "../global/DeleteDialog"
  import ReusableCloneWarning from '../global/ReusableCloneWarning'

  const rules = {
    name: [
      entry => !!entry && entry.length > 0 || 'Die Oberkategorie benötigt einen Namen.',
    ],
  }
  export default {
    props: {
      categoryData: {
        type: Object,
        default() {
          return {
            active: true,
            name: '',
            tags: [],
            visibility: {
              training: true,
              quiz: true,
            },
          }
        },
        required: false,
      },
      categoryGroups: {
        type: Array,
      },
      tagGroups: {
        required: true,
        type: Array,
      },
      open: {
        required: true,
        type: Boolean,
      },
    },
    data() {
      return {
        activeTab: 0,
        errorResponse: false,
        category: {},
        isLoading: false,
        message: null,
        rules,
        successResponse: false,
        deleteDialogOpen: false,
      }
    },
    watch: {
      categoryData: {
        handler() {
          this.updateData(this.categoryData)
        },
        deep: true,
        immediate: false,
      },
      open: {
        handler() {
          if (this.open) {
            this.updateData(this.categoryData)
          }
        },
      },
    },
    computed: {
      ...mapGetters({
        tags: 'tags/tags',
      }),
      isOpen: {
        get() {
          return this.open
        },
        set(open) {
          this.$emit('setOpen', open)
        },
      },
      inheritedTags() {
        if (!this.categoryGroups || !this.categoryGroups.length || !this.category.categorygroup_id) {
          return []
        }
        let categoryGroup = this.categoryGroups.find(cg => cg.id === this.category.categorygroup_id)
        if (!categoryGroup) {
          return []
        }
        return categoryGroup.tags
      },
    },
    methods: {
      fileUploadLoad(type, file) {
        this.category[type] = file
      },
      fileUploadSizeExceeded() {
        this.message = 'Die Bilddatei darf maximal 4MB groß sein!'
        this.errorResponse = true
      },
      updateData(categoryData) {
        this.category = JSON.parse(JSON.stringify(categoryData))
        if (!this.category.visibility) {
          this.category.visibility = {}
        }
        if (this.category.hiders) {
          this.category.visibility.quiz = !this.category.hiders.includes(1)
          this.category.visibility.training = !this.category.hiders.includes(2)
        }
      },
      deleteCategory() {
        this.deleteDialogOpen = true
      },
      save() {
        if (this.isLoading) {
          return
        }
        this.isLoading = true
        let apiUrl = '/backend/api/v1/categories/'
        if (this.category.id) {
          apiUrl += this.category.id
        }
        this.category.scopes = []
        if (this.category.visibility.quiz) {
          this.category.scopes.push(1)
        }
        if (this.category.visibility.training) {
          this.category.scopes.push(2)
        }
        axios.post(apiUrl, this.category).then(response => {
          if (response.data.success) {
            this.successResponse = true
            this.$emit('update', response.data.category)
            if (!this.category.id) {
              this.updateData(response.data.category)
              this.activeTab = 1
            } else {
              this.activeTab = 0
              this.isOpen = false
            }
          } else {
            this.message = response.data.error
            this.errorResponse = true
          }
          this.isLoading = false
        }).catch(error => {
          this.message = 'Es ist ein unerwarteter Fehler aufgetreten.'
          this.errorResponse = true
          this.isLoading = false
        })
      },
    },
    components: {
      DeleteDialog,
      TagSelect,
      FileUpload,
      ReusableCloneWarning,
    },
  }
</script>

<style lang="scss">
#app {
  $button-color: #2b76d2;

  .file-upload {
    margin-bottom: 1rem;

    & + .file-upload {
      margin-top: 3rem;
    }
  }

  .file-upload-image {
    display: block;
    margin: 20px auto;
    max-height: 250px;
    max-width: 100%;
    object-fit: cover;
  }

  .file-upload-input {
    border: 1px solid $button-color;
    color: $button-color;
    display: block;
    padding: 8px 10px;
    text-align: center;
    transition: background-color 0.3s ease;

    &::placeholder {
      color: $button-color;
    }
  }

  // Sichtbares Pseudo-Input wird durch das eigentliche Input überdeckt
  input:hover + .file-upload-input {
    background-color: rgba($button-color, 0.25);
  }
}
</style>
