export default {
  TEST: {
    MODE_QUESTIONS: 0,
    MODE_CATEGORIES: 1,
    FILTER_VISIBLE: 'visible',
    FILTER_EXPIRED: 'expired',
    FILTER_ARCHIVED: 'archived',
    FILTER_ARCHIVED_EXPIRED: 'archived_expired',
  },
  WEBINARS: {
    ROLE_MODERATOR: 1,
    ROLE_PARTICIPANT: 2,
    ROLE_OBSERVER: 3,
  },
  COURSES: {
    TYPE_APPOINTMENT: 20,
    TYPE_CERTIFICATE: 4,
    TYPE_CHAPTER: 1000,
    TYPE_FORM: 19,
    TYPE_LEARNINGMATERIAL: 1,
    TYPE_QUESTIONS: 1001,
    TYPE_TODOLIST: 21,
    DURATION_TYPES: {
      DYNAMIC: 1,
      FIXED: 0,
    },
    INTERVAL_TYPES: {
      MONTHLY: 1,
      WEEKLY: 0,
    },
    FILTERS:  {
      ALL: 'all',
      ACTIVE: 'active',
      ARCHIVED: 'archived',
      EXPIRED: 'expired',
      INVISIBLE: 'invisible',
      IS_NOT_REPEATING: 'is_not_repeating',
      IS_REPEATING: 'is_repeating',
      VISIBLE: 'visible',
    },
  },
  MEDIALIBRARY: {
    WBT_SUBTYPES: {
      XAPI: 0,
      SCORM: 1,
    },
  },
  QUESTIONS: {
    MAX_LENGTHS: {
      ANSWER: 300,
      INDEX_CARD_ANSWER: 400,
      FEEDBACK: 1023,
      TITLE: 511,
    },
    TYPE_SINGLE_CHOICE: 0,
    TYPE_MULTIPLE_CHOICE: 1,
    TYPE_BOOLEAN: 2,
    TYPE_INDEX_CARD: 3,
    ATTACHMENTS: {
      TYPE_IMAGE: 0,
      TYPE_AUDIO : 1,
      TYPE_YOUTUBE : 2,
      TYPE_AZURE_VIDEO : 3,
    }
  },
  ADVERTISEMENTS: {
    POSITION_LOGIN: 0,
    POSITION_HOME_MIDDLE: 1,
    POSITION_HOME_BOTTOM: 8,
    POSITION_NEWS: 2,
    POSITION_MEDIALIBRARY: 3,
    POSITION_POWERLEARNING: 4,
    POSITION_INDEXCARDS: 5,
    POSITION_QUIZ: 6,
    POSITION_TESTS: 7, // The currently last id is 8, see POSITION_HOME_BOTTOM
  },
  VOUCHERS: {
    INTERVAL_MONTHS: 0,
    INTERVAL_DAYS: 1,
    FILTER_ACTIVE: "active",
    FILTER_ARCHIVED: "archived",
    FILTER_ALL: "all",
  },
  NEWS: {
    FILTER_ACTIVE: "active",
    FILTER_VISIBLE: "visible",
    FILTER_EXPIRED: "expired",
  },
  CONTENT_CATEGORIES: {
    TYPE_KEYWORDS: 'keywords',
    TYPE_COURSES: 'courses',
    TYPE_TAGS: 'tags',
    TYPE_FORMS: 'forms',
  },
  COMMENTS: {
    TYPE_NEWS: 'news',
    TYPE_COURSES: 'courses',
    TYPE_LEARNING_MATERIALS: 'learningmaterials',
    TYPE_COURSE_CONTENT_ATTEMPT: 'course_content_attempt',
    STATUS_NORMAL: 'normal',
    STATUS_UNRESOLVED: 'unresolved',
    STATUS_DELETED: 'deleted',
  },
  COMMENT_REPORTS: {
    STATUS_REPORTED: 0,
    STATUS_PROCESSED_UNJUSTIFIED: 1,
    STATUS_PROCESSED_JUSTIFIED: 2,
    REASON_PHRASES: {
      0: 'Anderer Grund',
      1: 'Beleidigende Inhalte',
      2: 'Unerlaubte Werbung',
      3: 'Verletzung von Persönlichkeitsrechten',
    },
  },
  MORPH_TYPES: {
    TYPE_LEARNINGMATERIAL: 1,
    TYPE_NEWS: 2,
    TYPE_COMPETITION: 3,
    TYPE_CERTIFICATE: 4,
    TYPE_COURSE: 5,
    TYPE_QUESTION: 10,
    TYPE_COURSE_CONTENT_ATTEMPT: 1002,
  },
  MORPH_TYPE_LABELS: {
    1: 'Mediathek',
    2: 'News',
    3: 'Gewinnspiel',
    5: 'Kurs',
    1002: 'Kursinhalt',
  },
  TAGS: {
    FILTERS: {
      FILTER_ALL: {
        value: 'all',
        name: 'Alle',
      },
      FILTER_NONE: {
        value: 'none',
        name: 'Nicht verwendet',
      },
      FILTER_COURSE: {
        value: 'course',
        name: 'In Kurs',
      },
      FILTER_TEST: {
        value: 'test',
        name: 'In Test',
      },
      FILTER_LEARNINGMATERIAL: {
        value: 'learningmaterial',
        name: 'In Mediathek',
      },
      FILTER_QUIZ: {
        value: 'quiz',
        name: 'In Quiz-Battle',
      },
      FILTER_POWERLEARNING: {
        value: 'question',
        name: 'In In Powerlearning',
      },
      FILTER_INDEX_CARD: {
        value: 'index_card',
        name: 'In Karteikarte',
      },
      FILTER_VOUCHER: {
        value: 'voucher',
        name: 'In Voucher',
      },
      FILTER_ADVERTISEMENT: {
        value: 'advertisement',
        name: 'In Bannern',
      },
      FILTER_NEWS: {
        value: 'news',
        name: 'In News',
      },
      FILTER_WEBINAR: {
        value: 'webinar',
        name: 'In Webinare',
      },
      FILTER_PAGE: {
        value: 'page',
        name: 'In Seite',
      },
    },
  },
  REPORTINGS: {
    TYPE_QUIZ: 1,
    TYPE_USERS: 2,
    INTERVALS: {
      INTERVAL_1W: {
        text: 'Wöchentlich',
        value: '1w',
      },
      INTERVAL_2W: {
        text: 'Zwei-Wöchentlich',
        value: '2w',
      },
      INTERVAL_1M: {
        text: 'Monatlich',
        value: '1m',
      },
      INTERVAL_3M: {
        text: 'Vierteljährlich',
        value: '3m',
      },
      INTERVAL_6M: {
        text: 'Halbjährlich',
        value: '6m',
      },
      INTERVAL_1Y: {
        text: 'Jährlich',
        value: '1y',
      },
    }
  },
  REMINDERS: {
    DEFAULT_COURSE_REMINDER_DAYS: [30, 14, 3],
    TYPE_USER_TEST_NOTIFICATION: 0,
    TYPE_TEST_RESULTS: 1,
    TYPE_USER_COURSE_NOTIFICATION: 2,
    TYPE_ADMIN_COURSE_NOTIFICATION: 3,
  },
  APPOINTMENTS: {
    TYPE_ONLINE: 1,
    TYPE_IN_PERSON: 2,
    REMINDER_TIME_UNIT_MINUTES: 1,
    REMINDER_TIME_UNIT_HOURS: 2,
    REMINDER_TIME_UNIT_DAYS: 3,
    FILTER_ACTIVE: "active",
    FILTER_EXPIRED: "expired",
    FILTER_WITHOUT_PARTICIPANTS: "without_participants",
    FILTER_ACTIVE_WITHOUT_PARTICIPANTS: "active_without_participants",
  },
  SCHEDULE_CRON_JOBS: {
    REPETITION_COURSE: 6
  },
  SUBTITLES: {
    LANGUAGES: [
      {
        text: 'Keine',
        value: null,
      },
      {
        text: 'Deutsch',
        value: 'de-DE',
      },
      {
        text: 'Englisch',
        value: 'en-US',
      },
      {
        text: 'Französisch',
        value: 'fr-FR',
      },
      {
        text: 'Polnisch',
        value: 'pl-PL',
      },
      {
        text: 'Italienisch',
        value: 'it-IT',
      },
      {
        text: 'Chinesisch',
        value: 'zh-CN',
      },
      {
        text: 'Russisch',
        value: 'ru-RU',
      },
      {
        text: 'Türkisch',
        value: 'tr-TR',
      },
      {
        text: 'Spanisch',
        value: 'es-ES',
      },
      {
        text: 'Japanisch',
        value: 'ja-JP',
      },
      {
        text: 'Niederländisch',
        value: 'nl-NL',
      },
    ],
  },
  PUBLISHED_AT_TYPES: {
    IMMEDIATELY: 'immediately',
    PLANNED: 'planned'
  },
  FORMS: {
    TYPE_TEXTAREA: 1,
    TYPE_RATING: 2,
    TYPE_HEADER: 3,
    TYPE_SEPARATOR: 4,
    FILTERS: {
      ACTIVE: 'active',
      ARCHIVED: 'archived',
    },
  },
}
