export const SET_USER_TYPE = 'SET_USER_TYPE';
export const RESET_USER_TYPE = 'RESET_USER_TYPE';
const initialState = {
  userIsA: 'viewer',
};

export default (state = initialState, action) => {
  switch (action.type) {
    case SET_USER_TYPE:
      const { userIsA } = action;
      return {
        ...state,
        userIsA,
      };
    case RESET_USER_TYPE:
      return {
        ...initialState,
      };
    default:
      return state;
  }
};
