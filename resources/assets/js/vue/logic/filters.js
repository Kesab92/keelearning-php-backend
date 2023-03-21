import Vue from 'vue'
import { format, parse, parseISO } from 'date-fns'

const UNICODE_FLAG_OFFSET = 127397

Vue.filter('dateTime', (date) => {
  if (!date) {
    return ''
  }
  // if date is the Date's object, doesn't need parse()
  if(date instanceof Date) {
    return format(date, 'dd.MM.yyyy HH:mm')
  }
  try {
    return format(parse(date, 'yyyy-MM-dd HH:mm:ss', new Date()), 'dd.MM.yyyy HH:mm')
  } catch {
    // probably a raw model timestamp
    return format(parseISO(date), 'dd.MM.yyyy HH:mm')
  }
})

Vue.filter('date', (date) => {
  if (!date) {
    return ''
  }
  // if date is the Date's object, doesn't need parse()
  if(date instanceof Date) {
    return format(date, 'dd.MM.yyyy')
  }
  try {
    return format(parse(date, 'yyyy-MM-dd HH:mm:ss', new Date()), 'dd.MM.yyyy')
  } catch {
    // probably a raw model timestamp
    return format(parseISO(date), 'dd.MM.yyyy')
  }
})

Vue.filter('time', (date) => {
  if (!date) {
    return ''
  }
  // if date is the Date's object, doesn't need parse()
  if(date instanceof Date) {
    return format(date, 'HH:mm')
  }
  try {
    return format(parse(date, 'yyyy-MM-dd HH:mm:ss', new Date()), 'HH:mm')
  } catch {
    // probably a raw model timestamp
    return format(parseISO(date), 'HH:mm')
  }
})

Vue.filter('typeFilter', (value) => {
  switch (value) {
    case 0:
      return 'Ein Code pro Benutzer'
    case 1:
      return 'Ein Code für mehrere Benutzer'
    default:
      return 'Unbekannt'
  }
})

Vue.filter('decimals', (value) => {
  if (value) {
    return parseFloat(value).toFixed(2)
  } else {
    return parseFloat('0').toFixed(2)
  }
})

Vue.filter('emojiflag', (isoCode) => {
  if (!isoCode) {
    return '❓'
  }
  if (isoCode === '??') {
    return '👽'
  }
  return String.fromCodePoint(...[...isoCode.toUpperCase()].map(c => c.charCodeAt() + UNICODE_FLAG_OFFSET))
})

Vue.filter('fileSizeKB', (kb) => {
  if (kb >= 1024) {
    return new Intl.NumberFormat('de-DE', { maximumFractionDigits: 1 }).format(kb / 1024) + 'MB'
  }

  return kb + 'KB'
})

Vue.filter('truncate', (text, length, clamp) => {
  clamp = clamp || '...';
  return text.length > length ? text.slice(0, length) + clamp : text
})

Vue.filter('stripHtml', (input) => {
  let div = document.createElement('div')
  div.innerHTML = input
  return div.innerText
})

Vue.filter('numberFormat', (input, decimalDigits = 0) => {
  const formatter = new Intl.NumberFormat('de-DE', {
    minimumFractionDigits: decimalDigits,
    maximumFractionDigits: decimalDigits,
  })
  return formatter.format(input || 0)
})

