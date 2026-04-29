import { ThunkAction, ThunkActionDispatch, ThunkMiddleware, ThunkDispatch } from 'redux-thunk';
import { Provider } from 'react-redux';

export const INITIAL_STATE = {
  oAuthToken: '',
  refreshToken: '',
};

export const fetchStudent = (callback) => async (dispatch, getState) => {
  try {
    const accessToken = getState().AuthReducer.oAuthToken;

    let response = await axios.get(STUDENT_URL, {
      headers: { Authorization: 'Bearer '.concat(accessToken) },
    });

    dispatch({ type: 'FETCH_STUDENT', payload: response.data });
    callback();
  } catch (e) {
    console.log(e);
  }
};

export default AuthReducer = (state = INITIAL_STATE, action) => {
  const { oAuthToken, refreshToken } = action.payload;
  switch (action.type) {
    case 'REFRESH_OAUTH_DATA':
      return { ...state, oAuthToken, refreshToken };

    case 'LOGOUT':
      return INITIAL_STATE;

    case 'LOGIN_FETCH_SUCCESS':
      return { ...state, oAuthToken, refreshToken };

    default:
      return state;
  }
};
