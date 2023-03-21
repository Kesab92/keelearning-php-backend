import { read as xlsxRead, utils as xlsxUtils} from 'xlsx'
import parse from './csvparse'

export default {
  /**
   * Parses the given CSV file into a json object
   *
   * @param file
   * @param cb
   */
  parse(file, cb) {
    if(file.type === 'text/csv') {
      this.parseCSV(file, cb)
    } else {
      this.parseXLSX(file, cb)
    }
  },

  parseXLSX(file, cb) {
    var reader = new FileReader()
    reader.onload = function(e) {
      var data = new Uint8Array(e.target.result)
      var workbook = xlsxRead(data, {type: 'array'})
      let rows = xlsxUtils.sheet_to_json(workbook.Sheets[workbook.SheetNames[0]], {
        header: 1,
        blankrows: false,
        defval: '',
      }).map((row) => {
        return row.map((cell) => {
          if (typeof cell === 'string') {
            return cell.replace(/\r\n/g, '\n')
          }
          return cell
        })
      })
      cb({
        data: rows,
        errors: [],
        meta: {},
      })
    }
    reader.readAsArrayBuffer(file)
  },

  parseCSV(file, cb) {
    this.readFile(file, "utf-8", text => {
      if(this.containsWin1252EncodedChars(text)) {
        this.readFile(file, "windows-1252", text => {
          this.sanityChecks(parse(text), cb)
        })
      } else {
        this.sanityChecks(parse(text), cb)
      }
    })
  },

  sanityChecks(data, cb) {
    if(data.errors.length) {
      cb(data)
      return
    }
    let headerLength = data.data[0].length
    let hasLengthError = false
    data.data.forEach((entry, idx)=> {
      if(entry.length !== headerLength && !hasLengthError) {
        data.errors.push({
          message: 'Nicht alle Zeilen haben die gleiche Anzahl an Spalten.',
          row: idx + 1,
        })
        hasLengthError = true
      }
    })
    cb(data)
  },

  /**
   * Checks if the given utf-8 text contains a win1252 char code
   *
   * @param text
   * @returns {boolean}
   */
  containsWin1252EncodedChars(text) {
    let length = text.length
    for(let i = 0; i < length; i++) {
      if(text.charCodeAt(i) === 65533) {
        return true
      }
    }
    return false
  },

  /**
   * Reads the contents of a file as text
   *
   * @param file
   * @param encoding
   * @param cb
   */
  readFile(file, encoding, cb) {
    let reader = new FileReader()

    reader.onload = () => {
      cb(reader.result)
    }

    reader.readAsText(file, encoding)
  },

  /**
   * Tries to generate an array of keys from `availableHeaders` which matches given csv headers
   *
   * @param csvHeaders An array of headers from the csv file
   * @param availableHeaders An object of available headers. This depends on the type of import
   */
  matchHeaders(csvHeaders, availableHeaders) {
    let headers = []
    csvHeaders.forEach(header => {
      let matchedKey = Object.keys(availableHeaders).find(key => {
        let availableHeader = availableHeaders[key]
        if(availableHeader.title === header.trim()) {
          return true
        }
      })
      if(matchedKey && headers.indexOf(matchedKey) === -1) {
        headers.push(matchedKey)
      } else {
        headers.push(null)
      }
    })
    return headers
  }
}
