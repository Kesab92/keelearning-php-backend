export default [
  {
    title: 'Benutzerverwaltung',
    hints: [
      '* Berücksichtigt TAG-Beschränkungen',
      '* Benutzernamen sind sichtbar',
    ],
    rights: [
      {
        title: 'Bearbeiten',
        type: 'users-edit',
      },
      {
        title: 'Ansehen',
        type: 'users-view',
        impliedBy: 'users-edit',
      },
      {
        title: 'Benutzer exportieren',
        type: 'users-export',
        requiresOneOf: ['users-view'],
      },
      {
        title: 'Statistik',
        type: 'users-stats',
      },
      {
        title: 'Personenbezogene Daten einsehen',
        type: 'users-personaldata',
        preventedBySetting: 'hide_personal_data',
        requiresOneOf: [
          'users-edit',
          'users-view',
          'users-stats',
        ],
      },
      {
        title: 'E-Mail-Adressen einsehen',
        type: 'users-showemails',
        requiresOneOf: ['users-personaldata'],
        preventedBySetting: 'hide_emails_backend',
      },
    ],
  },
  {
    title: 'Kategorien',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'categories-edit',
      },
    ],
  },
  {
    title: 'Lernfragen-Pool',
    module: 'module_questions',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'questions-edit',
      },
      {
        title: 'Statistik (Quiz & Powerlearning)',
        type: 'questions-stats',
      },
    ],
  },
  {
    title: 'Vorgeschlagene Fragen',
    module: 'module_suggested_questions',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'suggestedquestions-edit',
      },
      {
        title: 'Personenbezogene Daten einsehen',
        type: 'suggestedquestions-personaldata',
        preventedBySetting: 'hide_personal_data',
        requiresOneOf: ['suggestedquestions-edit'],
      },
    ],
  },
  {
    title: 'Mediathek',
    module: 'module_learningmaterials',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'learningmaterials-edit',
      },
      {
        title: 'WBT-Statistik',
        type: 'learningmaterials-stats',
      },
      {
        title: 'Personenbezogene Daten einsehen',
        type: 'learningmaterials-personaldata',
        requiresOneOf: ['learningmaterials-stats'],
        preventedBySetting: 'hide_personal_data',
      },
    ],
  },
  {
    title: 'Voucher',
    module: 'module_vouchers',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'vouchers-edit',
      },
      {
        title: 'Personenbezogene Daten einsehen',
        type: 'vouchers-personaldata',
        requiresOneOf: ['vouchers-edit'],
        preventedBySetting: 'hide_personal_data',
      },
      {
        title: 'E-Mail-Adressen einsehen',
        type: 'vouchers-showemails',
        requiresOneOf: ['vouchers-personaldata'],
        preventedBySetting: 'hide_emails_backend',
      },
    ],
  },
  {
    title: 'Kurse',
    hints: ['* Berücksichtigt TAG-Beschränkungen'],
    module: 'module_courses',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'courses-edit',
        hint: 'Benutzernamen von verknüpften Usern sind sichtbar',
      },
      {
        title: 'Ansehen',
        type: 'courses-view',
        impliedBy: 'courses-edit',
      },
      {
        title: 'Statistik',
        type: 'courses-stats',
        requiresOneOf: [
          'courses-edit',
          'courses-view',
        ],
      },
      {
        title: 'Personenbezogene Daten einsehen',
        type: 'courses-personaldata',
        requiresOneOf: ['courses-stats'],
        preventedBySetting: 'hide_personal_data',
      },
      {
        title: 'E-Mail-Adressen einsehen',
        type: 'courses-showemails',
        requiresOneOf: ['courses-personaldata'],
        preventedBySetting: 'hide_emails_backend',
      },
    ],
  },
  {
    title: 'Formulare',
    hints: ['* Berücksichtigt TAG-Beschränkungen'],
    module: 'module_forms',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'forms-edit',
      },
      {
        title: 'Statistik',
        type: 'forms-stats',
        requiresOneOf: [
          'forms-edit',
        ],
      },
    ],
  },
  {
    title: 'Tests',
    hints: ['* Berücksichtigt TAG-Beschränkungen'],
    module: 'module_tests',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'tests-edit',
      },
      {
        title: 'Ansehen',
        type: 'tests-view',
        impliedBy: 'tests-edit',
      },
      {
        title: 'Statistik',
        type: 'tests-stats',
        requiresOneOf: ['tests-view'],
      },
      {
        title: 'Personenbezogene Daten einsehen',
        type: 'tests-personaldata',
        requiresOneOf: ['tests-stats'],
        preventedBySetting: 'hide_personal_data',
      },
      {
        title: 'E-Mail-Adressen einsehen',
        type: 'tests-showemails',
        requiresOneOf: ['tests-personaldata'],
        preventedBySetting: 'hide_emails_backend',
      },
    ],
  },
  {
    title: 'News',
    hints: ['* Berücksichtigt TAG-Beschränkungen'],
    module: 'module_news',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'news-edit',
      },
    ],
  },
  {
    title: 'Kommentare',
    module: 'module_comments',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'comments-personaldata',
        requiresOneOf: [
          'courses-view',
          'learningmaterials-edit',
          'news-edit',
        ],
        hint: 'Benutzernamen sind sichtbar',
      },
    ],
  },
  {
    title: 'Webinare',
    module: 'module_webinars',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'webinars-personaldata',
        hint: 'Benutzernamen sind bei der Moderator-Auswahl sichtbar',
      },
      {
        title: 'E-Mail-Adressen einsehen (nur interne Benutzer)',
        type: 'webinars-showemails',
        requiresOneOf: ['webinars-personaldata'],
        preventedBySetting: 'hide_emails_backend',
      },
    ],
  },
  {
    title: 'Gewinnspiele',
    hints: ['* Berücksichtigt TAG-Beschränkungen'],
    module: 'module_competitions',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'competitions-edit',
      },
      {
        title: 'Personenbezogene Daten einsehen',
        type: 'competitions-personaldata',
        requiresOneOf: ['competitions-edit'],
        preventedBySetting: 'hide_personal_data',
      },
      {
        title: 'E-Mail-Adressen einsehen',
        type: 'competitions-showemails',
        requiresOneOf: ['competitions-personaldata'],
        preventedBySetting: 'hide_emails_backend',
      },
    ],
  },
  {
    title: 'Quiz-Teams',
    module: 'module_quiz',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'quizteams-personaldata',
        hint: 'Benutzernamen sind sichtbar',
      },
      {
        title: 'E-Mail-Adressen einsehen',
        type: 'quizteams-showemails',
        requiresOneOf: ['quizteams-personaldata'],
        preventedBySetting: 'hide_emails_backend',
      },
    ],
  },
  {
    title: 'Karteikarten (veraltet)',
    module: 'module_index_cards',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'indexcards-edit',
      },
    ],
  },
  {
    title: 'TAGs',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'tags-edit',
      },
    ],
  },
  {
    title: 'Dashboard',
    rights: [
      {
        title: 'Benutzerbezogene Aktivitäten einsehen',
        hint: 'Benutzernamen sind sichtbar',
        type: 'dashboard-userdata',
      },
      {
        title: 'Personenbezogene Daten einsehen',
        preventedBySetting: 'hide_personal_data',
        type: 'dashboard-personaldata',
      },
    ],
  },
  {
    title: 'Einstellungen',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'settings-edit',
      },
      {
        title: 'Statistik - Inhaltsaufrufe',
        type: 'settings-viewcounts',
      },
      {
        title: 'Statistik - App-Bewertungen',
        type: 'settings-ratings',
      },
    ],
  },
  {
    title: 'Banner (Anzeigen)',
    module: 'module_advertisements',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'advertisements-edit',
      },
    ],
  },
  {
    title: 'Schlagwörter',
    module: 'module_keywords',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'keywords-edit',
      },
    ],
  },
  {
    title: 'Termine',
    hints: ['* Berücksichtigt TAG-Beschränkungen'],
    module: 'module_appointments',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'appointments-edit',
      },
      {
        title: 'Ansehen',
        type: 'appointments-view',
        impliedBy: 'appointments-edit',
      },
    ],
  },
  {
    title: 'Statische Seiten',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'pages-edit',
      },
    ],
  },
  {
    title: 'E-Mail-Vorlagen',
    rights: [
      {
        title: 'Bearbeiten',
        type: 'mails-edit',
      },
    ],
  },
]
