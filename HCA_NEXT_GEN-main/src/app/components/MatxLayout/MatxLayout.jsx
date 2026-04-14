import SnackbarUtils from 'SnackbarUtils';
import { MatxSuspense } from 'app/components';
import AuthContext from 'app/contexts/JWTAuthContext';
import useIdleTimeout from 'app/hooks/useIdleTimeout';
import useSettings from 'app/hooks/useSettings';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { useContext, useEffect, useState } from 'react';
import { useDispatch } from 'react-redux';
import AppAboutModule from '../AppAboutModule';
import AppInviteUserModule from '../AppInviteUserModule';
import AppTimer from '../AppTimer';
import AppSnackbar from '../ReusableComponents/AppSnackbar';
import commonConfig from '../commonConfig';
import { MatxLayouts } from './index';

const MatxLayout = (props) => {
  const { settings } = useSettings();
  const [modalIsOpen, setModalIsOpen] = useState(false);
  const dispatch = useDispatch();
  const handlePrompt = () => setModalIsOpen(true);
  const { idleTimer, setIdle } = useIdleTimeout({
    onPrompt: handlePrompt,
    idleTime: 1,
  });

  useEffect(() => {
    let timeoutId;
    let nextScheduledTime = localStorage.getItem('lastScheduledTime');
    const totalMins = 27;

    const scheduleNextExecution = () => {
      // Perform your desired action here
      if (idleTimer.isLastActiveTab()) handleRefreshToken();

      // Calculate and update the next scheduled time
      const currentTime = new Date().getTime();
      const twentySevenMinutesInMillis = 1000 * 60 * totalMins;
      nextScheduledTime = new Date(currentTime + twentySevenMinutesInMillis).toString();
      localStorage.setItem('lastScheduledTime', nextScheduledTime);

      // Schedule the next execution
      timeoutId = setTimeout(scheduleNextExecution, twentySevenMinutesInMillis);
    };

    if (nextScheduledTime) {
      const currentTime = new Date().getTime();
      const remainingTime = new Date(nextScheduledTime) - currentTime;
      timeoutId = setTimeout(scheduleNextExecution, remainingTime);
    } else {
      const now = new Date();
      nextScheduledTime = new Date(now.getTime() + totalMins * 60 * 1000).toString();
      localStorage.setItem('lastScheduledTime', nextScheduledTime);

      // Schedule the first execution
      const initialRemainingTime = totalMins * 60 * 1000;
      timeoutId = setTimeout(scheduleNextExecution, initialRemainingTime);
    }

    return () => {
      clearTimeout(timeoutId);
    };
  }, []);

  const handleRefreshToken = async () => {
    const authToken = getAccessToken();
    let config = {
      method: 'post',
      maxBodyLength: Infinity,
      url: commonConfig.urls.refreshToken,
      headers: {
        Authorization: `Bearer ${authToken}`,
      },
    };
    try {
      const response = await axios(config);
      if (response.data?.Code === 200) {
        localStorage.setItem('accessToken', response.data.Response.access_token);
        const accessToken = response.data.Response.access_token;
        dispatch({ type: 'SET_TOKEN', accessToken });
      }
    } catch (error) {
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
      logout();
    }
  };
  const stayLoggedIn = async () => {
    setModalIsOpen(false);
    idleTimer.reset();
    const authToken = getAccessToken();
    let config = {
      method: 'post',
      maxBodyLength: Infinity,
      url: commonConfig.urls.refreshToken,
      headers: {
        Authorization: `Bearer ${authToken}`,
      },
    };
    try {
      const response = await axios(config);
      if (response.data?.Code === 200) {
        localStorage.setItem('accessToken', response.data.Response.access_token);
        const accessToken = response.data.Response.access_token;
        dispatch({ type: 'SET_TOKEN', accessToken });
      }
    } catch (error) {
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
      logout();
    }
  };

  const { logout } = useContext(AuthContext);
  const handleIdle = () => {
    setIdle(true);
    dispatch({ type: 'SET_USER_TYPE', userIsA: 'viewer' });
    logout();
  };

  window.addEventListener('storage', (e) => {
    if (e.key === commonConfig.tokens.accessToken && e.oldValue && !e.newValue) {
      logout();
    }
  });

  const Layout = MatxLayouts[settings.activeLayout];
  return (
    <MatxSuspense>
      {idleTimer.isLastActiveTab() && (
        <AppTimer open={modalIsOpen} handleExtend={stayLoggedIn} handleLogout={handleIdle} />
      )}
      <AppAboutModule />
      <AppInviteUserModule />
      <AppSnackbar />
      <Layout {...props} />
    </MatxSuspense>
  );
};

export default MatxLayout;
