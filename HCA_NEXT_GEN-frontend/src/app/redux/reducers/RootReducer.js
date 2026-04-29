import { combineReducers } from 'redux';
import NavigationReducer from './NavigationReducer';
import snackbarReducer from '../ducks/snackbar';
import currentClientReducer from '../ducks/currentClient';
import accessTokenReducer from '../ducks/accessToken';
import userDetailsReducer from '../ducks/userDetails';
import userAccessReducer from '../ducks/userAccess';
import userPermissionsReducer from '../ducks/userPermissions';
import userTypeReducer from '../ducks/userType';
import modalReducer from '../ducks/modal';
import themeReducer from '../ducks/currentTheme';
import storage from 'redux-persist/lib/storage';

const appReducer = combineReducers({
  navigations: NavigationReducer,
  snackbar: snackbarReducer,
  currentClient: currentClientReducer,
  accessToken: accessTokenReducer,
  userDetails: userDetailsReducer,
  userAccess: userAccessReducer,
  userType: userTypeReducer,
  userAccessPermissions: userPermissionsReducer,
  modal: modalReducer,
  currentTheme: themeReducer,
});

const RootReducer = (state, action) => {
  if (action.type === 'USER_LOGOUT') {
    storage.removeItem('persist:root');
    localStorage.removeItem('persist:root');
    state = undefined;
  }
  return appReducer(state, action);
};

export default RootReducer;
