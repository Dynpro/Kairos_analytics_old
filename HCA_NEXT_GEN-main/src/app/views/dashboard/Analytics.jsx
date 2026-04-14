import { Icon, styled, useTheme } from '@mui/material';
import AppIframe from './AppIframe';

import { useSelector } from 'react-redux';
import { useLocation } from 'react-router-dom';

const MenuPathRoot = styled('div')(() => ({
  display: 'flex',
  flexWrap: 'wrap',
  alignItems: 'center',
  backgroundColor: 'none',
  padding: '5px 0 5px 10px',
}));

const MenuPathName = styled('h4')(({ theme }) => ({
  margin: 0,
  fontSize: '16px',
  paddingBottom: '1px',
  verticalAlign: 'middle',
  textTransform: 'capitalize',
  color: theme.palette.text.secondary,
}));

const ContentBox = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
}));

const findDashName = (str) => {
  if (str?.includes('|')) {
    return str.slice(str.indexOf('|') + 1).trim();
  }
  return str;
};

const PrintMenuPath = ({ items = [], nextIconColor = 'red' }) => {
  const recdItems = [...new Set(items)];
  if (recdItems.filter(Boolean).length <= 1) return null;
  const str = recdItems
    .filter(Boolean)
    .map((item) => <MenuPathName key={item}>{item}</MenuPathName>)
    .reduce((accu, elem, index) => {
      return accu === null
        ? [elem]
        : [
            ...accu,
            <Icon key={`icon-${index}`} sx={{ color: nextIconColor }}>
              navigate_next
            </Icon>,
            elem,
          ];
    }, null);
  return <MenuPathRoot>{str}</MenuPathRoot>;
};

const Analytics = () => {
  const theme = useTheme();
  const hint = theme.palette.text.hint;
  const location = useLocation();
  const currentClient = useSelector((state) => state.currentClient.client);

  const getClientId = () => {
    if (location.state && location.state.client_id) {
      return location.state.client_id;
    } else if (currentClient?.client_id) {
      return currentClient.client_id;
    } else {
      return;
    }
  };

  const getDashId = () => {
    if (location.state && location.state.dash_id) {
      return location.state.dash_id;
    } else {
      return;
    }
  };

  const getIframeUrl = () => {
    return location.state?.iframe_url || '';
  };

  const renderAppIframe = () => {
    const clientId = getClientId();
    const dashId = getDashId();
    const iframeUrl = getIframeUrl();

    return <AppIframe clientId={clientId} dashId={dashId} iframeUrl={iframeUrl} />;
  };

  return (
    <ContentBox className="analytics" style={{ margin: '0' }}>
      <PrintMenuPath
        nextIconColor={hint}
        items={[
          currentClient.subCategory_name,
          currentClient.client_name,
          currentClient.folder_name,
          findDashName(currentClient.dashboard_name),
        ]}
      />

      {renderAppIframe()}
    </ContentBox>
  );
};

export default Analytics;
