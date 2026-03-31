export const SET_CLIENT = 'SET_CLIENT';
export const RESET_CLIENT = 'RESET_CLIENT';

const initialState = {
  client: {
    subCategory_name: '',
    client_name: '',
    folder_name: '',
    dashboard_name: '',
    client_id: '',
  },
};

export default (state = initialState, action) => {
  switch (action.type) {
    case SET_CLIENT:
      const { client } = action;
      return {
        ...state,
        client,
      };
    case RESET_CLIENT:
      return {
        ...initialState,
      };
    default:
      return state;
  }
};

export const setClient = (client = '') => ({
  type: SET_CLIENT,
});
