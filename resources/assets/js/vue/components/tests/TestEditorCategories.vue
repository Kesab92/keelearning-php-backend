<template>
  <v-card class="mt-4">
    <v-dialog
      v-model="deletionDialog"
      max-width="290"
    >
      <v-card>
        <v-card-text>
          Soll diese Kategorie entfernt werden?
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>

          <v-btn
            color="red"
            flat="flat"
            @click="deleteTestCategory"
          >
            Entfernen
          </v-btn>

          <v-btn
            color="gray"
            flat="flat"
            @click="deletionDialog = false"
          >
            Abbrechen
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-toolbar>
      <v-toolbar-title>
        Kategorien
      </v-toolbar-title>
      <v-spacer />
      <categories-modal
        v-if="!isReadonly"
        @add="addCategories"
        @error="$emit('message', {type: 'error', message: $event})"
      >
        <v-btn
          color="primary"
          flat
        >
          Kategorien hinzufügen
          <v-icon right>
            add
          </v-icon>
        </v-btn>
      </categories-modal>
    </v-toolbar>
    <v-card-text>
      <v-list two-line>
        <v-list-tile
          v-if="!testCategories.length"
          class="grey--text"
        >
          Noch keine Kategorie  ausgewählt.
        </v-list-tile>
        <v-list-tile
          v-for="(testCategory, index) in testCategories"
          :key="testCategory.category_id"
          class="testcategory"
        >
          <v-list-tile-action v-if="!isReadonly">
            <v-btn
              icon
              @click="askDeleteTestCategory(index)"
            >
              <v-icon>
                delete
              </v-icon>
            </v-btn>
          </v-list-tile-action>
          <v-list-tile-content>
            <v-list-tile-title>
              {{ testCategory.name }}
              <span
                v-if="testCategory.question_count"
                class="grey--text"
              >
                ({{ testCategory.question_count }} {{ testCategory.question_count == 1 ? 'Frage' : 'Fragen' }})
              </span>
            </v-list-tile-title>
            <v-list-tile-sub-title>
              {{ testCategory.points }} Punkte
            </v-list-tile-sub-title>
          </v-list-tile-content>
          <v-list-tile-action class="testcategory-action">
            <v-text-field
              label="Anzahl Fragen"
              placeholder="1"
              type="number"
              min="1"
              step="1"
              :max="testCategory.question_count"
              :readonly="isReadonly"
              style="min-width: 90px"
              v-model.number="testCategory.question_amount"
              @change="changeInput(testCategory)"
            />
          </v-list-tile-action>
        </v-list-tile>
      </v-list>
    </v-card-text>
    <v-card-actions v-if="!isReadonly">
      <v-btn
        color="primary"
        :disabled="isSaving"
        :loading="isSaving"
        @click="saveCategories"
      >
        Kategorien speichern
      </v-btn>
    </v-card-actions>
  </v-card>
</template>

<script>
import {mapGetters} from 'vuex'
import CategoriesModal from './CategoriesModal'

export default {
  props: {
    test: {
      type: Object
    }
  },
  data() {
    return {
      deletionDialog: false,
      indexToDelete: null,
      isSaving: false,
      testCategories: [],
    }
  },
  mounted() {
    // parse preloaded categories
    this.test.test_categories.forEach((testCategory => {
      this.testCategories.push({
        id: testCategory.category_id,
        name: testCategory.category.name,
        points: testCategory.category.points,
        question_amount: testCategory.question_amount,
        question_count: testCategory.question_count,
      })
    }))
  },
  watch: {
    testCategories: {
      handler() {
        this.updateCount()
      },
      deep: true,
    },
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    isReadonly() {
      return !this.myRights['tests-edit']
    },
  },
  methods: {
    addCategories(categoryData) {
      categoryData.forEach((categoryDataEntry) => {
        if (!this.testCategories.some(tC => tC.id == categoryDataEntry.id)) {
          this.testCategories.push({
            id: categoryDataEntry.id,
            category_icon_url: categoryDataEntry.category_icon_url,
            question_count: categoryDataEntry.question_count,
            name: categoryDataEntry.name,
            points: categoryDataEntry.points,
            question_amount: 1,
          })
        }
      })
    },
    askDeleteTestCategory(index) {
      this.indexToDelete = index
      this.deletionDialog = true
    },
    deleteTestCategory() {
      this.$delete(this.testCategories, this.indexToDelete)
      this.deletionDialog = false
    },
    saveCategories() {
      this.isSaving = true
      axios.post(`/backend/api/v1/tests/${this.test.id}/categories`, {
        categories: this.testCategories,
      }).then(response => {
        if (response.data.success) {
          this.$emit('message', {
            type: 'success',
            message: 'Die Test-Kategorien wurden gespeichert.',
          })
        } else {
          this.$emit('message', {
            type: 'error',
            message: response.data.error,
          })
        }
      }).catch(error => {
        this.$emit('message', {
          type: 'error',
          message: 'Ein unerwarteter Fehler ist aufgetreten.',
        })
      }).finally(() => {
        this.isSaving = false
      })
    },
    changeInput(testCategory) {
      testCategory.question_amount = Math.min(testCategory.question_count, Math.max(1, testCategory.question_amount))
      this.updateCount()
    },
    updateCount() {
      this.$emit('update', this.testCategories.reduce((accumulator, category) => {
        return accumulator + Number(category.question_amount)
      }, 0))
    },
  },
  components: {
    CategoriesModal,
  },
};
</script>
