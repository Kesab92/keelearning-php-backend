import Helpers from '../helpers.js'

export default {
  firstname: {
    title: "*Vorname",
    required: true,
    warning(firstnames) {
      let firstShortFirstname = firstnames.find(firstname => {
        return firstname.length < 3
      })
      if (firstShortFirstname) {
        return "Manche Vornamen sind sehr kurz (z.B. \"" + firstShortFirstname + "\"). Ist die Spalte \"Vorname\" richtig zugeordnet?"
      }
      let firstLongFirstname = firstnames.find(firstname => {
        return firstname.length >= 30
      })
      if (firstLongFirstname) {
        return "Manche Vornamen sind sehr lang (z.B. \"" + firstLongFirstname + "\"). Ist die Spalte \"Vorname\" richtig zugeordnet?"
      }
      return false
    },
  },
  lastname: {
    title: "Nachname",
    required: false
  },
  mail: {
    title: "E-Mail",
    required: false,
    warning(emails) {
      let firstInvalidEmail = Helpers.getFirstInvalidMail(emails)
      if (firstInvalidEmail) {
        return "Manche E-Mail Adressen scheinen ungültig zu sein (z.B. \"" + firstInvalidEmail + "\"). Ist die Spalte \"E-Mail\" richtig zugeordnet?"
      }
    },
    error(emails) {
        let firstDuplicateEmail = Helpers.getFirstCaseInsensitiveDuplicate(emails)
        if (firstDuplicateEmail) {
          return "Es gibt doppelte Mail-Adressen (z.B. \"" + firstDuplicateEmail + "\")."
        }
        return false
    },
  },
  language: {
    title: "Sprache",
    required: false,
    error(languages, configuration) {
      let availableLanguages = configuration.languages
      let firstInvalidLanguage = languages.find(language => {
        return availableLanguages.indexOf(language) === -1
      })
      if(firstInvalidLanguage === '') {
        return "Bei mindestens einem Benutzer wurde keine Sprache übergeben."
      }
      if (typeof firstInvalidLanguage !== 'undefined')  {
        return "Manche Sprachen sind nicht verfügbar (z.B. \"" + firstInvalidLanguage + "\"). Gültige Sprachen sind: " + availableLanguages.join(", ") +". Ist die Spalte \"Sprache\" richtig zugeordnet?"
      }
      return false
    }
  },
}
