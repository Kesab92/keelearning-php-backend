<template>
  <div>
    <div class="headline mb-1">Einstellungen für das Quiz</div>
    <div class="body-1 mb-5">Diese Einstellungen werden in Echtzeit in der App aktiv.</div>

    <v-form v-model="formIsValid">
      <v-layout
        row>
        <v-flex
          class="pr-4"
          md6
          xs12>
          <div class="body-2 mb-3">Allgemeine Quiz Einstellungen</div>
          <template v-for="(setting, settingKey) in quizSettings">
            <SettingSwitch
              v-if="!setting.hidden"
              :key="settingKey"
              :description="setting.description"
              :is-candy="isCandy"
              :label="setting.label"
              :setting="settingKey"
              :settings="profileSettings"
              @updateSetting="updateSetting"
              type="profileSetting"/>
          </template>

          <div
            v-if="superadmin"
            class="body-2 mb-3">Einstellungen für Gewinnspiele</div>
          <SettingSwitch
            :description="setting.description"
            :key="settingKey"
            :is-candy="isCandy"
            :label="setting.label"
            :setting="settingKey"
            :settings="profileSettings"
            @updateSetting="updateSetting"
            type="profileSetting"
            v-for="(setting, settingKey) in competitionSettings"/>
        </v-flex>

        <v-flex
          class="pr-4"
          md6
          xs12>
          <v-text-field
            label="Zeit um eine Runde zu beantworten"
            outline
            :placeholder="profileSettings.quiz_round_answer_time.default + ''"
            v-model="config.quiz_round_answer_time">
            <div
              class="text-field-append"
              slot="append">Stunden
            </div>
          </v-text-field>

          <v-text-field
            label="Zeit um eine Herausforderung anzunehmen"
            outline
            :placeholder="profileSettings.quiz_round_initial_answer_time.default + ''"
            v-model="config.quiz_round_initial_answer_time">
            <div
              class="text-field-append"
              slot="append">Stunden
            </div>
          </v-text-field>

          <v-text-field
            hide-details
            label="Standard Zeit für Antworten"
            outline
            :placeholder="profileSettings.quiz_default_answer_time.default + ''"
            v-model="config.quiz_default_answer_time">
            <div
              class="text-field-append"
              slot="append">Sekunden
            </div>
          </v-text-field>

          <v-btn
            :disabled="isSaving"
            :loading="isSaving"
            @click="updateAppConfigItems"
            class="ml-0 mt-3"
            color="primary">
            Speichern
            <template
              v-if="savingSuccess"
              v-slot:loader>
              <v-icon light>done</v-icon>
            </template>
          </v-btn>
        </v-flex>
      </v-layout>
    </v-form>
  </div>
</template>

<script>
import AreaMixin from "./areaMixin"
import SettingSwitch from "../input-types/SettingSwitch"

export default {
  mixins: [AreaMixin],
  props: ["appSettings", "profileSettings", "profileId", "superadmin", "isCandy"],
  data() {
    return {
      config: {
        quiz_round_answer_time: '',
        quiz_round_initial_answer_time: '',
        quiz_default_answer_time: '',
      },
      quizSettings: {
        "quiz_users_choose_categories": {
          label: "Benutzer wählen die Quiz-Kategorien selbst",
          description: "Ansonsten werden beim Start vom Quiz automatisch zufällige Kategorien ausgewählt",
        },
        "quiz_enable_bots": {
          label: "Bots aktivieren (Computergegner)",
          hidden: !this.superadmin && !this.isCandy
        },
        "bot_game_mails": {
          label: "E-Mails auch für Bot Quizze versenden",
          description: "Dadurch werden auch bei Bot Quizzen Erinnerungen und Ergebnismails versendet",
        },
        "hide_emails_frontend": {
          label: "E-Mail Adressen der Benutzer ausblenden",
          description: "Gilt nur für die Benutzeransicht, unter Adminseite->Optionen können die E-Mail Adressen auf der Adminseite ausgeblendet werden",
        },
        "quiz_hide_player_statistics": {
          label: "Spieler Statistiken ausblenden",
          description: "Gilt nur für die Benutzeransicht. Dort werden dann keine Statistiken anderer Spieler mehr angezeigt",
        },
        "quiz_no_weekend_grace_period": {
          label: "Runden-Countdown läuft auch während des Wochenendes ab",
          description: "Ansonsten zählen Samstage und Sonntage nicht zur Zeit die man hat um in einem Quiz die nächste Runde zu spielen",
        },
      },
      competitionSettings: {
        "competitions_need_realname": {
          label: "Der echte Name ist vor Gewinnspiel-Teilnahme verpflichtend",
        },
        "competitions_need_email": {
          label: "E-Mail Adresse ist vor Gewinnspiel-Teilnahme verpflichtend",
        },
      },
    }
  },
  components: {
    SettingSwitch,
  },
}
</script>

<style lang="scss" scoped>
#app .text-field-append {
  margin-top: 3px;
}
</style>
