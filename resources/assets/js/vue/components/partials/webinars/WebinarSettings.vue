<template>
  <div>
    <v-text-field
      v-model="webinar.topic"
      :rules="rules.topic"
      label="Thema"
      placeholder="Thema"
      required
    />
    <TextEditor
      v-model="webinar.description"
      label="Beschreibung"
    />
    <v-layout row>
      <v-flex
        md4
        pr-4>
        <v-menu
          v-model="startsAtDatepicker"
          :close-on-content-click="false"
          :nudge-right="40"
          full-width
          lazy
          min-width="290px"
          offset-y
          transition="scale-transition"
        >
          <v-text-field
            slot="activator"
            :value="formatDate(webinar.starts_at_date)"
            label="Datum"
            prepend-icon="event"
            readonly
          />
          <v-date-picker
            v-model="webinar.starts_at_date"
            no-title
            scrollable
            first-day-of-week="1"
            locale="de-DE"
            @input="startsAtDatepicker = false"
          />
        </v-menu>
      </v-flex>
      <v-flex
        md4
        px-4>
        <v-menu
          v-model="startsAtTimepicker"
          :close-on-content-click="false"
          :nudge-right="40"
          full-width
          lazy
          min-width="290px"
          offset-y
          transition="scale-transition"
        >
          <v-text-field
            slot="activator"
            :value="webinar.starts_at_time"
            label="Startzeit"
            prepend-icon="access_time"
            readonly
          />
          <v-time-picker
            v-if="startsAtTimepicker"
            v-model="webinar.starts_at_time"
            format="24hr"
            no-title
            @click:minute="startsAtTimepicker = false"
          />
        </v-menu>
      </v-flex>
      <v-flex
        md4
        pl-4>
        <v-select
          v-model="webinar.duration_minutes"
          :items="durations"
          label="Dauer"
        />
      </v-flex>
    </v-layout>
    <v-select
      v-model="webinar.tag_ids"
      :items="formattedTags"
      chips
      item-text="label"
      item-value="id"
      label="Sichtbar für Benutzer mit TAGs"
      multiple
    />
    <div>
      <v-checkbox
        v-model="webinar.send_reminder"
      >
        <template slot="label">
          15 Minuten vor Beginn Reminder-Mail an Teilnehmer senden
          <v-btn
            color="info"
            flat
            href="/mails?edit=WebinarReminder"
            icon
            @click.stop
          >
            <v-icon>settings</v-icon>
          </v-btn>
        </template>
      </v-checkbox>
    </div>
    <v-checkbox
      v-model="webinar.show_recordings"
      label="Aufnahmen für Teilnehmer verfügbar machen"/>
  </div>
</template>

<script>
import TextEditor from "../global/TextEditor"

const durations = [{
  text: "Dauerhaft",
  value: null,
}]
const durationMinutes = [15, 30, 45, 1 * 60, 2 * 60, 3 * 60, 4 * 60, 5 * 60, 7 * 60, 8 * 60, 10 * 60]
durationMinutes.forEach((d) => {
  durations.push({
    text: d % 60 ? `${d}min` : `${d / 60}h`,
    value: d,
  })
})

const rules = {
  topic: [
    entry => !!entry && entry.length > 0 || "Das Webinar benötigt ein Thema.",
  ],
}

export default {
  props: [
    "tags",
    "value",
  ],
  data() {
    return {
      durations,
      rules,
      startsAtDatepicker: false,
      startsAtTimepicker: false,
    }
  },
  computed: {
    webinar: {
      get() {
        return this.value
      },
      set(webinar) {
        this.$emit("input", webinar)
      },
    },
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
  },
  methods: {
    formatDate(date) {
      return this.$options.filters.date(date)
    },
  },
  components: {
    TextEditor,
  },
}
</script>
