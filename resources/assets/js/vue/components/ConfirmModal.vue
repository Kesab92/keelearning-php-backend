<template>
    <div class="text-xs-center">
        <v-dialog v-model="dialog" width="500">
            <v-btn slot="activator" :color="buttonColor" dark>
                {{ buttonTitle }}
            </v-btn>

            <v-card>
                <v-card-title class="headline grey lighten-2" primary-title>Bestätigen</v-card-title>

                <v-card-text>
                    {{ message }}
                </v-card-text>

                <v-divider></v-divider>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="success" @click="confirm">Bestätigen</v-btn>
                    <v-btn color="error" @click="dialog = false">Abbrechen</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
    export default {
      props: {
        buttonTitle: {
          type: String,
          required: true
        },
        buttonColor: {
          type: String,
          required: false,
          default: 'primary'
        },
        message: {
          type: String,
          required: true
        }
      },
      data() {
        return {
          dialog: false
        }
      },
      methods: {
        confirm() {
          this.dialog = false
          this.$emit('success')
        }
      }
    }
</script>