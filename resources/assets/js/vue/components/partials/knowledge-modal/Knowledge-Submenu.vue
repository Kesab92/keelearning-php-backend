<template>
  <div>
    <v-toolbar dark>
      <v-toolbar-title>Wissensdatenbank</v-toolbar-title>
      <v-spacer></v-spacer>
      <v-btn v-if="superadmin" icon @click.prevent="toggleEditMode">
        <v-icon>edit</v-icon>
      </v-btn>
    </v-toolbar>
    <v-list subheader>
      <draggable :list="categories" @end="updateMove" :move="checkPermissions">
      <v-list-group v-for="category in categories" :key="category.id">
        <v-list-tile slot="activator">
          <category-modal
            v-if="editMode"
            :category="category"
            @update="update" />
          <v-list-tile-content>
            <div style="height: auto;">{{ category.name }}</div>
          </v-list-tile-content>
        </v-list-tile>

        <v-list-tile v-for="page in category.pages" :key="page.id">
          <a :href="'/help/knowledge?page=' + page.id">
            <v-list-tile-content>
              <div>{{ page.title }}</div>
            </v-list-tile-content>
          </a>
        </v-list-tile>
      </v-list-group>
      </draggable>
    </v-list>
    <div v-if="superadmin">
      <v-toolbar dark>
        <v-toolbar-title>Administration</v-toolbar-title>
      </v-toolbar>
      <div class="progress-container" v-if="categories === null">
        <v-progress-circular indeterminate></v-progress-circular>
      </div>
      <v-list v-else>
        <category-modal
          @update="update"
        />

        <page-modal
          type="knowledge"
          :categories="categories"
          @update="update">
          <v-list-tile slot>
            <a href="#">
              <v-list-tile-content>
                <v-list-tile-title>Neue Seite</v-list-tile-title>
              </v-list-tile-content>
            </a>
          </v-list-tile>
        </page-modal>
      </v-list>
    </div>
  </div>
</template>

<script>
  import CategoryModal from './CategoryModal'
  import draggable from 'vuedraggable'

  export default {
    components: {
      draggable,
      CategoryModal
    },
    props: {
      superadmin: {
        type: Boolean,
        required: true
      },
      categories: {
        type: Array,
        required: true
      }
    },
    data() {
      return {
        editMode: false
      }
    },
    methods: {
      toggleEditMode() {
        this.editMode = !this.editMode
      },
      updateMove() {
        if (!this.superadmin || !this.editMode) {
          return
        }

        if (this.categories) {
          let updatableCategories = []
          this.categories.forEach((category, sortIndex) => {
            if (category.sortIndex !== sortIndex) {
              updatableCategories.push({
                id: category.id,
                sortIndex: sortIndex
              })
            }
          })

          axios.post('/backend/api/v1/helpdesk/knowledge/sort', {
            categories: updatableCategories
          }).then(response => {
            if (!response.data.success) {
              this.failedToMoveCategory()
            }
          }).catch(this.failedToMoveCategory)
        }
      },
      failedToMoveCategory() {
        this.$emit('error', 'Es ist ein Fehler beim Verschieben der Kategorien aufgetreten.')
      },
      checkPermissions() {
        return this.superadmin && this.editMode
      },
      update() {
        this.$emit('update')
      }
    }
  }
</script>

<style lang="scss" scoped>
  #app nav.theme--dark.v-toolbar {
    background: #3d3d53;
  }

  #app .v-list__group__header > div:not(.v-list__group__header__prepend-icon):not(.v-list__group__header__append-icon) {
    flex: 1 1 auto;
  }
</style>

<style lang="scss">
  #app div.v-list__tile {
    height: auto;
    margin: 15px 0;
  }

  #app div.v-list__tile__title {
    height: auto;
    white-space: initial;
  }
</style>
