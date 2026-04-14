export const SET_THEME = 'SET_THEME';

const initialState = {
  theme: null,
};

export default (state = initialState, action) => {
  switch (action.type) {
    case SET_THEME:
      const { theme } = action;
      return {
        ...state,
        theme,
      };
    default:
      return state;
  }
};

export const setTheme = (theme = '') => ({
  type: SET_THEME,
});
