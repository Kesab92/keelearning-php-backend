<template>
  <div>
    <details-sidebar-toolbar>
      <template
        v-if="voucher"
        v-slot:default>
        <v-btn
          :loading="isSaving"
          color="primary"
          :disabled="disabledSaving"
          @click="$emit('save')"
        >
          Speichern
        </v-btn>

        <v-spacer/>

        <v-btn
          :disabled="!enableDownloadButton"
          outline
          target="_blank"
          :href="'/vouchers/' + voucher.id + '/codes'"
        >
          <v-progress-circular
            v-if="!enableDownloadButton"
            :size="20"
            :width="2"
            indeterminate
          />
          <template v-else>
            Codes herunterladen
          </template>
        </v-btn>

        <v-btn
          v-if="voucher.archived === 0"
          :loading="isSaving"
          outline
          @click="archive"
        >
          Archivieren
        </v-btn>

        <v-btn
          v-if="voucher.archived === 1"
          :loading="isSaving"
          outline
          @click="unarchive"
        >
          Dearchivieren
        </v-btn>

        <v-btn
          :loading="isSaving"
          color="red"
          outline
          @click="remove"
        >
          Löschen
        </v-btn>
      </template>
      <template
        v-slot:alerts>
        <v-alert
          outline
          type="info"
          :value="voucher.archived">
          Dieser Voucher ist archiviert und kann nicht mehr eingelöst werden
        </v-alert>
      </template>
    </details-sidebar-toolbar>
    <DeleteDialog
      v-model="deleteDialogOpen"
      :deletion-url="`/backend/api/v1/vouchers/${voucher.id}`"
      :dependency-url="`/backend/api/v1/vouchers/${voucher.id}/delete-information`"
      :entry-name="voucher.name"
      :redirect-url="afterDeletionRedirectURL"
      type-label="Voucher"
      @deleted="handleVoucherDeleted"/>
  </div>
</template>

<script>
import DeleteDialog from "../partials/global/DeleteDialog"

export default {
  props: [
    'voucher',
    'isSaving',
    'disabledSaving',
  ],
  data() {
    return {
      deleteDialogOpen: false,
      enableDownloadButton: false,
      intervalId: null
    }
  },
  computed: {
    afterDeletionRedirectURL() {
      return "/vouchers#/vouchers"
    },
  },
  watch: {
    voucher() {
      this.enableDownloadButton = false
      clearInterval(this.intervalId)
      this.startLoopForCodesChecking()
    },
  },
  created() {
    this.enableDownloadButton = this.voucher.amount_generated >= this.voucher.amount

    if (!this.enableDownloadButton) {
      this.startLoopForCodesChecking()
    }
  },
  methods: {
    archive() {
      this.$store.dispatch("vouchers/archiveVoucher", this.voucher.id)
    },
    unarchive() {
      this.$store.dispatch("vouchers/unarchiveVoucher", this.voucher.id)
    },
    remove() {
      this.deleteDialogOpen = true
    },
    handleVoucherDeleted() {
      this.$store.commit("vouchers/deleteVoucher", this.voucher.id)
      this.$store.dispatch("vouchers/loadVouchers")
    },
    startLoopForCodesChecking() {
      this.intervalId = window.setInterval(async function () {
        await this.checkIfCodesAreReady()

        if (this.enableDownloadButton) {
          clearInterval(this.intervalId)
          this.intervalId = null
        }
      }.bind(this), 2000);
    },
    checkIfCodesAreReady() {
      axios.get('/backend/api/v1/vouchers/' + this.voucher.id).then((response) => {
        const voucher = response.data.voucher
        this.enableDownloadButton = voucher.amount_generated >= voucher.amount
      })
    },
  },
  components: {
    DeleteDialog,
  }
}
</script>
