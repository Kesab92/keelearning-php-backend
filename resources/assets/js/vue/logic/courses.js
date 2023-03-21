const constants = {
  // For type definitions See MorphTypes.php
  TYPE_CHAPTER: 1000,
  TYPE_LEARNINGMATERIAL: 1,
  TYPE_QUESTIONS: 1001,
  TYPE_CERTIFICATE: 4,
  TYPE_FORM: 19,
  TYPE_APPOINTMENT: 20,
  TYPE_TODOLIST: 21,
}

constants.contentTypeLabels = {
  [constants.TYPE_CHAPTER]: 'Kapitel',
  [constants.TYPE_LEARNINGMATERIAL]: 'Datei',
  [constants.TYPE_FORM]: 'Formular',
  [constants.TYPE_QUESTIONS]: 'Lernfragen',
  [constants.TYPE_APPOINTMENT]: 'Termin',
  [constants.TYPE_CERTIFICATE]: 'Zertifikat',
  [constants.TYPE_TODOLIST]: 'Aufgabenliste',
}

export default constants
