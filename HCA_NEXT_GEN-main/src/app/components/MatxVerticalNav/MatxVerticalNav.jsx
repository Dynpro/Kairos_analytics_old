import { Box, ButtonBase, Icon, TextField } from '@mui/material';
import { styled } from '@mui/system';
import useSettings from 'app/hooks/useSettings';
import React, { Fragment } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { NavLink, useLocation } from 'react-router-dom';
import { Span } from '../Typography';
import MatxVerticalNavExpansionPanel from './MatxVerticalNavExpansionPanel';

import { Cancel } from '@mui/icons-material';
import { IconButton, InputAdornment } from '@mui/material';

const AppCancel = styled(Cancel)(({ theme, mode }) => ({
  color: theme.palette.text.secondary,
}));
const AppTotalCount = styled('p')(({ theme, mode }) => ({
  color: theme.palette.text.secondary,
}));

const ExtAndIntCommon = {
  display: 'flex',
  overflow: 'hidden',
  borderRadius: '4px',
  height: 44,
  whiteSpace: 'pre',
  marginBottom: '8px',
  textDecoration: 'none',
  justifyContent: 'space-between',
  transition: 'all 150ms ease-in',
  '&:hover': { background: 'rgba(255, 255, 255, 0.4)' },
  '&.compactNavItem': {
    overflow: 'hidden',
    justifyContent: 'center !important',
  },
  '& .icon': {
    fontSize: '18px',
    paddingLeft: '16px',
    paddingRight: '16px',
    verticalAlign: 'middle',
  },
};
const ExternalLink = styled('a')(({ theme }) => ({
  ...ExtAndIntCommon,
  color: theme.palette.text.primary,
}));

//here set color of selected item
const InternalLink = styled(Box)(({ theme, item }) => {
  return {
    '& a': {
      ...ExtAndIntCommon,
      color: theme.palette.text.primary,
    },
    '& .navItemActive': {
      backgroundColor: 'rgba(231,222,204, 0.8)',
    },
  };
});

/* colors all without children */
const StyledText = styled(Span)(({ mode }) => {
  return {
    fontSize: '0.875rem',
    paddingLeft: '0.8rem',
    display: mode === 'compact' && 'none',
    textAlign: 'left',
  };
});

const BulletIcon = styled('div')(({ theme }) => ({
  padding: '2px',
  marginLeft: '24px',
  marginRight: '8px',
  overflow: 'hidden',
  borderRadius: '300px',
  background: theme.palette.text.primary,
}));

const BadgeValue = styled('div')(() => ({
  padding: '1px 8px',
  overflow: 'hidden',
  borderRadius: '300px',
}));

const MatxVerticalNav = ({ items }) => {
  const { settings } = useSettings();
  const { mode } = settings.layout1Settings.leftSidebar;
  const dispatch = useDispatch();
  const currentClient = useSelector((state) => state.currentClient.client);
  const location = useLocation();

  const renderLevels = (data) => {
    return data.map((item, index) => {
      // if (item.type === 'label')
      //   return (
      //     <ListLabel key={index} mode={mode} className="sidenavHoverShow">
      //       {item.label}
      //     </ListLabel>
      //   );
      if (item.type === 'modal')
        return (
          <InternalLink key={index} item={item}>
            <ButtonBase
              onClick={() => {
                if (item.actionType === 'SET_OPEN_MODAL')
                  dispatch({ type: 'SET_OPEN_MODAL', open: true });

                if (item.actionType === 'SET_OPEN_INVITEUSERMODAL')
                  dispatch({ type: 'SET_OPEN_INVITEUSERMODAL', openInviteUser: true });
              }}
              key={item.name}
              name="child"
              sx={{ width: '100%', marginBottom: '15px' }}
            >
              {item?.icon ? (
                <Icon
                  className="icon"
                  sx={{ width: 36 }}
                  style={{
                    fontSize: item.level === 3 && '0.4em',
                    marginLeft: item.level === 1 ? '0.5em' : item.level === 3 ? '2em' : '',
                  }}
                >
                  {item.icon}
                </Icon>
              ) : (
                <Fragment>
                  <BulletIcon
                    className={`nav-bullet`}
                    sx={{ display: mode === 'compact' && 'none' }}
                  />
                  <Box
                    className="nav-bullet-text"
                    sx={{
                      ml: '20px',
                      fontSize: '11px',
                      display: mode !== 'compact' && 'none',
                    }}
                  >
                    {item.iconText}
                  </Box>
                </Fragment>
              )}
              <StyledText
                mode={mode}
                className="sidenavHoverShow"
                style={{
                  /*                   fontWeight: item.level === 3 ? 'none' : 'bold', */
                  fontSize: item.level === 3 && '0.3em',
                }}
                sx={
                  item.level === 3
                    ? {
                        textAlign: 'justify',
                        //textJustify: 'inter-word',
                        paddingLeft: '0em',
                        paddingRight: '2em',
                      }
                    : null
                }
              >
                {item.name}
              </StyledText>

              <Box mx="auto" />

              {item.badge && (
                <BadgeValue className="sidenavHoverShow">{item.badge.value}</BadgeValue>
              )}
            </ButtonBase>
          </InternalLink>
        );
      if (item.type === 'search')
        return (
          <TextField
            autoComplete="off"
            fullWidth
            size="small"
            sx={{
              borderBottom: '2px solid rgb(135, 206, 235)',
              width: '85%',
              marginLeft: '8%',
              marginTop: '5px',
            }}
            variant="standard"
            style={{ overflow: 'hidden' }}
            key={index}
            mode={mode}
            value={typeof item.searchVal === 'string' ? item.searchVal : ''}
            id="search_field"
            name="search_field"
            placeholder={item.label}
            onChange={(event) => item.onSearch(event.currentTarget.value)}
            InputProps={{
              endAdornment: (
                <InputAdornment position="end" sx={{ marginRight: '5px' }}>
                  {/* <AppTotalCount> {item.totalCount}</AppTotalCount> */}
                  {item.searchVal?.length > 0 && (
                    <IconButton edge="end" onClick={() => item.onSearch('')}>
                      <AppCancel />
                    </IconButton>
                  )}
                </InputAdornment>
              ),
            }}
          />
        );

      if (item.children) {
        return (
          <MatxVerticalNavExpansionPanel key={index} mode={mode} item={item}>
            {renderLevels(item.children)}
          </MatxVerticalNavExpansionPanel>
        );
      } else if (item.type === 'extLink') {
        return (
          <ExternalLink
            key={index}
            href={item.path}
            className={`${mode === 'compact' && 'compactNavItem'}`}
            rel="noopener noreferrer"
            target="_blank"
          >
            <ButtonBase key={item.name} name="child" sx={{ width: '100%' }}>
              {(() => {
                if (item.icon) {
                  return <Icon className="icon">{item.icon}</Icon>;
                } else {
                  return <span className="item-icon icon-text">{item.iconText}</span>;
                }
              })()}
              <StyledText mode={mode} className={`sidenavHoverShow`}>
                {item.name}
              </StyledText>
              <Box mx="auto"></Box>
              {item.badge && <BadgeValue>{item.badge.value}</BadgeValue>}
            </ButtonBase>
          </ExternalLink>
        );
      } else {
        return (
          <InternalLink key={index} item={item}>
            <NavLink
              to={item.path || ''}
              state={
                item?.route
                  ? {
                      callDashboard: item.callDashboard,
                      client_id: item.client_id,
                      client_name: item.client_name,
                      dash_id: item.dash_id,
                      folder_id: item.folder_id,
                      icon: item.icon,
                      level: item.level,
                      name: item.name,
                      path: item.path,
                      value: item.value,
                    }
                  : { ...item }
              }
              className={({ isActive }) => {
                const isDashboard = item.path === '/dashboard';
                const isSelectedDashboard = isDashboard && currentClient.value === item.value;
                return (isActive && (!isDashboard || isSelectedDashboard))
                  ? `navItemActive ${mode === 'compact' && 'compactNavItem'}`
                  : `${mode === 'compact' && 'compactNavItem'}`;
              }}
            >
              <ButtonBase
                key={item.name}
                name="child"
                sx={{ width: '100%' }}
                onClick={() => {
                  if (item?.callDashboard || item?.callDefault)
                    dispatch({
                      type: 'SET_CLIENT',
                      client: {
                        subCategory_name: item?.subcategory_name,
                        folder_name: item?.folder_name,
                        dashboard_name: item?.name,
                        client_name: item?.client_name,
                        client_id: item?.client_id,
                        dash_id: item?.dash_id,
                        value: item?.value,
                      },
                    });
                  if (item?.group === 'settings')
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
                {item?.icon ? (
                  <Icon
                    className="icon"
                    sx={{ width: 36 }}
                    style={{
                      fontSize: item.level === 3 && '12px', //0.9em
                      marginLeft:
                        item.level === 1
                          ? '0.2em'
                          : item.level === 2
                          ? '1em'
                          : item.level === 3
                          ? '1.8em'
                          : '',
                    }}
                  >
                    {item.icon}
                  </Icon>
                ) : (
                  <Fragment>
                    <BulletIcon
                      className={`nav-bullet`}
                      sx={{ display: mode === 'compact' && 'none' }}
                    />
                    <Box
                      className="nav-bullet-text"
                      sx={{
                        ml: '20px',
                        fontSize: '11px',
                        display: mode !== 'compact' && 'none',
                      }}
                    >
                      {item.iconText}
                    </Box>
                  </Fragment>
                )}
                <StyledText
                  mode={mode}
                  className="sidenavHoverShow"
                  style={{
                    fontSize: item.level === 3 && '12px', //0.9em
                  }}
                  sx={
                    item.level === 3
                      ? {
                          textAlign: 'justify',
                          paddingLeft: '0em',
                          paddingRight: '2em',
                        }
                      : null
                  }
                >
                  {item.name}
                </StyledText>

                <Box mx="auto" />

                {item.badge && (
                  <BadgeValue className="sidenavHoverShow">{item.badge.value}</BadgeValue>
                )}
              </ButtonBase>
            </NavLink>
          </InternalLink>
        );
      }
    });
  };

  return <div className="navigation">{renderLevels(items)}</div>;
};

export default React.memo(MatxVerticalNav);
