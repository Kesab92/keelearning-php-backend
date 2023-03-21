<template>
  <div>
    <ModuleIntro/>
    <UserTabs/>
    <AddUsersModal
      v-model="addUsersModalOpen"
      @refresh="loadData"
    />
    <v-snackbar
      v-model="snackbar"
      multi-line
      :timeout="5000"
      top
    >
      {{ snackbarMessage }}
      <v-btn
        color="primary"
        flat
        @click="snackbar = false"
      >
        Okay
      </v-btn>
    </v-snackbar>
    <v-dialog
      max-width="500"
      persistent
      v-model="deleteUsersModal">
      <v-card>
        <v-progress-circular
          :size="50"
          class="mx-auto my-4"
          color="primary"
          indeterminate
          v-if="deleteUsersInfo === null"/>
        <template v-else>
          <v-card-title
            class="headline grey lighten-2"
            primary-title>
            Benutzer wirklich löschen?
          </v-card-title>
          <v-card-text v-if="deleteUsersErrors === null">
            Die gewählten Benutzer und die dazugehörigen Abhängigkeiten werden anonymisiert oder gelöscht:<br><br>
            {{ deleteUsersInfo.users.length }} Benutzer werden gelöscht,<br>
            {{ deleteUsersInfo.accesslogs }} AccessLogs werden gelöscht,<br>
            {{ deleteUsersInfo.games }} Spiele werden anonymisiert,<br>
            {{ deleteUsersInfo.suggestions }} Fragenvorschläge werden anonymisiert,<br>
            {{ deleteUsersInfo.testSubmissions }} Testergebnisse werden gelöscht,<br>
            {{ deleteUsersInfo.voucherCodes }} eingelöste Gutscheincodes werden anonymisiert,<br>
            {{ deleteUsersInfo.groups }} Quiz-Team-Besitzer, sowie {{ deleteUsersInfo.tags }} TAG-Besitzer werden
            anonymisiert.<br><br>
            Aus den Quiz-Teams werden {{ deleteUsersInfo.group_member }} Mitglieder entfernt.<br><br>
            Möchten Sie diese Benutzer inklusive deren Abhängigkeiten wirklich anonymisieren?
          </v-card-text>
          <v-card-text v-else>
            Die gewählten Benutzer können nicht gelöscht werden: <br>
            <ul>
              <li
                :key="index"
                v-for="(error, index) in deleteUsersErrors">
                {{ error }}
              </li>
            </ul>
          </v-card-text>
          <v-card-actions>
            <v-spacer/>
            <v-btn
              :disabled="deleteUsersLoading || deleteUsersErrors !== null"
              :loading="deleteUsersLoading"
              @click="deleteUsers"
              color="error"
              flat>
              <v-icon left>delete</v-icon>
              Löschen & Anonymisieren
            </v-btn>
            <v-btn
              :disabled="deleteUsersLoading"
              @click="closeDeleteUsersModal"
              flat>
              Abbrechen
            </v-btn>
          </v-card-actions>
        </template>
      </v-card>
    </v-dialog>
    <v-dialog
      max-width="500"
      persistent
      v-model="addTagsModal">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          TAGs hinzufügen
        </v-card-title>
        <v-card-text>
          Ordne {{ selected.length }} ausgewählte{{ selected.length > 1 ? "n" : "m" }}
          Benutzer{{ selected.length > 1 ? "n" : "" }} diese TAGs zu:
          <br>
          <tag-select
            v-model="selectedAdditionalTags"
            limit-to-tag-rights
            multiple/>
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            :disabled="addTagsLoading"
            :loading="addTagsLoading"
            @click="addTags"
            color="success"
            flat>
            <v-icon left>add</v-icon>
            Hinzufügen
          </v-btn>
          <v-btn
            :disabled="addTagsLoading"
            @click="closeAddTagsModal"
            flat>
            Abbrechen
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog
      max-width="500"
      persistent
      v-model="deleteTagsModal">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          TAGs entfernen
        </v-card-title>
        <v-card-text>
          Entferne bei {{ selected.length }} ausgewählte{{ selected.length > 1 ? "n" : "m" }}
          Benutzer{{ selected.length > 1 ? "n" : "" }} diese TAGs:
          <br>
          <tag-select
            v-model="selectedAdditionalTags"
            limit-to-tag-rights
            multiple/>
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            :disabled="deleteTagsLoading"
            :loading="deleteTagsLoading"
            @click="deleteTags"
            color="success"
            flat>
            <v-icon left>remove</v-icon>
            Entfernen
          </v-btn>
          <v-btn
            :disabled="deleteTagsLoading"
            @click="closeDeleteTagsModal"
            flat>
            Abbrechen
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog
      max-width="500"
      persistent
      v-model="expirationModal">
      <v-card>
        <v-card-title
          class="headline grey lighten-2"
          primary-title>
          Löschdatum setzen
        </v-card-title>
        <v-card-text>
          <DatePicker
            v-model="expiresAt"
            label="Automatisches Löschdatum"
            :clearable="false"/>
          <p v-if="expiresAt">
            Am {{ expiresAt | date }} werden alle ausgewählten Benutzer automatisch gelöscht
          </p>
        </v-card-text>
        <v-card-actions>
          <v-spacer/>
          <v-btn
            :disabled="expirationLoading"
            :loading="expirationLoading"
            @click="setExpiration"
            color="success"
            flat>
            <v-icon left>add</v-icon>
            Speichern
          </v-btn>
          <v-btn
            :disabled="expirationLoading"
            @click="closeSetExpiresAtModal"
            flat>
            Abbrechen
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <warnings :warnings="warnings"/>
    <v-layout row>
      <v-flex shrink>
        <v-btn
          v-if="myRights['users-edit']"
          color="primary"
          @click="addUsersModalOpen = true">
          <v-icon
            dark
            left>send
          </v-icon>
          Einladen
        </v-btn>
      </v-flex>
      <v-flex
        v-if="!readonly"
        shrink>
        <v-overflow-btn
          class="s-actionsButton"
          v-model="action"
          :items="actions"
          :loading="actionLoading"
          label="Aktionen"/>
      </v-flex>
      <v-spacer/>
      <v-flex shrink>
        <v-tooltip
          v-if="canExport"
          bottom>
          <v-btn
            :href="exportLink"
            target="_blank"
            color="primary"
            slot="activator">
            <v-icon
              dark
              left>cloud_download
            </v-icon>
            Exportieren
          </v-btn>
          <span>Der Export wird mit den aktuellen Einstellungen gefiltert.</span>
        </v-tooltip>
      </v-flex>
    </v-layout>

    <v-card class="mt-2 mb-4">
      <v-card-title primary-title>
        <v-layout
          row
          align-center>
          <v-flex xs4>
            <v-select
              :items="filters"
              class="mr-4"
              clearable
              label="Filter"
              v-model="filter"/>
          </v-flex>
          <v-flex xs4>
            <TagSelect
              v-model="selectedTags"
              class="mr-4"
              :extend-items="getTagItems"
              limit-to-tag-rights
              multiple/>
          </v-flex>
          <v-flex xs4>
            <v-text-field
              append-icon="search"
              clearable
              :placeholder="searchPlaceholder"
              single-line
              v-model="search"/>
          </v-flex>
        </v-layout>
      </v-card-title>
      <v-data-table
        :headers="headers"
        :items="users"
        :loading="isLoading"
        :pagination.sync="pagination"
        :rows-per-page-items="[50, 100, 200]"
        :total-items="userCount"
        class="elevation-1 users-table"
        item-key="id"
        :select-all="!readonly"
        v-model="selected">
        <tr
          @click="editUser(props.item.id)"
          class="clickable"
          slot="items"
          slot-scope="props">
          <td
            v-if="!readonly"
            width="80px">
            <v-checkbox
              @click.stop="props.selected = !props.selected"
              hide-details
              primary
              v-model="props.selected"/>
          </td>
          <td>
            {{ props.item.username }}
            <div
              v-if="props.item.email"
              class="grey--text">
              {{ props.item.email }}
            </div>
            <v-layout row wrap align-center>
              <div
                v-if="props.item.role"
                class="deep-orange--text mr-2">
                {{ props.item.role.name }}
              </div>
              <div
                v-if="props.item.tag_rights_relation.length"
                class="mr-2">
                <v-tooltip bottom>
                  <span slot="activator">(eingeschränkt)</span>
                  Dieser Admin ist durch eine TAG-Beschränkung eingeschränkt
                </v-tooltip>
              </div>
              <div
                v-if="props.item.role && !props.item.role.is_main_admin && !props.item.role.rights.length"
                class="mr-2 red--text">
                Admin - ohne Rechte
              </div>
              <div
                v-if="props.item.is_keeunit"
                class="blue--text">
                <v-icon>support_agent</v-icon>
                <span style="vertical-align: text-bottom;">keeunit</span>
              </div>
            </v-layout>
          </td>
          <td>
            <v-chip
              :key="`${props.item.id}-${tag.id}`"
              disabled
              small
              v-for="tag in props.item.tags">
              {{ tag.label }}
            </v-chip>
          </td>
          <td class="no-wrap">
            {{ props.item.created_at | dateTime }}
          </td>
          <td class="no-wrap">
            <template v-if="props.item.last_activity">
              {{ props.item.last_activity | dateTime }}
            </template>
            <template v-else>
              <v-tooltip left>
                <div
                  class="grey--text"
                  slot="activator">
                  Unbekannt
                </div>
                <span>
                  Die Erfassung der letzten Aktivität wurde zum 24.08.2020 eingeführt.
                </span>
              </v-tooltip>
            </template>
          </td>
          <td class="no-wrap">
            <template v-if="(!props.item.role || !props.item.role.is_main_admin) && props.item.expires_at_combined">
              {{ props.item.expires_at_combined | date }}
              <div
                v-if="props.item.expires_at"
                class="grey--text">
                via Benutzerverwaltung
              </div>
              <div
                v-else
                class="grey--text">
                via Voucher
              </div>
            </template>
            <span
              class="grey--text"
              v-else>
              n/a
            </span>
          </td>
          <td>
            #{{ props.item.id }}
          </td>
        </tr>
        <template slot="no-data">
          <v-alert
            :value="true"
            type="info"
            v-show="(!users || users.length === 0) && !isLoading">
            Es wurden keine Benutzer gefunden.
          </v-alert>
        </template>
        <template slot="actions-prepend">
          <div class="page-select">
            Page:
            <v-select
              :items="pageSelectOptions"
              v-model="pagination.page"
              class="pagination"/>
          </div>
        </template>
      </v-data-table>
    </v-card>
    <UserSidebar @dataUpdated="loadData"/>
    <VoucherSidebar
      root-url="users.edit.management"
      route-prefix="users."/>
  </div>
</template>

<script>
import {debounce} from "lodash"
import {mapGetters} from "vuex"
import DatePicker from "../partials/global/Datepicker"
import Warnings from "./Warnings.vue"
import TagSelect from "../partials/global/TagSelect"
import ModuleIntro from "./ModuleIntro"
import UserSidebar from "./UserSidebar"
import AddUsersModal from "./AddUsersModal"
import VoucherSidebar from "../vouchers/VoucherSidebar"
import tableConfig from "../../mixins/tableConfig"
import UserTabs from "./UserTabs"

let axiosCancel = null
const paginationDefaults = {
  page: 1,
  rowsPerPage: 50,
  sortBy: "id",
}

export default {
  props: {
    tags: Array,
    readonly: Boolean,
    canExport: Boolean,
  },
  mixins: [
    tableConfig,
  ],
  data() {
    return {
      addUsersModalOpen: false,
      addTagsLoading: false,
      addTagsModal: false,
      deleteTagsLoading: false,
      deleteTagsModal: false,
      filter: null,
      deleteUsersErrors: null,
      deleteUsersInfo: null,
      deleteUsersLoading: false,
      deleteUsersModal: false,
      expirationModal: false,
      expirationLoading: false,
      userCount: null,
      isLoading: true,
      pagination: {...paginationDefaults},
      search: null,
      selected: [],
      watchSettings: false,
      selectedAdditionalTags: [],
      selectedTags: [],
      users: [],
      roles: [],
      expiresAt: null,
      warnings: {},
      snackbar: false,
      snackbarMessage: null,
      action: null,
      actionLoading: null,
      actions: [
        {
          text: 'TAGs hinzufügen',
          value: 'openAddTagsModal',
        },
        {
          text: 'TAGs entfernen',
          value: 'openDeleteTagsModal',
        },
        {
          text: 'Benutzer löschen',
          value: 'openDeleteUsersModal',
        },
        {
          text: 'Benutzer erneut einladen',
          value: 'reinvite',
        },
        {
          text: 'Löschdatum setzen',
          value: 'openExpirationModal',
        },
      ],
    }
  },
  watch: {
    $route() {
      if (this.$route.name === 'users.index') {
        this.restoreConfig()
        this.loadData()
      }
    },
    filter() {
      this.storeConfig()
    },
    search: debounce(function () {
      this.storeConfig()
    }, 300),
    selectedTags() {
      this.storeConfig()
    },
    action() {
      if (this.action) {
        if (!this.selected.length) {
          alert('Bitte wählen Sie zuerst Benutzer aus der Liste aus')
        } else {
          if (this.readonly) {
            alert('Sie haben nicht die benötigte Berechtigung um diese Aktion auszuführen.')
          } else {
            // Call the action handler
            this[this.action]()
          }
        }
        this.$nextTick(() => {
          this.action = null
        })
      }
    },
    pagination: {
      deep: true,
      handler() {
        this.loadData()
      },
    },
  },
  created() {
    this.loadWarnings()
  },
  computed: {
    ...mapGetters({
      isSuperAdmin: 'app/isSuperAdmin',
      myRights: 'app/myRights',
      showEmails: 'app/showEmails',
      showPersonalData: 'app/showPersonalData',
    }),
    filters() {
      let filters = [
        {
          text: "Login gesperrt",
          value: "failed_login",
        },
        {
          text: "Ohne spielbare Kategorie",
          value: "without_category",
        },
        {
          text: "Nutzungsbedingungen noch nicht akzeptiert",
          value: "tos_not_accepted",
        },
        {
          text: "Nutzungsbedingungen akzeptiert",
          value: "tos_accepted",
        },
        {
          text: "Admins ohne Berechtigungen",
          value: "powerless_admins",
        },
        {
          text: "Admins",
          value: "admins",
        },
        {
          text: "Administratoren mit TAG-Beschränkung",
          value: "admins_with_tag_rights",
        },
        {
          text: "Deaktivierte Benutzer",
          value: "inactive",
        },
        {
          text: "Wiederherstellbare Benutzer",
          value: "deleted",
        },
        {
          text: "Temporärer Benutzer",
          value: "tmp",
        },
      ]

      if (this.roles) {
        this.roles.forEach((role) => {
          filters.push({
            text: `Rolle: ${role.name}`,
            value: `role_ ${role.id}`,
          })
        })
      }

      return filters
    },
    exportLink() {
      let settings = {...this.pagination}
      delete settings.page
      delete settings.rowsPerPage
      delete settings.totalItems
      settings.search = this.search
      settings.filter = this.filter
      if (this.selectedTags.length) {
        settings.tags = this.selectedTags.join(",")
      }

      let query = Object.keys(settings).map(key => {
        if (!settings[key]) {
          return null
        }
        return `${encodeURIComponent(key)}=${encodeURIComponent(settings[key])}`
      }).filter(v => v !== null).join("&")
      return `/users/export?${query}`
    },
    headers() {
      return [
        {
          text: 'Name',
          value: 'username',
          width: '300px',
        },
        {
          text: 'Benutzergruppen',
          value: 'tags',
          sortable: false,
        },
        {
          text: 'Registrierung',
          value: 'created_at',
          width: '150px',
        },
        {
          text: 'Letzte Aktivität',
          value: 'last_activity',
          width: '150px',
        },
        {
          text: 'Löschdatum',
          value: 'expires_at_combined',
          width: '150px',
        },
        {
          text: 'ID',
          value: 'id',
          width: '90px',
        },
      ]
    },
    pageSelectOptions() {
      if (!this.userCount || !this.pagination.rowsPerPage) {
        return [1]
      }
      const max = Math.ceil(this.userCount / this.pagination.rowsPerPage)
      const options = []
      for (let i = 1; i <= max; i++) {
        options.push(i)
      }
      return options
    },
    searchPlaceholder() {
      if (!this.showPersonalData('users')) {
        return 'Username / ID'
      }
      if (!this.showEmails('users')) {
        return 'Name / ID'
      }
      return 'Name / Mail / ID'
    },
  },
  methods: {
    getCurrentTableConfig() {
      const config = {
        filter: this.filter,
      }
      if (this.search) {
        config.search = this.search
      }
      if (this.selectedTags.length) {
        config.selectedTags = this.selectedTags
      }
      return config
    },
    getBaseRoute() {
      return {
        name: 'users.index',
      }
    },
    getTagItems(items) {
      return [
        {
          label: "Benutzer ohne TAG",
          id: -1,
        },
      ].concat(items)
    },
    openAddTagsModal() {
      this.addTagsModal = true
    },
    openDeleteTagsModal() {
      this.deleteTagsModal = true
    },
    addTags() {
      this.addTagsLoading = true
      axios.post("/backend/api/v1/users/tags", {
        tags: this.selectedAdditionalTags,
        users: this.selected.map(u => u.id),
      }).then(() => {
        this.closeAddTagsModal()
        this.addTagsLoading = false
        this.loadData()
        this.loadWarnings()
      })
    },
    deleteTags() {
      this.deleteTagsLoading = true
      axios.post("/backend/api/v1/users/delete-tags", {
        tags: this.selectedAdditionalTags,
        users: this.selected.map(u => u.id),
      }).then(() => {
        this.closeDeleteTagsModal()
        this.deleteTagsLoading = false
        this.loadData()
        this.loadWarnings()
      })
    },
    setExpiration() {
      this.expirationLoading = true
      axios.post("/backend/api/v1/users/expiration", {
        expires_at: this.expiresAt,
        users: this.selected.map(u => u.id),
      }).then(() => {
        this.closeSetExpiresAtModal()
        this.loadData()
        this.loadWarnings()
      }).catch((e) => {
        let message = e.response.data.message
        if(!message) {
          message = 'Es gab einen Fehler beim Speichern der Daten'
        }
        alert(message)
      }).finally(() => {
        this.expirationLoading = false
      })
    },
    closeAddTagsModal() {
      this.selectedAdditionalTags = []
      this.addTagsModal = false
    },
    closeDeleteTagsModal() {
      this.selectedAdditionalTags = []
      this.deleteTagsModal = false
    },
    closeDeleteUsersModal() {
      this.deleteUsersModal = false
      this.deleteUsersErrors = null
      this.deleteUsersInfo = null
    },
    closeSetExpiresAtModal() {
      this.expiresAt = null
      this.expirationModal = false
    },
    deleteUsers() {
      this.deleteUsersLoading = true
      axios.post("/backend/api/v1/users/delete", {
        users: this.selected.map(u => u.id),
      }).then(() => {
        window.setTimeout(() => {
          this.loadData()
          this.loadWarnings()
          this.closeDeleteUsersModal()
          this.deleteUsersLoading = false
        }, 2000) // queue sometimes needs a bit to delete the users
      })
    },
    goToInvite($event) {
      if (this.readonly) {
        alert('Sie haben nicht die benötigte Berechtigung um Benutzer einzuladen.')
        $event.preventDefault()
        return false
      }
    },
    editUser(userId) {
      this.$router.push({
        name: 'users.edit.general',
        params: {
          userId,
        },
      })
    },
    loadData() {
      if (axiosCancel) {
        axiosCancel()
      }
      this.isLoading = true
      let cancelToken = new axios.CancelToken(c => {
        axiosCancel = c
      })
      axios.get("/backend/api/v1/users", {
        cancelToken,
        params: {
          ...this.pagination,
          filter: this.filter,
          search: this.search,
          tags: this.selectedTags,
        },
      }).then(response => {
        if (response instanceof axios.Cancel) {
          return
        }

        this.selected = []
        this.userCount = response.data.count
        this.users = response.data.users
        this.roles = response.data.roles
        this.isLoading = false
      })
    },
    loadWarnings() {
      axios.get("/backend/api/v1/users/warnings").then(response => {
        this.warnings = response.data.warnings
      })
    },
    openDeleteUsersModal() {
      this.deleteUsersModal = true
      axios.post("/backend/api/v1/users/deletion-information", {
        users: this.selected.map(u => u.id),
      }).then(response => {
        if (response.data.success) {
          this.deleteUsersInfo = response.data.info
        } else {
          this.deleteUsersErrors = response.data.errors
        }
      })
    },
    reinvite() {
      let message = 'Möchten Sie wirklich das Passwort von ' + this.selected.length + ' Benutzern zurücksetzen und sie neu einladen?'
      if (this.selected.length === 1) {
        message = 'Möchten Sie wirklich das Passwort von einem Benutzer zurücksetzen und die Einladung erneut versenden?'
      }
      if (!confirm(message)) {
        return
      }
      this.actionLoading = true
      axios.post("/backend/api/v1/users/reinvite", {
        users: this.selected.map(u => u.id),
      }).then(() => {
        this.snackbarMessage = 'Die Benutzer wurden neu eingeladen.'
        this.snackbar = true
        this.selected = []
      }).catch(() => {
        alert('Die Benutzer konnten nicht erneut eingeladen werden. Bitte versuchen Sie es später erneut.')
      }).finally(() => {
        this.actionLoading = false
      })
    },
    openExpirationModal() {
      this.expirationModal = true
    },
  },
  components: {
    UserSidebar,
    ModuleIntro,
    TagSelect,
    Warnings,
    AddUsersModal,
    UserTabs,
    VoucherSidebar,
    DatePicker,
  },
}
</script>

<style lang="scss">
body > .v-dialog__content .v-dialog.v-dialog--active {
  height: 90% !important;
}

.modal {
  background: white;
  margin: 0 auto;
  min-height: 100%;
  padding: 20px;
  position: relative;
  width: 650px;

  &.loading * {
    opacity: 0;
  }
}

.ui.segment.menu {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
}

#app .users-table {
  .v-datatable__actions__select {
    max-width: 180px;
  }

  .page-select {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-right: 14px;
    height: 58px; // IE11 fix
    margin-bottom: -6px;
    color: rgba(0, 0, 0, 0.54);

    // IE11 fixes
    .v-select__slot, .v-select__selections {
      height: 32px;
    }

    .v-select {
      flex: 0 1 0;
      margin: 13px 0 13px 34px;
      font-size: 12px;
    }
  }
}

#app .s-actionsButton.v-overflow-btn {
  margin-top: 0;
  min-width: 240px;

  .v-input__control:before {
    display: none;
  }
}

// "select all" input button
#app > div.application--wrap > div > div.v-card.v-sheet.theme--light > div.elevation-1 > div.v-table__overflow > table > thead > tr:nth-child(1) > th:nth-child(1) .v-input--selection-controls__input {
  position: relative;

  &::after {
    background: white;
    border: 2px solid gray;
    border-radius: 5px;
    content: 'Betrifft nur sichtbare Ergebnisse';
    display: block;
    left: 50%;
    opacity: 0;
    padding: 3px 5px;
    pointer-events: none;
    position: absolute;
    top: 25px;
    z-index: 50;
  }

  &:hover::after {
    opacity: 1;
  }
}
</style>
