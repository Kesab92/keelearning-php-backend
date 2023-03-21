<template>
  <v-dialog
    max-width="500"
    persistent
    v-model="dialog">
    <v-form
      v-model="formIsValid"
      @submit.prevent="createReporting">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Neues Reporting erstellen
        </v-card-title>
        <v-card-text>

          <v-text-field
            v-model="emails"
            :rules="[$rules.email]"
            autofocus
            label="Empfänger E-Mail Adressen"
            hide-details
            required
            class="mb-3 mt-2"
            box />
          <tag-select
            v-model="tagIds"
            color="blue-grey lighten-2"
            label="Benutzer-TAGs"
            multiple
            box
            limitToTagRights
          />
        </v-card-text>
        <v-card-actions>
          <v-btn
            @click="closeModal"
            flat>
            Abbrechen
          </v-btn>
          <v-spacer />
          <v-btn
            :loading="isSaving"
            :disabled="isSaving || !formIsValid"
            color="primary"
            type="submit"
            flat>
            Reporting erstellen
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-form>
  </v-dialog>
</template>


<script>
import TagSelect from "../global/TagSelect";
export default {
  props: {
    type: {
      type: Number,
      required: true,
    },
    value: {
      type: Boolean,
      required: true,
    },
    editRouteName: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      formIsValid: true,
      isSaving: false,
      emails: null,
      tagIds: [],
    }
  },
  methods: {
    createReporting() {
      if (!this.formIsValid || this.isSaving) {
        return
      }
      this.isSaving = true

      const tagIds = this.tagIds

      axios.post('/backend/api/v1/reportings', {
        emails: this.emails,
        type: this.type,tag_ids: this.tagIds,
        tagIds,
      }).then(response => {
        this.$router.push({
          name: this.editRouteName,
          params: {
            reportingId: response.data.reporting.id,
          },
        })
        this.$store.dispatch('reportings/updateReportings', this.type)
        this.closeModal()
      }).catch(e => {
        if(e.response) {
          alert(e.response.data.message)
        } else {
          alert('Das Reporting konnte nicht erstellt werden.')
        }
      }).finally(() => {
        this.isSaving = false
      })
    },
    closeModal() {
      this.emails = null
      this.tagIds = []
      this.dialog = false
    }
  },
  computed: {
    dialog: {
      set(value) {
        this.$emit('input', value)
      },
      get() {
        return this.value
      }
    }
  },
  components: {
    TagSelect,
  },
}
</script>
