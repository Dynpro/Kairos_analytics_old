export const SET_USER = 'SET_USER';
export const SET_USER_PROFILE = 'SET_USER_PROFILE';
export const SET_USER_NAME = 'SET_USER_NAME';
export const RESET_USER_PROFILE = 'RESET_USER_PROFILE';

const initialState = {
  user: null,
};

export default (state = initialState, action) => {
  switch (action.type) {
    case SET_USER:
      const { user } = action;
      return {
        ...state,
        user,
      };
    case SET_USER_PROFILE:
      const { profile_pic } = action;
      return {
        ...state,
        user: { ...state.user, profile_pic },
      };
    case SET_USER_NAME:
      const { name, last_name } = action;
      return {
        ...state,
        user: { ...state.user, name, last_name },
      };
    case RESET_USER_PROFILE:
      return {
        ...state,
        user: null,
      };

    default:
      return state;
  }
};

export const setUser = (user = null) => ({
  type: SET_USER,
});
