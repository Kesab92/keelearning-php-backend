<template>
  <v-list-tile
    @click="modalOpen = true"
    :class="{
      inactive: inactive,
    }"
    class="overwrite-default-height"
  >
    <v-list-tile-avatar>
      <v-icon class="grey lighten-1 white--text">
        laptop
      </v-icon>
    </v-list-tile-avatar>

    <v-list-tile-content style="height: 50px">
      <v-list-tile-title>
        {{ webinar.topic }}
        <span class="grey--text text--lighten-1">
          ({{ webinar.starts_at | dateTime }}, {{ durationText }})
        </span>
      </v-list-tile-title>
      <v-list-tile-sub-title
        v-if="tagNames || webinar.additional_users_count"
        class="tag-names"
      >
        {{ tagNames }}
        <span v-if="webinar.additional_users_count">
          <template v-if="tagNames">
            <template v-if="webinar.additional_users_count == 1">
              +1 zusätzlicher Benutzer
            </template>
            <template v-else>
              +{{ webinar.additional_users_count }} zusätzliche Benutzer
            </template>
          </template>
          <template v-else>
            {{ webinar.additional_users_count }} Benutzer
          </template>
        </span>
      </v-list-tile-sub-title>
    </v-list-tile-content>

    <v-list-tile-action>
      <v-chip
        v-if="inactive"
        outline
        class="inactive-badge"
        @click.stop
      >
        abgelaufen
      </v-chip>
      <template v-else>
        <v-btn
          v-if="webinar.join_link"
          color="info"
          target="_blank"
          :href="webinar.join_link"
          icon
          flat
          @click.stop
        >
          <v-icon dark>
            link
          </v-icon>
        </v-btn>
        <v-tooltip v-else left>
          <v-btn
            slot="activator"
            color="info"
            target="_blank"
            :href="webinar.join_link"
            icon
            flat
            disabled
            @click.stop
          >
            <v-icon>
              link
            </v-icon>
          </v-btn>
          <span>
            Ihr Benutzeraccount hat keine Zugangsberechtigung zu diesem Webinar.
          </span>
        </v-tooltip>
      </template>
    </v-list-tile-action>
    <webinar-modal
      :open="modalOpen"
      :webinarId="webinar.id"
      :tags="tags"
      @delete="$emit('delete', $event)"
      @setOpen="setModalOpen"
      @update="updateWebinar"
    />
  </v-list-tile>
</template>

<script>
import moment from 'moment'
import WebinarModal from './WebinarModal'

export default {
  props: {
    webinar: {
      required: true,
      type: Object,
    },
    tags: {
      required: true,
      type: Array,
    },
  },
  data() {
    return {
      modalOpen: false,
    }
  },
  methods: {
    updateWebinar(webinar) {
      this.$emit('update', webinar)
    },
    setModalOpen(open) {
      this.modalOpen = open
    },
  },
  computed: {
    inactive() {
      if (!this.webinar.duration_minutes) {
        return false
      }
      return moment(this.webinar.starts_at).add(this.webinar.duration_minutes, 'minutes').isBefore()
    },
    tagNames() {
      return this.tags.filter(tag => {
        return this.webinar.tag_ids.indexOf(tag.id) > -1
      }).map(tag => tag.label)
        .join(', ')
    },
    durationText() {
      if (!this.webinar.duration_minutes) {
        return 'Unbegrenzt'
      }
      return this.webinar.duration_minutes + ' Minuten'
    },
  },
  components: {
    WebinarModal,
  },
}
</script>

<style lang="scss">
#app .overwrite-default-height {
  .v-list__tile {
    height: auto !important;
    padding-bottom: 10px;
    padding-top: 10px;
  }

  .v-list__tile__content {
    height: auto !important;
  }

  .tag-names {
    overflow: visible !important;
    white-space: normal !important;
  }

  .inactive-badge {
    margin-top: -16px;
    position: absolute;
    right: 0;
    top: 50%;
  }
}
</style>
