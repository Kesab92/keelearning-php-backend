import constants from "../../logic/constants";

const defaultState = {
  voucherDetails: {},
  listIsLoading: false,
  search: null,
  filter: constants.VOUCHERS.FILTER_ACTIVE,
  tagGroups: [],
  tagsWithoutGroup: [],
  tagsRequired: false,
  pagination: {
    sortBy: 'id',
    descending: true,
    page: 1,
    rowsPerPage: 50,
  },
  vouchers: [],
  vouchersCount: 0,
}

export default defaultState
