import React, { createContext, useState } from 'react';
import { themes } from 'app/components/MatxTheme/initThemes';

import { merge } from 'lodash';

import { MatxLayoutSettings } from 'app/components/MatxLayout/settings';
import { useSelector } from 'react-redux';

const SettingsContext = createContext({
  settings: MatxLayoutSettings,
  updateSettings: () => {},
});

export const SettingsProvider = ({ settings, children }) => {
  const currentTheme = useSelector((state) => state.currentTheme?.theme);
  const initSettings = {
    activeLayout: 'layout1', // layout1, layout2
    activeTheme: currentTheme?.activeTheme || 'blue', // View all valid theme colors inside MatxTheme/themeColors.js
    perfectScrollbar: false,

    themes: themes,
    layout1Settings: {
      leftSidebar: {
        show: true,
        mode: 'full', // full, close, compact, mobile,
        theme: currentTheme?.layout1Settings?.leftSidebar?.theme || 'slateDark1', // View all valid theme colors inside MatxTheme/themeColors.js
        bgImgURL: '/assets/images/sidebar/sidebar-bg-dark.jpg',
      },
      topbar: {
        show: true,
        fixed: true,
        theme: currentTheme?.layout1Settings?.topbar?.theme || 'purpleDark11', // View all valid theme colors inside MatxTheme/themeColors.js
      },
    }, // open Layout1/Layout1Settings.js

    secondarySidebar: {
      show: false, //initially true
      open: false,
      theme: 'slateDark1', // View all valid theme colors inside MatxTheme/themeColors.js
    },
    // Footer options
    footer: {
      show: false,
      fixed: false,
      theme: 'slateDark1', // View all valid theme colors inside MatxTheme/themeColors.js
    },
  };
  const [currentSettings, setCurrentSettings] = useState(settings || initSettings);

  const handleUpdateSettings = (update = {}) => {
    const marged = merge({}, currentSettings, update);
    setCurrentSettings(marged);
    return marged;
  };

  return (
    <SettingsContext.Provider
      value={{
        settings: currentSettings,
        updateSettings: handleUpdateSettings,
      }}
    >
      {children}
    </SettingsContext.Provider>
  );
};

export default SettingsContext;
