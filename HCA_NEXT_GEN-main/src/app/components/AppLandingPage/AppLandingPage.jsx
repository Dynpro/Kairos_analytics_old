import useSettings from 'app/hooks/useSettings';
import AnalyticsToolInfo from './AnalyticsToolInfo';
import AppGetInTouch from './AppGetInTouch';
import Header from './Header';
import HeroSection from './HeroSection';
import LearnMore from './LearnMore';
import { useDispatch } from 'react-redux';
import { useEffect } from 'react';
import commonConfig from '../commonConfig';
import { defaultThemeOption } from 'app/utils/utils';

const AppLandingPage = () => {
  const dispatchX = useDispatch();
  const { updateSettings } = useSettings();
  useEffect(() => {
    updateSettings(defaultThemeOption);
    dispatchX({
      type: 'SET_THEME',
      theme: defaultThemeOption,
    });
    dispatchX({ type: 'SET_USER_TYPE', userIsA: 'viewer' });
    dispatchX({ type: 'RESET_TOKEN' });
    dispatchX({ type: 'RESET_ALL_LOOKER_DATA' });
    dispatchX({ type: 'RESET_USER_PROFILE' });
    dispatchX({ type: 'RESET_CLIENT' });
    dispatchX({ type: 'RESET_USER_TYPE' });
    dispatchX({ type: 'RESET_USERACCESS_PERMISSIONS' });

    localStorage.removeItem(commonConfig.tokens.accessToken);
    localStorage.removeItem(commonConfig.tokens.persist);
    localStorage.removeItem(commonConfig.tokens.lastScheduledTime);
  }, []);
  return (
    <>
      <Header />
      <HeroSection />
      <AnalyticsToolInfo />
      <LearnMore />
      <AppGetInTouch />
    </>
  );
};

export default AppLandingPage;
