export const SET_TOKEN = 'SET_TOKEN';
export const RESET_TOKEN = 'RESET_TOKEN';

const initialState = {
  accessToken: '',
};

export default (state = initialState, action) => {
  switch (action.type) {
    case SET_TOKEN:
      const { accessToken } = action;
      return {
        ...state,
        accessToken,
      };
    case RESET_TOKEN:
      return {
        ...initialState,
      };
    default:
      return state;
  }
};

export const setAccessToken = (accessToken = '') => ({
  type: SET_TOKEN,
});
