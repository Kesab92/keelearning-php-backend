import {addDays, addWeeks, addMonths, isPast, isFuture} from "date-fns"
import constants from "./constants"

export default {
  copyToClipboard(text) {
    let textareaElement = document.createElement('textarea')
    textareaElement.value = text
    textareaElement.style.position = 'absolute'
    textareaElement.style.left = '-1000px'
    document.body.appendChild(textareaElement)
    textareaElement.select()
    textareaElement.setSelectionRange(0, text.length)
    document.execCommand('copy')
    document.body.removeChild(textareaElement)
  },
  getFirstInvalidMail(mails) {
    const emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/
    return mails.find(email => {
        return !emailRegex.test(String(email).toLowerCase())
      })
  },
  getFirstCaseInsensitiveDuplicate(entries) {
    entries = entries.map(entry => String(entry).toLowerCase())
    return entries.find((entry, index) => entries.indexOf(entry) !== index)
  },
  reorder(array, sourceIndex, destinationIndex) {
    const smallerIndex = Math.min(sourceIndex, destinationIndex)
    const largerIndex = Math.max(sourceIndex, destinationIndex)

    return [
      ...array.slice(0, smallerIndex),
      ...(sourceIndex < destinationIndex
        ? array.slice(smallerIndex + 1, largerIndex + 1)
        : []),
      array[sourceIndex],
      ...(sourceIndex > destinationIndex
        ? array.slice(smallerIndex, largerIndex)
        : []),
      ...array.slice(largerIndex + 1),
    ]
  },
  getYouTubeId(url) {
    if(!url) {
      return null
    }
    const ytRegex = /https?:\/\/(?:[0-9A-Z-]+\.)?(?:youtu\.be\/|youtube(?:-nocookie)?\.com\S*[^\w\s-])([\w-]{11})(?=[^\w-]|$)(?![?=&+%\w.-]*(?:['"][^<>]*>|<\/a>))[?=&+%\w.-]*/ig
    return url.replace(ytRegex, '$1')
  },
  isInFrame() {
    // checks if we're inside an iFrame (legacy backend inside relaunched backend)
    return window.location !== window.parent.location
  },
  isYouTubeURL(url) {
    try {
      const linkURL = new URL(url)
      const youtubeHostnames = [
        'www.youtube.com',
        'm.youtube.com',
        'youtube.com',
        'youtu.be',
      ]
      return youtubeHostnames.includes(linkURL.hostname)
    } catch(e) {
      return false
    }
  },
  getYouTubeURL(link) {
    if(!link) {
      return null
    }
    if(!this.isYouTubeURL(link)) {
      return null
    }
    const ytid = this.getYouTubeId(link)
    if(!ytid) {
      return null
    }
    return `https://www.youtube-nocookie.com/embed/${ytid}?modestbranding=1&rel=0`
  },
  getNestedValue(obj, path, fallback) {
    const last = path.length - 1

    if (last < 0) return obj === undefined ? fallback : obj

    for (let i = 0; i < last; i++) {
      if (obj == null) {
        return fallback
      }
      obj = obj[path[i]]
    }

    if (obj == null) return fallback

    return obj[path[last]] === undefined ? fallback : obj[path[last]]
  },
  getObjectValueByPath (obj, path, fallback) {
    // credit: http://stackoverflow.com/questions/6491463/accessing-nested-javascript-objects-with-string-key#comment55278413_6491621
    if (!path || path.constructor !== String) return fallback
    path = path.replace(/\[(\w+)\]/g, '.$1') // convert indexes to properties
    path = path.replace(/^\./, '') // strip a leading dot
    return this.getNestedValue(obj, path.split('.'), fallback)
  },
  ucfirst(string) {
    if(!string || typeof string !== 'string') {
      return string
    }
    return string[0].toLocaleUpperCase() + string.slice(1)
  },
  getLearningmaterialFileType(data) {
    if(data.link) {
      if(this.isYouTubeURL(data.link)) {
        return 'youtube'
      } else {
        return 'link'
      }
    }
    if(!data.file_type) {
      return null
    }
    if(data.file_type === 'wbt') {
      return 'wbt'
    } else if (['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'].includes(data.file_type)) {
      return 'image'
    } else if (['audio/mpeg', 'audio/mp3'].includes(data.file_type)) {
      return 'audio'
    } else if (data.file_type === 'azure_video') {
      return 'azure_video'
    } else {
      return 'misc'
    }
  },
  getImageDimensions(file) {
    return new Promise((resolve, reject) => {
      let url = URL.createObjectURL(file)
      let img = new Image

      img.onload = function() {
        resolve({
          width: img.width,
          height: img.height,
        })
        URL.revokeObjectURL(img.src)
      }

      img.src = url
    })

  },
  hasBrokenTags(voucher, tagGroups) {
    let selectedTags = {}
    for (let i = 0; i < voucher.selectedTags.length; i++) {
      let tagId = voucher.selectedTags[i]
      for (let j = 0; j < tagGroups.length; j++) {
        if (tagGroups[j].tags.findIndex(t => t.id == tagId) != -1) {
          if (!tagGroups[j].can_have_duplicates && selectedTags[tagGroups[j].id]) {
            // more than one tag from same group was previously selected?
            return true
          }
          if (tagGroups[j].signup_selectable) {
            // tag from a signup-selectable group?
            return true
          }
          selectedTags[tagGroups[j].id] = tagId
          break
        }
      }
    }
    return false
  },
  nextRepetitionCourseDate(course) {
    if(!course.is_repeating || !course.available_from) {
      return null
    }

    if(![constants.COURSES.INTERVAL_TYPES.WEEKLY, constants.COURSES.INTERVAL_TYPES.MONTHLY].includes(course.repetition_interval_type)) {
      return null
    }

    if(Number(course.repetition_interval) < 1) {
      return null
    }

    let repetitionDate  = new Date(course.available_from)

    if(isFuture(repetitionDate)) {
      return repetitionDate
    }

    if (course.latestRepeatedCourseCreatedAt) {
      const latestRepeatedCourseCreatedAt = new Date(course.latestRepeatedCourseCreatedAt)

      if (latestRepeatedCourseCreatedAt.getTime() > repetitionDate.getTime()) {
        repetitionDate = latestRepeatedCourseCreatedAt
        repetitionDate.setHours(0, 0, 0, 0)

        switch(course.repetition_interval_type) {
          case constants.COURSES.INTERVAL_TYPES.WEEKLY:
            repetitionDate = addWeeks(repetitionDate, course.repetition_interval)
            break
          case constants.COURSES.INTERVAL_TYPES.MONTHLY:
            repetitionDate = addMonths(repetitionDate, course.repetition_interval)
            break
        }
      }
    }

    if(isPast(repetitionDate)) {
      const now = new Date()
      if (now.getHours() < constants.SCHEDULE_CRON_JOBS.REPETITION_COURSE) {
        return now
      }
      return addDays(now, 1)
    }

    return repetitionDate
  },
  humanReadableQuestionsDifficulty(difficulty) {
    if(difficulty === null) {
      return '?'
    }
    return Math.round((1 - (Number(difficulty) + 1) / 2) * 100)
  },
}
