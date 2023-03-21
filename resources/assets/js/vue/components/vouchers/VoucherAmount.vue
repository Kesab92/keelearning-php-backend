<template>
  <v-layout
    row
    align-center>
    <v-flex
      shrink
      class="mr-4 py-3">
      <v-btn
        class="mx-0"
        color="primary"
        outline
        @click="showInput = !showInput">
        Neue Code Anzahl setzen
      </v-btn>
    </v-flex>
    <v-form ref="form" lazy-validation>
      <v-flex grow>
        <v-layout
          v-if="showInput"
          align-center
          row>
          <v-flex shrink>
            <v-text-field
              type="number"
              v-model="voucherData.amount"
              :min="min"
              :rules="amountRules"
              :label="voucher.type ? 'Anzahl der Einlösbarkeit' : 'Anzahl an Voucher Codes'"
              required
            />
          </v-flex>
          <v-flex shrink>
            <v-btn
              :disabled="isLoading"
              :loading="isLoading"
              @click.native="save">
              <template v-if="voucher.type == 1">
                Anzahl anpassen
              </template>
              <template v-else>
                Codes erzeugen
              </template>
            </v-btn>
          </v-flex>
        </v-layout>
      </v-flex>
      <div v-if="message">
        {{ message }}
      </div>
    </v-form>
  </v-layout>
</template>

<script>
export default {
  props: {
    voucher: {
      type: Object,
      required: true
    }
  },
  data() {
    return{
      showInput: false,
      isLoading: false,
      amountRules: [
        v => !!v || 'Der Voucher muss mindestens 1 Code enthalten.'
      ],
      voucherData: null,
      message: null,
      min: 0
    }
  },
  created() {
    this.voucherData = JSON.parse(JSON.stringify(this.voucher))
    this.voucherData.amount += 1
    this.min = this.voucher.amount + 1
  },
  methods: {
    save() {
      this.isLoading = true
      axios.post('/backend/api/v1/vouchers/' + this.voucher.id + '/produce', { amount: this.voucherData.amount }).then(response => {
        if (response.data.success) {
          this.message = 'Die Voucher Menge wurde erfolgreich geändert.'
          this.$store.dispatch('vouchers/loadVouchers')
          this.$store.dispatch('vouchers/loadVoucher', {voucherId: this.voucher.id})
        } else {
          this.message = response.data.error
        }
        this.isLoading = false
      })
    }
  }
}
</script>
