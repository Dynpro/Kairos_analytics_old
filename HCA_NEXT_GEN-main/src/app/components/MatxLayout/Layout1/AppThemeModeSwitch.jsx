import { Tooltip } from '@mui/material';

import useSettings from 'app/hooks/useSettings';
import { useDispatch, useSelector } from 'react-redux';

const determineIcon = (activeTheme) => (activeTheme === 'slateDark1' ? 'light_mode' : 'dark_mode');

const demoLayouts = [
  {
    isPro: false,
    name: 'Light Theme',
    thumbnail: '/assets/images/screenshots/layout1-customizer.png',
    options: {
      activeTheme: 'blue1',
      activeLayout: 'layout1',
      layout1Settings: {
        topbar: { theme: 'blueDark1', fixed: true },
        leftSidebar: { mode: 'full', theme: 'whiteBlue1', bgOpacity: 0.98 },
      },
      footer: { theme: 'slateDark1' },
    },
  },
  {
    isPro: false,
    name: 'Dark Theme',
    thumbnail: '/assets/images/screenshots/layout3-customizer.png',
    options: {
      activeTheme: 'slateDark1',
      activeLayout: 'layout1',
      layout1Settings: {
        topbar: { theme: 'purpleDark11', fixed: true },
        leftSidebar: { mode: 'full', theme: 'slateDark1', bgOpacity: 0.92 },
      },
    },
  },
];

const AppThemeModeSwitch = ({ Icon, StyledIconButton, TooltipName }) => {
  const { _, updateSettings } = useSettings();
  const dispatch = useDispatch();

  const setTheme = (layout) => {
    updateSettings(layout);
    dispatch({ type: 'SET_THEME', theme: layout });
  };
  const currentTheme = useSelector((state) => state.currentTheme?.theme?.activeTheme);
  const determinedIcon = determineIcon(currentTheme);
  return (
    <Tooltip title={TooltipName}>
      <StyledIconButton
        style={{ cursor: 'pointer' }}
        onClick={() => {
          switch (currentTheme) {
            case 'slateDark1':
              setTheme(demoLayouts[0].options);
              break;
            case 'blue1':
              setTheme(demoLayouts[1].options);
              break;
            case 'blue':
              setTheme(demoLayouts[1].options);
              break;
            //   default:
            //     setTheme(demoLayouts[0].options);
          }
        }}
      >
        <Icon>{determinedIcon}</Icon>
      </StyledIconButton>
    </Tooltip>
  );
};

export default AppThemeModeSwitch;
