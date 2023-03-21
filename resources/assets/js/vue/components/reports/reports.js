export default{
  users: [
    {
      title: 'Basisinformationen',
      settings: [
        {
          title: 'Benutzer-ID',
          type: 'user_id',
          default: true,
        },
        {
          title: 'Benutzername',
          type: 'username',
          preventedBySetting: 'hide_personal_data',
          showPersonalData: 'users',
          default: true,
        },
        {
          title: 'Vor- und Nachname',
          type: 'user_names',
          preventedBySetting: 'hide_personal_data',
          showPersonalData: 'users',
          default: true,
        },
        {
          title: 'E-Mail-Adresse',
          type: 'email',
          showEmails: 'users',
          default: true,
        },
        {
          title: 'Metafelder',
          type: 'metafields',
          default: true,
        },
        {
          title: 'TAGs',
          type: 'tags',
          default: true,
        },
        {
          title: 'Spalte pro TAG-Gruppe',
          type: 'tag_groups',
          default: true,
        },
        {
          title: 'Spalte pro TAG',
          type: 'column_per_tag',
          default: true,
        },
        {
          title: 'Zuletzt online',
          type: 'last_online',
          default: true,
        },
        {
          title: 'Eingelöste Voucher',
          type: 'redeemed_vouchers',
          module: 'module_vouchers',
          necessaryRights: [
            'vouchers-edit',
          ],
          default: true,
        },
      ]
    },
    {
      title: 'Prüfungs-Statistiken',
      settings: [
        {
          title: 'Kurse',
          type: 'courses',
          module: 'module_courses',
          necessaryRights: [
            'courses-stats',
          ],
          default: true,
        },
        {
          title: 'Tests',
          type: 'tests',
          module: 'module_tests',
          necessaryRights: [
            'tests-stats',
          ],
          default: true,
        },
        {
          title: 'Dauer',
          type: 'total_duration',
          requiresOneOf: [
            'courses',
            'tests',
          ],
          default: false,
        },
        {
          title: 'Abschlussdatum',
          type: 'finished_at',
          requiresOneOf: [
            'courses',
            'tests',
          ],
          default: false,
        },
      ]
    },
    {
      title: 'Lern-Statistiken',
      settings: [
        {
          title: 'Quiz-Battle',
          type: 'quiz_battle',
          module: 'module_quiz',
          default: false,
        },
        {
          title: 'Spiele gesamt',
          type: 'total_games',
          default: false,
          requiresOneOf: ['quiz_battle'],
        },
        {
          title: 'Spiele gegen Menschen',
          type: 'human_games',
          default: false,
          requiresOneOf: ['quiz_battle'],
        },
        {
          title: 'Spiele gewonnen gegen Menschen',
          type: 'human_wins',
          default: false,
          requiresOneOf: ['quiz_battle'],
        },
        {
          title: 'Letztes Spiel',
          type: 'last_game',
          default: false,
          requiresOneOf: ['quiz_battle'],
        },
        {
          title: 'Powerlearning',
          type: 'powerlearning',
          module: 'module_powerlearning',
          default: false,
        },
      ],
    },
  ],
}
