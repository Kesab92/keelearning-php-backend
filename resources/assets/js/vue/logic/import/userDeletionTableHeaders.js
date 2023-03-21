import Helpers from '../helpers.js'

export default {
  mail: {
    title: "*E-Mail",
    required: true,
    warning(emails) {
      let firstInvalidEmail = Helpers.getFirstInvalidMail(emails)
      if (firstInvalidEmail) {
        return "Manche E-Mail Adressen scheinen ungültig zu sein (z.B. \"" + firstInvalidEmail + "\"). Ist die Spalte \"E-Mail\" richtig zugeordnet?"
      }
      let firstDuplicateEmail = Helpers.getFirstCaseInsensitiveDuplicate(emails)
      if (firstDuplicateEmail) {
        return "Es gibt doppelte Mail-Adressen (z.B. \"" + firstDuplicateEmail + "\")."
      }
      return false
    },
  },
}
