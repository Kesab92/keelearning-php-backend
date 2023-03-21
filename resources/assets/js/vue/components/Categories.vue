<template>
  <div>
    <div class="c-moduleIntro">
      <h1 class="c-moduleIntro__heading">
        Lernkategorien
      </h1>
      <div class="c-moduleIntro__description">
        Jede Frage muss einer Kategorie zugewiesen sein.
      </div>
      <div class="c-moduleIntro__links">
        <v-btn
          flat
          color="primary"
          small
          href="https://helpdesk.keelearning.de/de/articles/4233298-kategorien"
          target="_blank"
        >
          <v-icon
            small
            class="mr-1">
            help
          </v-icon>
          Anleitung öffnen
        </v-btn>
      </div>
    </div>
    <v-card v-if="isLoading">
      <div class="pa-3 text-xs-center">
        <v-progress-circular
          indeterminate
          color="primary"
        />
      </div>
    </v-card>
    <template v-else>
      <div class="mb-3">
        <category-group-modal
          v-if="useCategoryGroups"
          :tag-groups="tagGroups"
          @update="updateCategoryGroup"
        />
        <category-modal
          :category-groups="categoryGroups"
          :tag-groups="tagGroups"
          :open="newCategoryModalOpen"
          @setOpen="setNewCategoryModalOpen"
          @update="updateCategory"
        />
      </div>
      <template v-if="useCategoryGroups">
        <v-card
          v-for="categoryGroup in sortedCategoryGroups"
          :key="`cg-${categoryGroup.id}`"
          class="mb-3"
        >
          <v-card-title
            primary-title
            class="pb-0"
          >
            <div class="headline">
              {{ categoryGroup.name }}

              <category-group-modal
                :category-group-data="categoryGroup"
                :has-categories="!!sortedCategoriesByGroupId(categoryGroup.id).length"
                :tag-groups="tagGroups"
                @delete="deleteCategoryGroup"
                @update="updateCategoryGroup"
              />
            </div>
            <div class="caption">
              {{ tagNames(categoryGroup.tags) }}
            </div>
          </v-card-title>
          <v-card-text class="pt-0">
            <categories-table
              :categories="sortedCategoriesByGroupId(categoryGroup.id)"
              :category-groups="categoryGroups"
              :tag-groups="tagGroups"
              @update="updateCategory"
            />
          </v-card-text>
        </v-card>
      </template>
      <template v-if="ungroupedCategories.length">
        <v-card
          class="mb-3"
        >
          <v-card-title
            primary-title
            class="pb-0"
          >
            <div
              :class="{
                'red--text': useCategoryGroups,
              }"
              class="headline"
            >
              {{ useCategoryGroups ? 'Ohne Gruppe, nicht sichtbar in der App' : 'Kategorien' }}
            </div>
          </v-card-title>
          <v-card-text class="pt-0">
            <categories-table
              :categories="ungroupedCategories"
              :category-groups="categoryGroups"
              :tag-groups="tagGroups"
              @update="updateCategory"
            />
          </v-card-text>
        </v-card>
      </template>
    </template>
  </div>
</template>

<script>
import CategoryModal from './partials/categories/CategoryModal'
import CategoryGroupModal from './partials/categories/CategoryGroupModal'
import CategoriesTable from './partials/categories/CategoriesTable'
import {mapGetters} from "vuex"

export default {
  data() {
    return {
      categoryGroups: [],
      categories: [],
      isLoading: true,
      tagGroups: [],
      useCategoryGroups: null,
      newCategoryModalOpen: false,
    }
  },
  created() {
    this.$store.dispatch('tags/updateTags')
    this.loadData()
  },
  computed: {
    ...mapGetters({
      tags: 'tags/tags',
    }),
    sortedCategoryGroups() {
      return [...this.categoryGroups].sort((groupA, groupB) => {
        const nameA = groupA.name.toLowerCase()
        const nameB = groupB.name.toLowerCase()
        if (nameA < nameB) {
          return -1
        }
        if (nameA > nameB) {
          return 1
        }
        return 0
      })
    },
    ungroupedCategories() {
      if (!this.useCategoryGroups) {
        return this.sortCategories(this.categories)
      }
      return this.sortedCategoriesByGroupId(null)
    },
  },
  methods: {
    sortedCategoriesByGroupId(groupId) {
      return this.sortCategories(this.categories.filter(category => category.categorygroup_id == groupId))
    },
    sortCategories(categories) {
      return [...categories].sort((categoryA, categoryB) => {
        if (categoryA.active != categoryB.active) {
          if (categoryA.active) {
            return -1
          }
          return 1
        }

        const nameA = categoryA.name.toLowerCase()
        const nameB = categoryB.name.toLowerCase()
        if (nameA < nameB) {
          return -1
        }
        if (nameA > nameB) {
          return 1
        }

        return 0
      })
    },
    tagNames(tags) {
      return this.tags.filter(tag => tags.indexOf(tag.id) > -1).map(tag => tag.label).join(', ')
    },
    loadData() {
      axios.get('/backend/api/v1/categories').then(response => {
        this.categories = response.data.categories
        this.categoryGroups = response.data.categoryGroups || []
        this.tagGroups = response.data.tagGroups
        this.useCategoryGroups = response.data.useCategoryGroups
        this.isLoading = false
      })
    },
    updateCategory(category) {
      let index = this.categories.findIndex(cg => cg.id == category.id)
      if (index === -1) {
        this.categories.push(category)
      } else {
        this.$set(this.categories, index, category)
      }
    },
    deleteCategoryGroup(categoryGroupId) {
      let index = this.categoryGroups.findIndex(cg => cg.id == categoryGroupId)
      this.categoryGroups.splice(index, 1)
    },
    updateCategoryGroup(categoryGroup) {
      let index = this.categoryGroups.findIndex(cg => cg.id == categoryGroup.id)
      if (index === -1) {
        this.categoryGroups.push(categoryGroup)
      } else {
        this.$set(this.categoryGroups, index, categoryGroup)
      }
    },
    setNewCategoryModalOpen(open) {
      this.newCategoryModalOpen = open
    },
  },
  components: {
    CategoryModal,
    CategoryGroupModal,
    CategoriesTable,
  },
}
</script>

<style lang="scss" scoped>
.caption {
  color: rgba(0, 0, 0, 0.54);
  margin-bottom: 10px !important;
  width: 100%;
}
</style>
