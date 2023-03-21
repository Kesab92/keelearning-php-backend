<template>
  <div>
    <v-card>
      <v-toolbar>
        <v-btn
          href="/tests#/tests"
          icon
        >
          <v-icon>keyboard_backspace</v-icon>
        </v-btn>
        <v-text-field
          v-model="editable.name"
          :readonly="isReadonly" />
        <v-text-field
          class="ml-2"
          ref="testUrl"
          readonly
          :value="test.url"
          style="max-width:350px"
          @click="selectUrl"
        />
        <template v-if="!isReadonly">
          <v-spacer />
          <v-btn
            v-if="editable.archived === 0"
            :loading="isSavingTest"
            outline
            @click="archive"
          >
            Archivieren
          </v-btn>
          <v-btn
            v-if="editable.archived === 1"
            :loading="isSavingTest"
            outline
            @click="unarchive"
          >
            Dearchivieren
          </v-btn>
          <v-btn
            :loading="isSavingTest"
            color="red"
            outline
            @click="remove"
          >
            Löschen
          </v-btn>
        </template>
      </v-toolbar>
      <v-layout
        row
        wrap>
        <v-flex
          xs6
          class="pl-4 pr-4 pt-4">
          <v-subheader>
            Sichtbar für folgende User:
          </v-subheader>
          <div class="field">
            <v-select
              item-text="label"
              item-value="id"
              label="TAG"
              chips
              multiple
              :items="formattedTags"
              :readonly="isReadonly"
              v-model="editable.tag_ids"
              @change="resetQuizTeam"
            />
            <template v-if="editable.quiz_team_id">
              <div class="ui horizontal divider">legacy</div>
              <v-select
                item-text="name"
                item-value="id"
                label="Quiz-Team"
                :items="quizTeams"
                v-model="editable.quiz_team_id"
                readonly
              />
            </template>
          </div>
          <v-subheader>
            Bei Bestehen werden folgende TAGs gesetzt:
          </v-subheader>
          <div class="field">
            <v-select
              item-text="label"
              item-value="id"
              label="TAG"
              chips
              multiple
              :items="formattedAwardTags"
              :readonly="isReadonly"
              v-model="editable.award_tag_ids"
            />
          </div>
        </v-flex>
        <v-flex
          xs6
          class="pl-4 pr-4 pt-4">
          <v-subheader>
            Einstellungen
          </v-subheader>
          <v-layout
            row
            wrap>
            <v-flex
              xs6
              px-2>
              <v-text-field
                label="Mindestpunktzahl"
                placeholder="0 - 100"
                suffix="%"
                type="number"
                :readonly="isReadonly"
                v-model="editable.min_rate"
              />
            </v-flex>
            <v-flex
              xs6
              px-2>
              <v-text-field
                label="Maximale Versuche?"
                placeholder="Unbegrenzt"
                type="number"
                :readonly="isReadonly"
                v-model="editable.attempts"
              />
            </v-flex>
            <v-flex
              xs12
              px-2>
              <v-checkbox
                v-model="editable.repeatable_after_pass"
                label="Test nach Bestehen weiterhin wiederholbar?"
                :readonly="isReadonly"
              />
            </v-flex>
            <v-flex
              xs6
              px-2>
              <v-menu
                ref="menu"
                v-model="datePickerOpen"
                :close-on-content-click="false"
                :disabled="isReadonly"
                :nudge-right="40"
                lazy
                transition="scale-transition"
                offset-y
                full-width
                min-width="290px"
                style="z-index: 11"
              >
                <v-text-field
                  slot="activator"
                  label="Test endet am"
                  prepend-icon="event"
                  :value="formatDate(editable.active_until)"
                  readonly
                />
                <v-date-picker
                  v-if="!isReadonly"
                  v-model="editable.active_until"
                  no-title
                  scrollable
                  first-day-of-week="1"
                  locale="de-DE"
                  @input="datePickerOpen = false"
                />
              </v-menu>
            </v-flex>
            <v-flex
              xs6
              px-2>
              <v-text-field
                label="Zeitangabe in Minuten"
                :placeholder="`${ placeholderMinutes } Minuten`"
                prepend-icon="access_time"
                :readonly="isReadonly"
                type="number"
                v-model="test.minutes"
              />
            </v-flex>
            <v-flex
              xs12
              px-2>
              <v-checkbox
                v-model="editable.no_download"
                label="Zertifikats-Download durch Benutzer deaktivieren"
                :readonly="isReadonly" />
            </v-flex>
            <v-flex
              xs12
              px-2>
              <v-checkbox
                v-model="editable.send_certificate_to_admin"
                label="Zertifikat per E-Mail an Admin senden"
                :readonly="isReadonly" />
            </v-flex>
          </v-layout>
        </v-flex>
        <v-flex
          xs12
          class="pl-4 pr-4 pb-4">
          <v-layout row>
            <v-flex
              xs12
              md4>
              <v-layout
                row
                wrap>
                <v-flex
                  xs12
                  lg5
                  class="pr-4">
                  <v-subheader>
                    Icon
                  </v-subheader>
                  <TestIcon
                    :current-image="editable.icon_url"
                    :readonly="isReadonly"
                    :test="test"
                    @newImage="handleNewIcon" />
                </v-flex>
                <v-flex
                  xs12
                  lg7
                  class="pr-4">
                  <v-subheader>
                    Cover Bild
                  </v-subheader>
                  <TestCoverImage
                    :current-image="editable.cover_image_url"
                    :readonly="isReadonly"
                    :test="test"
                    @newImage="handleNewCoverImage" />
                </v-flex>
              </v-layout>
            </v-flex>
            <v-flex
              xs12
              md8>
              <v-subheader>
                Beschreibung
              </v-subheader>
              <TextEditor
                :height="200"
                :disabled="isReadonly"
                v-model="editable.description" />
            </v-flex>
          </v-layout>
        </v-flex>
      </v-layout>
      <v-card-actions class="pr-4 pl-4">
        <v-btn
          v-if="!isReadonly"
          color="primary"
          :disabled="isSavingTest"
          :loading="isSavingTest"
          @click="saveTest"
        >
          Einstellungen speichern
        </v-btn>

        <v-spacer />

        <v-btn
          :href="`/tests/${test.id}/certificates`"
          color="primary"
          outline
        >
          Zertifikat
        </v-btn>

        <v-btn
          :href="`/tests/${test.id}/reminders`"
          color="primary"
          outline
        >
          Erinnerungen
        </v-btn>
        <v-btn
          v-if="myRights['tests-stats']"
          :href="`/tests/${test.id}/results`"
          color="primary"
          outline
        >
          Statistiken
        </v-btn>
      </v-card-actions>
    </v-card>

    <DeleteDialog
      v-model="deleteDialogOpen"
      :deletion-url="`/backend/api/v1/tests/${test.id}/delete`"
      :dependency-url="`/backend/api/v1/tests/${test.id}/delete-information`"
      :entry-name="test.name"
      :redirect-url="afterDeletionRedirectURL"
      type-label="Test"/>
  </div>
</template>

<script>
import moment from 'moment'
import TestCoverImage from "./TestCoverImage"
import TestIcon from "./TestIcon"
import DeleteDialog from "../partials/global/DeleteDialog"
import TextEditor from "../partials/global/TextEditor"
import {mapGetters} from "vuex";

export default {
  props: {
    quizTeams: {
      type: Array,
    },
    placeholderMinutes: {
      default: 2,
      required: false,
      type: Number,
    },
    tags: {
      type: Array,
    },
    test: {
      type: Object,
    },
  },
  data() {
    return {
      datePickerOpen: false,
      editable: {
        active_until: null,
        attempts: null,
        award_tag_ids: [],
        quiz_team_id: null,
        min_rate: null,
        minutes: null,
        name: null,
        no_download: null,
        send_certificate_to_admin: null,
        tag_ids: [],
        description: null,
        cover_image_url: null,
        archived: false,
      },
      isSavingTest: false,
      deleteDialogOpen: false,
    }
  },
  mounted() {
    this.$set(this, 'editable', this.test)
    this.editable.active_until = this.test.active_until ? moment(this.test.active_until).format('YYYY-MM-DD') : null
    this.$set(this.editable, 'award_tag_ids', this.test.award_tags.map(tag => tag.id))
    this.$set(this.editable, 'tag_ids', this.test.tags.map(tag => tag.id))
    this.editable.attempts = this.editable.attempts || null
  },
  computed: {
    ...mapGetters({
      myRights: 'app/myRights',
    }),
    formattedTags() {
      if (!this.tags) {
        return null
      }
      return this.tags.map((tag) => {
        let label = tag.label
        if (tag.tag_group) {
          label = `${tag.tag_group.name}: ${tag.label}`
        }
        return {
          group_id: tag.tag_group_id,
          group_name: tag.tag_group ? tag.tag_group_name : null,
          id: tag.id,
          label,
        }
      }).sort((tagA, tagB) => {
        if (tagA.label.toLowerCase() < tagB.label.toLowerCase()) {
          return -1
        }
        return 1
      })
    },
    formattedAwardTags() {
      if (!this.formattedTags) {
        return null
      }
      let selectedTagGroupIds = this.formattedTags
        .filter(tag => tag.group_id && this.editable.award_tag_ids.indexOf(tag.id) > -1)
        .map(tag => tag.group_id)
      return this.formattedTags.map((tag) => {
        tag = {...tag}
        if (
          tag.group_id
          && selectedTagGroupIds.indexOf(tag.group_id) > -1
          && this.editable.award_tag_ids.indexOf(tag.id) == -1
        ) {
          tag.disabled = true
        }
        return tag
      })
    },
    isReadonly() {
      return !this.myRights['tests-edit']
    },
    afterDeletionRedirectURL() {
      return "/tests#/tests"
    },
  },
  methods: {
    formatDate(date) {
      return this.$options.filters.date(date)
    },
    resetQuizTeam() {
      this.editable.quiz_team_id = null
    },
    handleNewCoverImage(coverImageUrl) {
      this.$set(this.editable, 'cover_image_url', coverImageUrl)
    },
    handleNewIcon(iconUrl) {
      this.$set(this.editable, 'icon_url', iconUrl)
    },
    saveTest() {
      this.isSavingTest = true
      let testData = {
        active_until             : this.editable.active_until,
        attempts                 : this.editable.attempts,
        award_tag_ids            : this.editable.award_tag_ids,
        cover_image_url          : this.editable.cover_image_url,
        description              : this.editable.description,
        icon_url                 : this.editable.icon_url,
        min_rate                 : this.editable.min_rate,
        minutes                  : this.editable.minutes,
        name                     : this.editable.name,
        no_download              : this.editable.no_download,
        repeatable_after_pass    : this.editable.repeatable_after_pass,
        send_certificate_to_admin: this.editable.send_certificate_to_admin,
      }
      if (!this.editable.quiz_team_id) {
        testData.tag_ids = this.editable.tag_ids
      }
      axios.post(`/backend/api/v1/tests/${this.test.id}/update`, testData).then(response => {
        if (response.data.success) {
          this.$emit('message', {
            type: 'success',
            message: 'Der Test wurde gespeichert.',
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
        this.isSavingTest = false
      })
    },
    selectUrl() {
      this.$refs.testUrl.$refs.input.select()
    },
    remove() {
      this.deleteDialogOpen = true
    },
    archive() {
      const confirmArchive = confirm("Möchten Sie diesen Test wirklich archivieren?")
      if (!confirmArchive) {
        return
      }
      axios.post(`/backend/api/v1/tests/${this.test.id}/archive`).then(response => {
        if (response.data.success) {
          this.editable.archived = 1
          this.$emit('message', {
            type: 'success',
            message: 'Der Test wurde archiviert.',
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
          message: 'Der Test konnte leider nicht archiviert werden.',
        })
      }).finally(() => {
        this.isSavingTest = false
      })
    },
    unarchive() {
      const confirmUnarchive = confirm("Möchten Sie diesen Test wirklich wiederherstellen?")
      if (!confirmUnarchive) {
        return
      }
      axios.post(`/backend/api/v1/tests/${this.test.id}/unarchive`).then(response => {
        if (response.data.success) {
          this.editable.archived = 0
          this.$emit('message', {
            type: 'success',
            message: 'Der Test wurde dearchiviert.',
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
          message: 'Der Test konnte leider nicht dearchiviert werden.',
        })
      }).finally(() => {
        this.isSavingTest = false
      })
    },
    handleTestDeleted() {
      window.location.href = `/tests`
    },
  },
  components: {
    TestIcon,
    TestCoverImage,
    DeleteDialog,
    TextEditor,
  },
}
</script>
