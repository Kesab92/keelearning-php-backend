import constants from "../constants";

function hasNoIndicators(correctIndicators) {
  return correctIndicators.filter(indicator => {
    let trimmed = (indicator + '').trim()
    if (trimmed.length === 0) {
      return false
    }
    return trimmed.length > 1 || (trimmed !== '0' && trimmed !== '1')
  }).length
}

const image = {
  title: "Bild-URL",
    required: false,
  error(imageUrls) {
    if(!imageUrls.length) {
      return false
    }

    for (const imageUrl of imageUrls) {
      if(imageUrl.length && !imageUrl.startsWith('http://') && !imageUrl.startsWith('https://')) {
        return 'Manche Bild-URLs sind ungültig. URLs müssen mit https:// beginnen.'
      }
    }

    return false
  },
}

const question = {
  title: "*Frage",
  required: true,
  warning(questions) {
    let firstShortQuestion = questions.find(question => {
      return question.length < 5
    })
    if(firstShortQuestion) {
      return 'Manche Fragestellungen sind sehr kurz (z.B. "' + firstShortQuestion + '"). Ist die Spalte richtig zugeordnet?'
    }
    return false
  },
  error(questions) {
    let firstToLongQuestion = questions.find(question => {
      return question.length > constants.QUESTIONS.MAX_LENGTHS.TITLE
    })
    if(firstToLongQuestion) {
      return `This question is too long`
    }
    return false
  },
}

const feedback = {
  title: "Feedback",
  required: false,
}

export default {
  singlechoice: {
    question: question,
    feedback: feedback,
    image: image,
    correct_answer: {
      title: "*Richtige Antwort",
      required: true,
      error(answers) {
        let emptyAnswers = answers.filter(answer => {
          return answer.length === 0
        }).length
        if(emptyAnswers) {
          let error
          if(emptyAnswers === 1) {
            error = 'Eine Frage hat'
          } else {
            error = emptyAnswers + ' Fragen haben'
          }
          return error + ' keine richtige Antwort. Eventuell ist die Spalte falsch zugeordnet oder es fehlen Daten in der Datei.'
        }
        return false
      },
    },
    incorrect_answer1: {
      title: "*Falsche Antwort 1",
      required: true,
      error(answers) {
        let emptyAnswers = answers.filter(answer => {
          return answer.length === 0
        }).length
        if(emptyAnswers) {
          let error
          if(emptyAnswers === 1) {
            error = 'Eine Frage hat'
          } else {
            error = emptyAnswers + ' Fragen haben'
          }
          return error + ' keine falsche Antwort. Eventuell ist die Spalte falsch zugeordnet oder es fehlen Daten in der Datei.'
        }
        return false
      },
    },
    incorrect_answer2: {
      title: "Falsche Antwort 2",
      required: false,
    },
    incorrect_answer3: {
      title: "Falsche Antwort 3",
      required: false,
    },
    incorrect_answer4: {
      title: "Falsche Antwort 4",
      required: false,
    },
  },
  multiplechoice: {
    question: question,
    feedback: feedback,
    image: image,
    answer1: {
      title: "*Antwort 1",
      required: true,
    },
    answer1_correct: {
      title: "*Antwort 1 richtig (falsch=0/richtig=1)",
      required: true,
      error(correctIndicators) {
        let noIndicator = hasNoIndicators(correctIndicators)
        if(noIndicator) {
          let error
          if(noIndicator === 1) {
            error = 'Eine Frage hat bei "Antwort 1"'
          } else {
            error = noIndicator + ' Fragen haben bei "Antwort 1"'
          }
          return error + ' ungültige Angaben ob die Antwort richtig oder falsch ist. Eventuell ist die Spalte falsch zugeordnet oder es stehen ungültige Daten in der Datei.'
        }
        return false
      },
    },
    answer2: {
      title: "*Antwort 2",
      required: true,
    },
    answer2_correct: {
      title: "*Antwort 2 richtig (falsch=0/richtig=1)",
      required: true,
      error(correctIndicators) {
        let noIndicator = hasNoIndicators(correctIndicators)
        if(noIndicator) {
          let error
          if(noIndicator === 1) {
            error = 'Eine Frage hat bei "Antwort 2"'
          } else {
            error = noIndicator + ' Fragen haben bei "Antwort 2"'
          }
          return error + ' ungültige Angaben ob die Antwort richtig oder falsch ist. Eventuell ist die Spalte falsch zugeordnet oder es stehen ungültige Daten in der Datei.'
        }
        return false
      },
    },
    answer3: {
      title: "Antwort 3",
      required: false,
    },
    answer3_correct: {
      title: "Antwort 3 richtig (falsch=0/richtig=1)",
      required: false,
      error(correctIndicators) {
        let noIndicator = hasNoIndicators(correctIndicators)
        if(noIndicator) {
          let error
          if(noIndicator === 1) {
            error = 'Eine Frage hat bei "Antwort 3"'
          } else {
            error = noIndicator + ' Fragen haben bei "Antwort 3"'
          }
          return error + ' ungültige Angaben ob die Antwort richtig oder falsch ist. Eventuell ist die Spalte falsch zugeordnet oder es stehen ungültige Daten in der Datei.'
        }
        return false
      },
    },
    answer4: {
      title: "Antwort 4",
      required: false,
    },
    answer4_correct: {
      title: "Antwort 4 richtig (falsch=0/richtig=1)",
      required: false,
      error(correctIndicators) {
        let noIndicator = hasNoIndicators(correctIndicators)
        if(noIndicator) {
          let error
          if(noIndicator === 1) {
            error = 'Eine Frage hat bei "Antwort 4"'
          } else {
            error = noIndicator + ' Fragen haben bei "Antwort 4"'
          }
          return error + ' ungültige Angaben ob die Antwort richtig oder falsch ist. Eventuell ist die Spalte falsch zugeordnet oder es stehen ungültige Daten in der Datei.'
        }
        return false
      },
    },
    answer5: {
      title: "Antwort 5",
      required: false,
    },
    answer5_correct: {
      title: "Antwort 5 richtig (falsch=0/richtig=1)",
      required: false,
      error(correctIndicators) {
        let noIndicator = hasNoIndicators(correctIndicators)
        if(noIndicator) {
          let error
          if(noIndicator === 1) {
            error = 'Eine Frage hat bei "Antwort 5"'
          } else {
            error = noIndicator + ' Fragen haben bei "Antwort 5"'
          }
          return error + ' ungültige Angaben ob die Antwort richtig oder falsch ist. Eventuell ist die Spalte falsch zugeordnet oder es stehen ungültige Daten in der Datei.'
        }
        return false
      },
    },
  },
  boolean: {
    question: question,
    feedback: feedback,
    image: image,
    correct_answer: {
      title: "*Richtige Antwort",
      required: true,
      error(answers) {
        let emptyAnswers = answers.filter(answer => {
          return answer.length === 0
        }).length
        if(emptyAnswers) {
          let error
          if(emptyAnswers === 1) {
            error = 'Eine Frage hat'
          } else {
            error = emptyAnswers + ' Fragen haben'
          }
          return error + ' keine richtige Antwort. Eventuell ist die Spalte falsch zugeordnet oder es fehlen Daten in der Datei.'
        }
        return false
      },
    },
    incorrect_answer: {
      title: "*Falsche Antwort",
      required: true,
      error(answers) {
        let emptyAnswers = answers.filter(answer => {
          return answer.length === 0
        }).length
        if(emptyAnswers) {
          let error
          if(emptyAnswers === 1) {
            error = 'Eine Frage hat'
          } else {
            error = emptyAnswers + ' Fragen haben'
          }
          return error + ' keine falsche Antwort. Eventuell ist die Spalte falsch zugeordnet oder es fehlen Daten in der Datei.'
        }
        return false
      },
    },
  },
  indexcards: {
    question: {
      title: "*Vorderseite",
      required: true,
    },
    image: image,
    correct_answer: {
      title: "*Rückseite",
      required: true,
      error(answers) {
        let emptyAnswers = answers.filter(answer => {
          return answer.length === 0
        }).length
        if(emptyAnswers) {
          let error
          if(emptyAnswers === 1) {
            error = 'Eine Frage hat'
          } else {
            error = emptyAnswers + ' Fragen haben'
          }
          return error + ' keine richtige Antwort. Eventuell ist die Spalte falsch zugeordnet oder es fehlen Daten in der Datei.'
        }
        return false
      },
    },
  }
}
