import {
  Box,
  Icon,
  IconButton,
  List,
  ListItemButton,
  ListItemIcon,
  ListItemText,
  styled,
  useTheme,
} from '@mui/material';
import { useDispatch, useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { H5 } from './Typography';

const types = {
  settingsType: 'settings',
};
const settingOptions = {
  users: {
    name: 'Users',
    path: '/splitUsers',
    icon: 'person',
    level: 1,
    group: types.settingsType,
  },
  looker: {
    name: 'Looker',
    path: '/lookerList',
    icon: 'monitoring',
    level: 1,
    group: types.settingsType,
  },
  /*   snowflake: {
      name: 'Snowflake',
      path: '/material/buttons',
      icon: 'storage',
      level: 1,
     r typ:typee
    }, */
  groups: {
    name: 'Group Master',
    path: '/group-master',
    icon: 'groups',
    level: 1,
    group: types.settingsType,
  },
  user_access: {
    name: 'User Access Control',
    path: '/groups',
    icon: 'diversity_3',
    level: 1,
    group: types.settingsType,
  },
  roles: {
    name: 'Roles',
    path: '/role',
    icon: 'person_add',
    level: 1,
    group: types.settingsType,
  },
  clients: {
    name: 'Clients',
    path: '/client',
    icon: 'settings',
    level: 1,
    group: types.settingsType,
  },
    reports: {
      name: 'Reports',
      path: '/reports',
      icon: 'summarize',
      level: 1,
      group: types.settingsType,
    }, 
  generate_reports: {
    name: 'Generate Reports',
    path: '/splitGenerateReports',
    icon: 'description',
    level: 1,
    group: types.settingsType,
  },
  about: {
    name: 'About',
    icon: 'info',
    type: 'modal',
    level: 1,
    actionType: 'SET_OPEN_MODAL',
    group: types.settingsType,
  },
  invite_user: {
    name: 'Invite User',
    icon: 'group_add',
    type: 'modal',
    level: 1,
    actionType: 'SET_OPEN_INVITEUSERMODAL',
    group: types.settingsType,
  },
  referral: {
    name: 'Referral',
    path: '/referral',
    icon: 'fingerprint',
    level: 1,
    group: types.settingsType,
  },
  /*   localization: {
      name: 'Localization',
      icon: 'settings',
      path: '/localization',
      level: 1,
      group: types.settingsType,
    }, */
};

function generateSettingOptions(userPermissions) {
  const arr = [];
  if (userPermissions?.looker && userPermissions['looker'] === 1)
    arr.push(settingOptions['looker']);

  /* if (userPermissions?.snowflake && userPermissions['snowflake'] === 1)
      arr.push(settingOptions['snowflake']); */
  if (userPermissions?.clients && userPermissions['clients'] === 1)
    arr.push(settingOptions['clients']);
  if (userPermissions?.users && userPermissions['users'] === 1) arr.push(settingOptions['users']);
  if (userPermissions?.roles && userPermissions['roles'] === 1) arr.push(settingOptions['roles']);
  if (userPermissions?.group_module && userPermissions['group_module'] === 1)
    arr.push(settingOptions['groups'], settingOptions['user_access']);
    if (userPermissions?.reports && userPermissions['reports'] === 1)
      arr.push(settingOptions['reports']); 
  if (userPermissions?.generate_report && userPermissions['generate_report'] === 1)
    arr.push(settingOptions['generate_reports']);
  /* arr.push(settingOptions['localization']); */
  if (userPermissions?.invite_user && userPermissions['invite_user'] === 1)
    arr.push(settingOptions['invite_user']);
  if (userPermissions?.referral && userPermissions['referral'] === 1)
    arr.push(settingOptions['referral']);
  arr.push(settingOptions['about']);
  arr.map((item) => ({ ...item, newType: 'sd' }));
  return arr.length > 0 ? arr : null;
}

const ChatContainer = styled('div')(({ theme }) => ({
  height: '100%',
  display: 'flex',
  flexDirection: 'column',
  background: theme.palette.type === 'light' ? '#fff' : theme.palette.primary.main,
}));

const ProfileBox = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  padding: '12px 12px 12px 20px',
  color: theme.palette.text.primary,
}));

const AppHr = styled('hr')(({ theme }) => {
  return {
    width: '100%',
    padding: 0,
    margin: 0,
    backgroundColor: theme.palette.text.primary,
  };
});

const ChatStatus = styled('div')(() => ({
  marginLeft: '12px',
  '& h5': {
    marginTop: 0,
    fontSize: '14px',
    marginBottom: '3px',
  },
  '& span': { fontWeight: '500' },
}));

const TopBarSettingsMenuOptions = ({ togglePopup }) => {
  const { uaPermissions } = useSelector((state) => ({
    uaPermissions: state.userAccessPermissions.userPermissions,
  }));

  const { palette } = useTheme();

  const textPrimary = palette.text.primary;

  const settingsOptions = generateSettingOptions(uaPermissions);
  const navigate = useNavigate();
  const dispatch = useDispatch();

  return (
    <ChatContainer>
      <ProfileBox>
        <Box display="flex" alignItems="center">
          <Icon>settings</Icon>
          <ChatStatus>
            <H5>Settings</H5>
          </ChatStatus>
        </Box>
        <IconButton onClick={togglePopup}>
          <Icon fontSize="small" style={{ color: textPrimary }}>
            clear
          </Icon>
        </IconButton>
      </ProfileBox>
      <AppHr />
      <List>
        {settingsOptions.map((item, index) => (
          <ListItemButton
            key={index}
            onClick={() => {
              if (item.type !== 'modal') {
                navigate(item.path);
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
              }
              if (item.actionType === 'SET_OPEN_MODAL') {
                dispatch({ type: 'SET_OPEN_MODAL', open: true });
              }
              if (item.actionType === 'SET_OPEN_INVITEUSERMODAL') {
                dispatch({ type: 'SET_OPEN_INVITEUSERMODAL', openInviteUser: true });
              }
              togglePopup();
            }}
          >
            <ListItemIcon>
              <Icon style={{ color: textPrimary }}>{item?.icon}</Icon>
            </ListItemIcon>
            <ListItemText primary={item?.name} />
          </ListItemButton>
        ))}
      </List>
    </ChatContainer>
  );
};

export default TopBarSettingsMenuOptions;