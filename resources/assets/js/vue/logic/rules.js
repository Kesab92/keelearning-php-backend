// validation rules for inputs
export default {
  minChars(charCount) {
    return ((input) => {
      if (input.length >= charCount) {
        return true
      }
      return `Mindestens ${charCount} Zeichen`
    })
  },
  maxChars(charCount) {
    return ((input) => {
      if (input.length <= charCount) {
        return true
      }
      return `Maximal ${charCount} Zeichen`
    })
  },
  noDuplicate(existingValues) {
    return ((input) => {
      const sanitizedExistingValues = existingValues.map((existingValue) => {
        if (typeof existingValue === 'string') {
          return existingValue.toLowerCase()
        }
        return existingValue
      })
      let sanitizedInput = input
      if (typeof sanitizedInput === 'string') {
        sanitizedInput = sanitizedInput.toLowerCase()
      }
      if (!sanitizedExistingValues.includes(sanitizedInput)) {
        return true
      }
      return `Ein Eintrag für "${input}" existiert bereits`
    })
  },
  url(input) {
    if (!input) {
      return true
    }
    if (!/^https?:\/\/.*/.test(input)) {
      return 'Bitte geben Sie eine gültige URL an, beginnend mit http:// oder https://'
    }
    return true
  },
  email(input) {
    if (!input) {
      return true
    }
    if(input.includes('@')) {
      return true
    }
    return 'Bitte geben Sie eine gültige E-Mail an'
  },
  emails(input) {
    const emails = input.split(',')
    let validEmails = true
    emails.forEach((item) => {
      if(!item.includes('@')) {
        validEmails = 'Bitte geben Sie eine oder mehrere durch Kommas getrennte E-Mails an'
        return
      }
    })
    return validEmails
  },
  required(input) {
    if (input !== null && typeof input !== 'undefined' && input.length) {
      return true
    }
    return 'Wird benötigt'
  },
  time(input) {
    if (!input) {
      return true
    }

    const regex = new RegExp(/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/)

    if(regex.test(input)) {
      return true
    }

    return 'Ungültige Zeitangabe'
  },
}
