import constants from "../../logic/constants"

export function getPositionLabels() {
  return {
    [constants.ADVERTISEMENTS.POSITION_LOGIN]: 'Login',
    [constants.ADVERTISEMENTS.POSITION_HOME_MIDDLE]: 'Home (mitte)',
    [constants.ADVERTISEMENTS.POSITION_HOME_BOTTOM]: 'Home (unten)',
    [constants.ADVERTISEMENTS.POSITION_NEWS]: 'News',
    [constants.ADVERTISEMENTS.POSITION_MEDIALIBRARY]: 'Mediathek',
    [constants.ADVERTISEMENTS.POSITION_POWERLEARNING]: 'Powerlearning',
    [constants.ADVERTISEMENTS.POSITION_INDEXCARDS]: 'Karteikarten',
    [constants.ADVERTISEMENTS.POSITION_QUIZ]: 'Quiz',
    [constants.ADVERTISEMENTS.POSITION_TESTS]: 'Tests',
  }
}

export function getOrderedPositions() {
  return [
    constants.ADVERTISEMENTS.POSITION_LOGIN,
    constants.ADVERTISEMENTS.POSITION_HOME_MIDDLE,
    constants.ADVERTISEMENTS.POSITION_HOME_BOTTOM,
    constants.ADVERTISEMENTS.POSITION_NEWS,
    constants.ADVERTISEMENTS.POSITION_MEDIALIBRARY,
    constants.ADVERTISEMENTS.POSITION_POWERLEARNING,
    constants.ADVERTISEMENTS.POSITION_INDEXCARDS,
    constants.ADVERTISEMENTS.POSITION_QUIZ,
    constants.ADVERTISEMENTS.POSITION_TESTS,
  ]
}

export function getPositionLabel(position) {
  const labels = getPositionLabels()
  if(typeof labels[position] === 'undefined') {
    return 'n/a'
  }
  return labels[position]
}
