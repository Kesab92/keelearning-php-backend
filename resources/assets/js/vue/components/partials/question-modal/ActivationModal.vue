<template>
    <v-dialog
        v-model="dialog"
        width="50%">
        <v-btn
            block
            :color="openModalButtonColor"
            slot="activator"
            :disabled="selectedQuestions.length == 0">
            {{ selectedQuestions.length }}
            <template v-if="selectedQuestions.length === 1">Frage</template>
            <template v-else>Fragen</template>
            {{ openModalButtonName }}
        </v-btn>

        <v-card>
            <v-card-title
                class="headline grey lighten-2"
                primary-title>
                {{ title }}
            </v-card-title>

            <v-card-text>{{ message }}</v-card-text>
            <v-divider></v-divider>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                    color="success"
                    @click="activateQuestions">
                    {{ successButton }}
                </v-btn>
                <v-btn
                    color="error"
                    @click="dialog = false">
                    Abbrechen
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: {
            selectedQuestions: {
                type: Array,
                required: true
            },
            openModalButtonName: {
                type: String,
                required: true
            },
            title: {
                type: String,
                required: true
            },
            message: {
                type: String,
                required: true
            },
            successButton: {
                type: String,
                required: true
            },
            openModalButtonColor: {
                type: String,
                required: false,
                default: 'info'
            }
        },
        data() {
            return {
                dialog: false
            }
        },
        methods: {
            activateQuestions() {
                this.dialog = false
                this.$emit('success', {
                  commitOnlyQuestionsWithoutErrors: true
                })
            }
        }
    }
</script>

<style lang="scss" scoped>
    #app {
        .dialog__container {
            width: 100%;
        }
    }
</style>