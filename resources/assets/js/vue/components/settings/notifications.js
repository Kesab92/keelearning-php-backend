export default [
  {
    title: 'User',
    notifications: [
      {
        title: 'Wir vermissen dich',
        description: 'Wird an inaktive User 5, 10 und 30 Tage nach dem letzten Quiz-Spiel gesendet',
        editable: true,
        mailTemplate: 'AppReminder',
      },
      {
        title: 'Ihr Account wird bald gelöscht',
        description: 'User mit Löschdatum werden vor dem Löschen informieren (die Anzahl der Tage ist konfigurierbar)',
        editable: true,
        mailTemplate: 'ExpirationReminder',
      },
      {
        title: 'App-Feedback',
        description: 'Betrifft nur Admins. Enthält die App-Bewertung eines Users',
        editable: false,
        mailTemplate: 'AppFeedback',
      },
      {
        title: 'User wird zur App durch einen Admin eingeladen',
        description: 'Wird beim Anlegen eines neuen Users durch den Admin gesendet bzw. durch den User-Import',
        editable: false,
        mailTemplate: 'AppInvitation',
      },
      {
        title: 'Account-Löschaufforderung durch User',
        description: 'Betrifft nur Admins. User bittet darum das ein Account gelöscht wird\n',
        editable: false,
        mailTemplate: 'UserDeletionRequest',
      },
      {
        title: 'Registrierungsbestätigung nach Selbstregistrierung',
        description: 'Wird nach der Selbstregistrierung versandt zur Verifizierung des Users',
        editable: false,
        mailTemplate: 'AuthWelcome',
        requiresOneOf: ['signup_enabled'],
      },
      {
        title: 'Registrierungsbestätigung nach SSO-Selbstregistrierung',
        description: 'Wird versandt, wenn sich ein User ohne Account das erste Mal über Single Sign-on anmeldet',
        editable: false,
        mailTemplate: 'AuthSSOWelcome',
        requiresOneOf: ['enable_sso_registration'],
      },
      {
        title: 'User initiierter Passwort-Reset',
        description: 'E-Mail zum Zurücksetzen des Passworts wird an den User versandt',
        editable: false,
        mailTemplate: 'AuthResetPassword',
      },
      {
        title: 'Admin initiierter Passwort-Reset',
        description: 'E-Mail zum Zurücksetzen des Passworts wird an den User versandt',
        editable: false,
        mailTemplate: 'AuthBackendResetPassword',
      },
      {
        title: 'Bestätigung neuer E-Mail Adresse',
        description: 'Wird versandt, wenn ein User in der App eine neue E-Mail Adresse hinterlegt',
        editable: false,
        mailTemplate: 'EmailChangeConfirmation',
        requiresOneOf: ['allow_email_change'],
      },
      {
        title: 'Direktnachricht an User',
        description: 'Wird versandt, wenn ein Admin einen User über das Userprofil anschreibt',
        editable: false,
        mailTemplate: 'DirectMessage',
      },
    ]
  },
  {
    title: 'Kurse',
    module: 'module_courses',
    notifications: [
      {
        title: 'Kurs bestanden',
        description: 'Informiert User über einen bestandenen Kurs',
        editable: true,
        mailTemplate: 'PassedCourse',
      },
      {
        title: 'Kurs veröffentlicht',
        description: 'Informiert User über die Veröffentlichung eines Kurses',
        editable: true,
        mailTemplate: 'NewCourseNotification',
      },
      {
        title: 'Kurs-Eskalationsmanagement an User',
        description: 'Stufenweise Erinnerung an User über Ablauf eines Kurses',
        editable: true,
        mailTemplate: 'CourseReminder',
      },
      {
        title: 'User erbittet Zugriff auf einen Kurs',
        description: 'Betrifft nur Admins. Ein User erbittet Zugriff auf einen Vorschau-Kurs',
        editable: false,
        mailTemplate: 'CourseAccessRequest',
      },
      {
        title: 'Kurs-Eskalationsmanagement an Admin',
        description: 'Betrifft nur Admins. Stufenweise Erinnerung an Admins über Status eines Kurses',
        editable: false,
        mailTemplate: 'CourseResultReminder',
      },
      {
        title: 'Kurs wird demnächst wiederholt',
        description: 'Betrifft nur Admins. Informiert Kursverantwortliche über anstehende Kurs-Wiederholung',
        editable: false,
        mailTemplate: 'RepetitionCourseReminder',
      },
    ],
  },
  {
    title: 'Quiz-Battle',
    notifications: [
      {
        title: 'Quiz-Battle Herausforderung',
        description: 'Informiert User, dass er zu einem Quiz-Battle herausgefordert wurde',
        editable: true,
        mailTemplate: 'GameInvitation',
        requiresOneOf: ['module_quiz'],
      },
      {
        title: 'Du bist an der Reihe im Quiz-Battle',
        description: 'Informiert User, dass er an der Reihe ist für die nächste Quiz-Runde',
        editable: true,
        mailTemplate: 'GameReminder',
        requiresOneOf: ['module_quiz'],
      },
      {
        title: 'Quiz-Battle gewonnen',
        description: 'Informiert User, dass er ein Quiz-Battle gewonnen hat',
        editable: true,
        mailTemplate: 'GameWonInfo',
        requiresOneOf: ['module_quiz'],
      },
      {
        title: 'Quiz-Battle unentschieden',
        description: 'Informiert User, dass das Quiz-Battle unentschieden ausgegangen ist',
        editable: true,
        mailTemplate: 'GameDrawInfo',
        requiresOneOf: ['module_quiz'],
      },
      {
        title: 'Quiz-Battle verloren',
        description: 'Informiert User, dass er ein Quiz-Battle verloren hat',
        editable: true,
        mailTemplate: 'GameLostInfo',
        requiresOneOf: ['module_quiz'],
      },
      {
        title: 'Quiz-Battle abgebrochen',
        description: 'Informiert User, dass ein aktives Quiz-Battle abgebrochen wurde',
        editable: true,
        mailTemplate: 'GameAbort',
        requiresOneOf: ['module_quiz'],
      },
      {
        title: 'User wurde zu einem Quiz-Team hinzugefügt',
        description: 'Informiert User, dass er zu einem Quiz-Team hinzugefügt wurde',
        editable: true,
        mailTemplate: 'QuizTeamAdd',
        requiresOneOf: [
          'module_quiz',
        ],
      },
      {
        title: 'Frage von einem User vorgeschlagen',
        description: 'Betrifft nur Admins. Informiert Admin über die Einreichung einer neuen Lernfrage durch den User',
        editable: false,
        mailTemplate: 'AppQuestionSuggestion',
        requiresOneOf: ['module_suggested_questions'],
      },
    ]
  },
  {
    title: 'Gewinnspiel',
    module: 'module_competitions',
    notifications: [
      {
        title: 'Einladung zum Gewinnspiel',
        description: 'Informiert den User über die Teilnahme am Gewinnspiel',
        editable: true,
        mailTemplate: 'CompetitionInvitation',
      },
      {
        title: 'Erinnerung, am Gewinnspiel teilzunehmen',
        description: 'Erinnert den User am Gewinnspiel teilzunehmen',
        editable: true,
        mailTemplate: 'CompetitionReminder',
      },
      {
        title: 'Ergebnis des Gewinnspieles',
        description: 'Informiert den User über seinen Gewinnspielplatz',
        editable: true,
        mailTemplate: 'CompetitionResult',
      },
    ]
  },
  {
    title: 'Mediathek',
    module: 'module_learningmaterials',
    notifications: [
      {
        title: 'Mediathek-Inhalt veröffentlicht',
        description: 'Informiert den User über einen neuen Mediathek-Inhalt',
        editable: true,
        mailTemplate: 'LearningMaterialsPublished',
      },
    ]
  },
  {
    title: 'News',
    module: 'module_news',
    notifications: [
      {
        title: 'News veröffentlicht',
        description: 'Informiert den User über eine neue News',
        editable: true,
        mailTemplate: 'NewsPublishedInfo',
      },
    ]
  },
  {
    title: 'Kommentare',
    module: 'module_comments',
    notifications: [
      {
        title: 'Benachrichtigung bei Antwort',
        description: 'Benachrichtigt Kommentar-Autoren über Antworten und neue Beiträge',
        editable: true,
        mailTemplate: 'SubscriptionComment',
      },
      {
        title: 'Autor - Kommentar von Admin für bedenklich erklärt',
        description: 'Informiert den Autor, dass der Kommentar als bedenklich eingestuft wurde',
        editable: true,
        mailTemplate: 'CommentDeletedForAuthor',
      },
      {
        title: 'Melder - Kommentar von Admin für bedenklich erklärt',
        description: 'Informiert den Melder, dass der Kommentar als bedenklich eingestuft wurde',
        editable: true,
        mailTemplate: 'CommentDeletedForReporter',
      },
      {
        title: 'Kommentar unbedenklich',
        description: 'Informiert den Autor, dass sein Kommentar geprüft wurde und unbedenklich ist',
        editable: true,
        mailTemplate: 'CommentNotDeleted',
      },
    ]
  },
  {
    title: 'Termine',
    module: 'module_appointments',
    notifications: [
      {
        title: 'Termin veröffentlicht',
        description: 'Informiert den User über einen neuen Termin',
        editable: true,
        mailTemplate: 'NewAppointment',
      },
      {
        title: 'Terminerinnerung',
        description: 'Erinnert den User vor dem Startdatum an einen Termin',
        editable: true,
        mailTemplate: 'AppointmentReminder',
      },
      {
        title: 'Termin aktualisiert',
        description: 'Informiert den User über eine Terminaktualisierung',
        editable: true,
        mailTemplate: 'AppointmentStartDateWasUpdated',
      },
    ]
  },
  {
    title: 'Webinar',
    module: 'module_webinars',
    notifications: [
      {
        title: 'Webinar-Erinnerung',
        description: 'Informiert den User, dass sein Webinar gleich startet',
        editable: true,
        mailTemplate: 'WebinarReminder',
      },
      {
        title: 'Webinar-Erinnerung für externe Teilnehmer',
        description: 'Informiert einen externen Teilnehmer, dass sein Webinar gleich startet',
        editable: false,
        mailTemplate: 'WebinarReminderExternal',
      },
    ]
  },
  {
    title: 'Test',
    module: 'module_tests',
    notifications: [
      {
        title: 'Test bestanden',
        description: 'Informiert User über einen bestandenen Test',
        editable: true,
        mailTemplate: 'TestPassed',
      },
      {
        title: 'Test-Eskalationsmanagement an User',
        description: 'Stufenweise Erinnerung an User zur Deadline eines Tests',
        editable: true,
        mailTemplate: 'TestReminder',
      },
    ]
  },
  {
    title: 'Sonstiges',
    notifications: [
      {
        title: 'Inhalt gemeldet',
        description: 'Betrifft nur Admins. E-Mail an Admin zu Inhalten, die von Usern gemeldet werden',
        editable: false,
        mailTemplate: 'ItemFeedback',
        requiresOneOf: [
          'module_tests',
          'module_courses',
          'module_learningmaterials',
        ],
      },
    ]
  },
]
