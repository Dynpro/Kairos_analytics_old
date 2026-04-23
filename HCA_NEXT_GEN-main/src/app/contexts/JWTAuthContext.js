import SnackbarUtils from 'SnackbarUtils';
import { MatxLoading } from 'app/components';
import commonConfig from 'app/components/commonConfig';
import commonRoutes from 'app/components/commonRoutes';
import useSettings from 'app/hooks/useSettings';
import { defaultThemeOption, getAccessToken } from 'app/utils/utils';
import axios from 'axios.js';
import jwtDecode from 'jwt-decode';
import { createContext, useEffect, useReducer } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';

const initialState = {
  isAuthenticated: false,
  isInitialised: false,
  user: null,
};

const setSession = (accessToken) => {
  if (accessToken) {
    localStorage.setItem(commonConfig.tokens.accessToken, accessToken);
    axios.defaults.headers.common.Authorization = `Bearer ${accessToken}`;
  } else {
    localStorage.removeItem(commonConfig.tokens.accessToken);
    localStorage.removeItem(commonConfig.tokens.persist);
    localStorage.removeItem(commonConfig.tokens.lastScheduledTime);
    delete axios.defaults.headers.common.Authorization;
  }
};

const reducer = (state, action) => {
  switch (action.type) {
    case 'INIT':
      return {
        ...state,
        isAuthenticated: action.payload.isAuthenticated,
        isInitialised: true,
        user: action.payload.user,
      };

    case 'LOGIN':
      return {
        ...state,
        isAuthenticated: true,
        user: action.payload.user,
      };

    case 'LOGOUT':
      return {
        ...state,
        isAuthenticated: false,
        user: null,
      };

    case 'REGISTER':
      return {
        ...state,
        isAuthenticated: true,
        user: action.payload.user,
      };

    default:
      return state;
  }
};

const AuthContext = createContext({
  ...initialState,
  method: 'JWT',
  login: () => Promise.resolve(),
  logout: () => {},
  register: () => Promise.resolve(),
});

export const AuthProvider = ({ children }) => {
  const { updateSettings } = useSettings();
  const navigate = useNavigate();
  const [state, dispatch] = useReducer(reducer, initialState);
  const dispatchX = useDispatch();
  const user = useSelector((state) => state.userDetails?.user);

  // ✅ FIXED LOGIN
  const login = async (email, password) => {
    try {
      const response = await axios.post(commonConfig.urls.login, {
        email,
        password,
      });

      if (response?.status === 200) {
        const accessToken = response?.data?.Response?.access_token;
        const user = response?.data?.Response?.user;
        const userPermissions = response?.data?.Response?.user_access;

        if (!accessToken) throw new Error('No access token received');

        setSession(accessToken);

        dispatchX({ type: 'SET_TOKEN', accessToken });
        dispatchX({ type: 'SET_USER', user });
        dispatchX({ type: 'SET_USERACCESS_PERMISSIONS', userPermissions });
        dispatchX({ type: 'SET_USER_TYPE', userIsA: 'viewer' });

        dispatch({
          type: 'LOGIN',
          payload: {
            user: {
              avatar: '/assets/images/face-6.jpg',
              email: user?.email,
              name: `${user?.name} ${user?.last_name}`,
              id: user?.id,
              role: user?.role,
              group: user?.group,
            },
          },
        });

        // Fetch user access data
        const [uaClients, uaFolders, uaDashboards, uaSubCategories] =
          await Promise.all([
            axios(commonConfig.urls.getUserAccessClients),
            axios(commonConfig.urls.getUserAccessFolders),
            axios(commonConfig.urls.getUserAccessdashboards),
            axios(commonConfig.urls.getClientCateWiseUserAccesAllsNew),
          ]);

        dispatchX({ type: 'SET_USERACCESS_CLIENTS', uaClients: uaClients?.data?.data });
        dispatchX({ type: 'SET_USERACCESS_FOLDERS', uaFolders: uaFolders?.data?.data });
        dispatchX({
          type: 'SET_USERACCESS_DASHBOARDS',
          uaDashboards: uaDashboards?.data?.data,
        });
        dispatchX({
          type: 'SET_USERACCESS_SUBCATEGORIES',
          uaSubCategories: uaSubCategories?.data?.data,
        });

        return response.data;
      }
    } catch (error) {
      console.error('Login Error:', error);

      if (error.response) {
        SnackbarUtils.error(error.response.data?.message || 'Login failed');
      } else if (error.request) {
        SnackbarUtils.error('Server not responding (502 / Network issue)');
      } else {
        SnackbarUtils.error('Unexpected error occurred');
      }

      throw error;
    }
  };

  const register = async (firstname, lastname, email, password, confirm_password, group_code) => {
    try {
      const response = await axios.post(commonConfig.urls.register, {
        firstname,
        lastname,
        email,
        password,
        confirm_password,
        group_code,
        entity_id: process.env.REACT_APP_env_entity_id,
      });

      const { accessToken, user } = response.data;

      if (response.data?.Code === 200) {
        SnackbarUtils.success('OTP SUCCESSFULLY SENT');
        navigate(commonRoutes.session.validateotp, { state: response.data.Response });
      } else {
        SnackbarUtils.error('Registration failed');
      }

      setSession(accessToken);

      dispatch({
        type: 'REGISTER',
        payload: { user },
      });
    } catch (error) {
      SnackbarUtils.error('Registration error');
      console.error(error);
    }
  };

  const resetStore = () => {
    dispatchX({ type: 'RESET_TOKEN' });
    dispatchX({ type: 'RESET_ALL_LOOKER_DATA' });
    dispatchX({ type: 'RESET_USER_PROFILE' });
    dispatchX({ type: 'RESET_CLIENT' });
    dispatchX({ type: 'RESET_USERACCESS_PERMISSIONS' });
    dispatch({ type: 'LOGOUT' });
  };

  const logout = () => {
    const accessToken = getAccessToken();

    axios.post(commonConfig.urls.logout, null, {
      headers: { Authorization: `Bearer ${accessToken}` },
    });

    setSession(null);
    updateSettings(defaultThemeOption);

    dispatchX({ type: 'SET_THEME', theme: defaultThemeOption });

    resetStore();
  };

  useEffect(() => {
    (async () => {
      try {
        const accessToken = getAccessToken();
        if (!accessToken) throw new Error();

        const decodedToken = jwtDecode(accessToken);
        const notExpired =
          new Date(decodedToken.nbf * 1000 + 29 * 60 * 1000) > new Date();

        if (notExpired) {
          setSession(accessToken);
          dispatch({
            type: 'INIT',
            payload: { isAuthenticated: true, user: user || null },
          });
        } else {
          throw new Error();
        }
      } catch {
        setSession(null);
        dispatch({
          type: 'INIT',
          payload: { isAuthenticated: false, user: null },
        });
      }
    })();
  }, []);

  if (!state.isInitialised) return <MatxLoading />;

  return (
    <AuthContext.Provider
      value={{
        ...state,
        method: 'JWT',
        login,
        logout,
        register,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export default AuthContext;
