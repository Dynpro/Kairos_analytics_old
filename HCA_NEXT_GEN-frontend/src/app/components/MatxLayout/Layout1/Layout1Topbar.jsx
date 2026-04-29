import { Avatar, Icon, IconButton, MenuItem, Tooltip, useMediaQuery } from '@mui/material';
import { Box, styled, useTheme } from '@mui/system';
import { MatxMenu } from 'app/components';
import AppCurrentClient from 'app/components/AppCurrentClient';
import AppTogglePermission from 'app/components/AppTogglePermission';
import { themeShadows } from 'app/components/MatxTheme/themeColors';
import TopBarSettingsMenu from 'app/components/TopBarSettingsMenu';
import TopBarSettingsMenuOptions from 'app/components/TopBarSettingsMenuOptions';
import commonRoutes from 'app/components/commonRoutes';
import useAuth from 'app/hooks/useAuth';
import useRefreshData from 'app/hooks/useRefreshData';
import useSettings from 'app/hooks/useSettings';
import { topBarHeight } from 'app/utils/constant';
import React from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Link } from 'react-router-dom';
import { Span } from '../../../components/Typography';
import AppFullscreen from './AppFullscreen';
import AppThemeModeSwitch from './AppThemeModeSwitch';

const StyledIconButton = styled(IconButton)(({ theme }) => ({
  color: theme.palette.text.secondary,
}));

const TopbarRoot = styled('div')(() => ({
  top: 0,
  zIndex: 96,
  transition: 'all 0.3s ease',
  boxShadow: themeShadows[8],
  height: topBarHeight,
}));

const TopbarContainer = styled(Box)(({ theme }) => ({
  color: theme.palette.text.secondary,
  padding: '8px',
  paddingLeft: 18,
  paddingRight: 20,
  height: '100%',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  background: theme.palette.primary.main,
  [theme.breakpoints.down('sm')]: {
    paddingLeft: 16,
    paddingRight: 16,
  },
  [theme.breakpoints.down('xs')]: {
    paddingLeft: 14,
    paddingRight: 16,
  },
}));

const UserMenu = styled(Box)(() => ({
  display: 'flex',
  alignItems: 'center',
  cursor: 'pointer',
  borderRadius: 24,
  padding: 4,
  '& span': { margin: '0 8px' },
}));
const UserInfo = styled(Box)(() => ({
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  borderRadius: 24,
  padding: 4,
  '& span': { margin: '0 8px' },
}));

const StyledItem = styled(MenuItem)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  minWidth: 235,
  '& a': {
    width: '100%',
    display: 'flex',
    alignItems: 'center',
    textDecoration: 'none',
  },
  '& span': { marginRight: '10px', color: theme.palette.text.primary },
}));

const Layout1Topbar = () => {
  const theme = useTheme();
  const { settings, updateSettings } = useSettings();
  const { logout } = useAuth();
  const user = useSelector((state) => state.userDetails?.user);
  const permission_btn = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.permission_btn
  );
  const isMdScreen = useMediaQuery(theme.breakpoints.down('md'));
  const updateSidebarMode = (sidebarSettings) => {
    updateSettings({
      layout1Settings: { leftSidebar: { ...sidebarSettings } },
    });
  };

  const handleSidebarToggle = () => {
    let { layout1Settings } = settings;
    let mode;
    if (isMdScreen) {
      mode = layout1Settings.leftSidebar.mode === 'close' ? 'mobile' : 'close';
    } else {
      mode = layout1Settings.leftSidebar.mode === 'full' ? 'close' : 'full';
    }
    updateSidebarMode({ mode });
  };

  const dispatch = useDispatch();

  const { handleRefreshData } = useRefreshData();

  return (
    <TopbarRoot>
      <TopbarContainer>
        <Box display="flex">
          <Tooltip title="Toggle Sidebar">
            <StyledIconButton onClick={handleSidebarToggle}>
              <Icon>menu</Icon>
            </StyledIconButton>
          </Tooltip>
          <Tooltip title="Home">
            <Link to={commonRoutes.home}>
              <StyledIconButton
                onClick={() => {
                  handleRefreshData();
                  dispatch({
                    type: 'SET_CLIENT',
                    client: {
                      subCategory_name: '',
                      client_name: '',
                      folder_name: '',
                      dashboard_name: '',
                      client_id: '',
                    },
                  });
                }}
              >
                <Icon>home</Icon>
              </StyledIconButton>
            </Link>
          </Tooltip>
        </Box>
        <Box display="flex">
          <AppCurrentClient />
        </Box>
        <Box display="flex" alignItems="center">
          {/* <Tooltip title="User Guides">
            <StyledIconButton
              onClick={() => window.open('https://education.ahcanalytics.com/', '_blank')}
            >
              <Icon>menu_book</Icon>
            </StyledIconButton>
          </Tooltip> */}

          <AppThemeModeSwitch
            Icon={Icon}
            StyledIconButton={StyledIconButton}
            TooltipName="Change Theme"
          />
          <AppFullscreen
            Icon={Icon}
            StyledIconButton={StyledIconButton}
            TooltipName="Toggle Fullscreen"
          />
          <MatxMenu
            menuButton={
              <UserMenu>
                <Avatar src={user?.profile_pic} sx={{ cursor: 'pointer' }} />
              </UserMenu>
            }
          >
            <StyledItem>
              <Link
                to={commonRoutes.userProfile.viewProfile}
                onClick={() =>
                  dispatch({
                    type: 'SET_CLIENT',
                    client: {
                      subCategory_name: '',
                      client_name: '',
                      folder_name: '',
                      dashboard_name: '',
                      client_id: '',
                    },
                  })
                }
              >
                <Avatar src={user?.profile_pic} sx={{ cursor: 'pointer' }} />
                <UserMenu>
                  <UserInfo>
                    <span>
                      <strong>
                        {user?.name || ''} {user?.last_name || ''}
                      </strong>
                    </span>
                    <span>{user?.group || ''}</span>{' '}
                  </UserInfo>
                </UserMenu>{' '}
              </Link>
            </StyledItem>
            {permission_btn === 1 && (
              <StyledItem>
                <AppTogglePermission />
              </StyledItem>
            )}

            <TopBarSettingsMenu
              display={true}
              icon={
                <StyledItem>
                  <Icon>settings</Icon>
                  <Span> Settings </Span>
                </StyledItem>
              }
            >
              <TopBarSettingsMenuOptions />
            </TopBarSettingsMenu>

            <StyledItem onClick={logout}>
              <Icon> power_settings_new </Icon>
              <Span> Logout </Span>
            </StyledItem>
          </MatxMenu>
        </Box>
      </TopbarContainer>
    </TopbarRoot>
  );
};

export default React.memo(Layout1Topbar);
