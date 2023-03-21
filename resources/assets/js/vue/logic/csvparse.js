import Papa from "papaparse"

const SEP_COMMA = ","
const SEP_SEMICOLON = ";"
const SEP_UNKNOWN = ""
let ESCAPE = "\""


/**
 * Identifies the escape character in the csv string.
 * Must be either a single or double quote character.
 */
function identify_escape_char(csv, fallback = "\"") {
  let last = ""
  const type1 = "\""
  const type2 = "'"
  let is_check_sep = false
  let esc_count = 0
  // search for a comma after a valid escape or endline character.
  for (let i = 0; i < csv.length; i++) {
    const c = csv.charAt(i)
    // a valid separator must come after an even number of escapes
    if (is_check_sep && esc_count % 2 === 0
      && (c === "," || c === ";" || c === "\n" || c === "\r")) {
      if (last === type1) return type1
      if (last === type2) return type2
    }
    if (!(c === type1 || c === type2)) continue
    is_check_sep = true
    esc_count++
    last = c
  }
  // guess
  const type1_count = csv.split(type1).length - 1
  const type2_count = csv.split(type2).length - 1
  if (type1_count > type2_count) return type2
  if (type2_count > type1_count) return type1
  return fallback
}


// Returns an array of rows.
function string_to_rows(csv, row_limit = 100) {
  let row = ""
  let esc_count = 0
  let res = []
  for (let i = 0; (i < csv.length && res.length < row_limit); i++) {
    const c = csv.charAt(i)
    if (c === ESCAPE) {
      esc_count++
    }
    // search for row end
    // a newline is only valid row end when the delimiter count is even
    if (c === "\n" && esc_count % 2 === 0) { // \r can be ignored
      if (row !== "") {
        res = [...res, row.trim()]
      }
      esc_count = 0
      row = ""
    }
    // normal char in row
    else {
      row += c
    }
  }
  // handle case that last row does not end with a newline
  if (row !== "") res = [...res, row.trim()]
  return res
}


/**
 * Tries to classify the CSV separator, which can only be concluded accurately
 * when a separator comes after an escape character and the input is vailid.
 *
 * Return a triple => {count-if-not-sure, sep, sure}
 * Examples:          {1, ',', true}   // "a",b,c => clear   - kind 1
 *                    {2, ';', false}  // a;b;c,d => unclear - kind 2
 *                    {2, ',', false}  // a,b,"c" => unclear - kind 2b
 */
function identify_row(row) {
  if (row.trim() === "") {
    return {count: 0, sep: SEP_UNKNOWN, sure: false}
  }
  let esc_count = 0
  let last_char_esc = false
  let contains_esc = row.includes(ESCAPE)
  let count_com = 0 // uncertain, number of commas
  let count_sem = 0 // uncertain, number of semicolons
  let last_char_sec

  const chars = row.split("")
  for (let i = 0; i < chars.length; i++) {
    const c = chars[i]

    // handle escape
    if (c === ESCAPE) {
      esc_count++
      if (esc_count === 2) {
        esc_count = 0
      }
      last_char_esc = true
      continue
    }

    // ignore non-separator characters
    if (!(c === SEP_COMMA || c === SEP_SEMICOLON)) {
      last_char_sec = false
      continue
    }
    // discard separator character inside escaped sequence
    if (esc_count > 0) {
      last_char_esc = false
      continue
    }

    // kind 1 - separator can come after escape character
    if (last_char_esc) {
      last_char_esc = false
      return {count: 1, sep: c, sure: true}
    }
    // uncertain, kind 2 or kind 2b
    c === SEP_COMMA ? count_com++ : count_sem++
  } //  END FOREACH

  if (count_com > 0 && count_com > count_sem) {
    return {count: count_com, sep: SEP_COMMA, sure: false}
  } else if (count_sem > 0 && count_sem > count_com) {
    return {count: count_sem, sep: SEP_SEMICOLON, sure: false}
  } else if (count_com === count_sem) {
    return {count: count_com, sep: SEP_UNKNOWN, sure: false}
  }

  return {count: 0, sep: SEP_UNKNOWN, sure: false}
}


/**
 * Identify the separator of a vailid CSV file with at last one row.
 * The result is accurate if at last one row contains a cell after an
 * escaped cell, e.g.: "foo",bar
 * Otherwise if a min. column count is given, it is used to determine the
 * separator. In case of uncertain matches for comma and semicolon,
 * the separator with the highest count is returned.
 *
 * The algorithm can detect as separator character: , ;
 *                         and as escape character: " '
 *
 */
function identify(csv, min_columns = -1, fallback_sep = SEP_COMMA) {
  ESCAPE = identify_escape_char(csv)
  const rows_count = 100
  const arr = string_to_rows(csv, rows_count).map(v => identify_row(v))

  if (arr.length === 0) return fallback_sep

  let count_clear_com = 0
  let count_clear_sem = 0
  let min_row_count_com = 0
  let min_row_count_sem = 0
  let unclear_rows_com = 0
  let unclear_rows_sem = 0

  arr.forEach((obj) => {
    let {count, sep, sure} = obj
    if (sure) {
      if (sep === SEP_COMMA) {
        count_clear_com++
      } else {
        count_clear_sem++
      }
    } else {
      if (sep === SEP_COMMA) {
        if (min_row_count_com < count) {
          min_row_count_com = count
        }
        unclear_rows_com++
      } else {
        if (min_row_count_sem < count) {
          min_row_count_sem = count
        }
        unclear_rows_sem++
      }
    }
  })
  // when returned from here the result is accurate
  if (count_clear_com > 0 && count_clear_sem === 0) {
    return SEP_COMMA
  } else if (count_clear_sem > 0 && count_clear_com === 0) {
    return SEP_SEMICOLON
  }
  // input can't be 100% acurrately, try our best
  if (count_clear_com > 0 && count_clear_sem > 0) {
    if (count_clear_sem > count_clear_com) {
      return SEP_SEMICOLON
    } else if(count_clear_com > count_clear_sem) {
      return SEP_COMMA
    }
  }
  if (min_columns > 0) {
    if (min_row_count_com >= min_columns && min_row_count_sem === 0) {
      return SEP_COMMA
    } else if (min_row_count_sem >= min_columns && min_row_count_com === 0) {
      return SEP_SEMICOLON
    }
  }
  // first check the row count with a separator,
  // than – if nessessary – compare the amount of separators
  if (unclear_rows_com > 0 && unclear_rows_sem === 0) {
    return SEP_COMMA
  } else if (unclear_rows_sem > 0 && unclear_rows_com === 0) {
    return SEP_SEMICOLON
  } else if (unclear_rows_com > 0 && unclear_rows_sem > 0) {

    if (unclear_rows_com === unclear_rows_sem) {
      // chose the result with fewer separator entries
      if (min_row_count_com < min_row_count_sem) {
        return SEP_COMMA
      } else {
        return SEP_SEMICOLON
      }
    }
    // chose the separator witch is more dominant
    else if (unclear_rows_com > unclear_rows_sem) {
      return SEP_COMMA
    } else {
      return SEP_SEMICOLON
    }
  }
  return fallback_sep
}

/**
 * Parses the given csv string and returns a 2D array of strings.
 *
 * To more accuratly detect the correct CSV separator, min_columns can be set to
 * increaes the chance of finding the correct separator in uncertain situations.
 */
function parse(csv) {
  const escapeChar = identify_escape_char(csv)

  const config = {
    escapeChar,
    delimiter: identify(csv, escapeChar),
    worker: false,
    skipEmptyLines: 'greedy',
  }
  let res = Papa.parse(csv, config)

  const data_len = res.data.length
  // remove last item if it is empty (mistake of papaparse)
  if (data_len > 0 && res.data[data_len - 1].every(s => s === "")) {
    res.data.pop()
  }

  return res
}

export default parse
