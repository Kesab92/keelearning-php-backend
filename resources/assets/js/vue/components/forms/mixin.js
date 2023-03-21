import constants from "../../logic/constants"

export default {
  data() {
    return {
      typeIcons: {
        [constants.FORMS.TYPE_TEXTAREA]: 'edit',
        [constants.FORMS.TYPE_RATING]: 'star',
        [constants.FORMS.TYPE_HEADER]: 'title',
        [constants.FORMS.TYPE_SEPARATOR]: 'vertical_distribute',
      },
      typeLabels: {
        [constants.FORMS.TYPE_TEXTAREA]: 'Freitext',
        [constants.FORMS.TYPE_RATING]: 'Rating',
        [constants.FORMS.TYPE_HEADER]: 'Überschrift',
        [constants.FORMS.TYPE_SEPARATOR]: 'Trennlinie',
      },
    }
  },
}
