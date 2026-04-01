/* eslint-disable */
import { Box, Icon, IconButton, styled, Tooltip, useTheme, Menu, MenuItem, ListItemIcon, ListItemText, Typography } from '@mui/material';
import { MoreVert, Refresh, FileDownload, FilterAltOff } from '@mui/icons-material';
import AppIframe from './AppIframe';
import { useState, useEffect } from 'react';
import axios from 'axios';
import SnackbarUtils from 'SnackbarUtils';
import { MatxLoading } from 'app/components';
import commonConfig from 'app/components/commonConfig';
import { getAccessToken } from 'app/utils/utils';
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

  const [refreshKey, setRefreshKey] = useState(0);
  const [anchorEl, setAnchorEl] = useState(null);
  const open = Boolean(anchorEl);

  const handleOpenMenu = (event) => setAnchorEl(event.currentTarget);
  const handleCloseMenu = () => setAnchorEl(null);

  const handleManualRefresh = () => {
    setRefreshKey((prev) => prev + 1);
    handleCloseMenu();
  };

  const handleDownload = async () => {
    const dashUrl = document.querySelector('iframe')?.src;
    const clientId = currentClient?.client_id || currentClient?.id;

    if (clientId) {
      // Use the backend's PDF generation method as requested
      try {
        SnackbarUtils.info('Preparing your PDF report. This may take a few moments...');
        const response = await axios.post(
          `${commonConfig.urls.baseURL}/direct_download_pdf`,
          { client_id: clientId },
          {
            headers: { Authorization: `Bearer ${getAccessToken()}` },
            responseType: 'blob',
          }
        );

        if (response.data.type === 'application/pdf') {
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const a = document.createElement('a');
          a.href = url;
          a.download = `${findDashName(currentClient.dashboard_name) || 'Dashboard'}.pdf`;
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(url);
          document.body.removeChild(a);
          SnackbarUtils.success('Download started');
        } else {
          // If response is not a PDF, it's likely a JSON error blob
          const reader = new FileReader();
          reader.onload = () => {
            const errorMsg = JSON.parse(reader.result)?.Message || 'PDF generation error';
            SnackbarUtils.error(errorMsg);
            triggerFallbackDownload(dashUrl);
          };
          reader.readAsText(response.data);
        }
      } catch (error) {
        console.error('Backend PDF generation failed:', error);
        triggerFallbackDownload(dashUrl);
      }
    } else if (dashUrl) {
      triggerFallbackDownload(dashUrl);
    } else {
      SnackbarUtils.error('Dashboard URL not found');
    }
    handleCloseMenu();
  };

  const triggerFallbackDownload = (dashUrl) => {
    if (!dashUrl) {
      SnackbarUtils.error('Dashboard URL not found');
      return;
    }
    const dashIdMatch = dashUrl.match(/reporting\/([^\/\?]+)/);
    if (dashIdMatch && dashIdMatch[1]) {
      window.open(`https://lookerstudio.google.com/reporting/${dashIdMatch[1]}/downloadPDF`, '_blank');
    } else {
      window.open(dashUrl.replace('/embed', ''), '_blank');
    }
  };

  const handleResetFilters = () => {
    // Note: Reset filters is usually handled within the Looker Studio report header.
    // If there's a postMessage API, we'd use it here.
    SnackbarUtils.info('Reset filters is available in the report header');
    handleCloseMenu();
  };

  useEffect(() => {
    const handleKeyDown = (event) => {
      // Clear cache and refresh: Shift + Ctrl + Enter
      if (event.shiftKey && (event.ctrlKey || event.metaKey) && event.key === 'Enter') {
        event.preventDefault();
        handleManualRefresh();
      }
      // Download: Alt + Shift + D
      if (event.altKey && event.shiftKey && event.key.toLowerCase() === 'd') {
        event.preventDefault();
        handleDownload();
      }
      // Reset filters: Ctrl + Alt + R
      if ((event.ctrlKey || event.metaKey) && event.altKey && event.key.toLowerCase() === 'r') {
        event.preventDefault();
        handleResetFilters();
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, []);

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
    } else if (currentClient?.dash_id) {
      return currentClient.dash_id;
    } else {
      return;
    }
  };

  const renderAppIframe = () => {
    const clientId = getClientId();
    const dashId = getDashId();

    return <AppIframe key={refreshKey} clientId={clientId} dashId={dashId} />;
  };

  // Re-compilation trigger comment
  return (
    <ContentBox className="analytics" style={{ margin: '0' }}>
      <Box display="flex" justifyContent="space-between" alignItems="center">
        <Box className="breadcrumb">
          <PrintMenuPath
            nextIconColor={hint}
            items={[
              currentClient.subCategory_name?.toLowerCase().includes('hospital')
                ? null
                : currentClient.subCategory_name,
              currentClient.client_name,
              currentClient.folder_name,
              findDashName(currentClient.dashboard_name),
            ]}
          />
        </Box>
        <Box display="flex" gap={1} pr={2} alignItems="center" className="dashboard-actions">
          <Tooltip title="Clear cache and refresh">
            <IconButton size="small" onClick={handleManualRefresh}>
              <Refresh fontSize="small" />
            </IconButton>
          </Tooltip>

          <Tooltip title="Reset filters">
            <IconButton size="small" onClick={handleResetFilters}>
              <FilterAltOff fontSize="small" />
            </IconButton>
          </Tooltip>

          <Tooltip title="Download as PDF">
            <IconButton size="small" onClick={handleDownload}>
              <FileDownload fontSize="small" />
            </IconButton>
          </Tooltip>

          <IconButton size="small" onClick={handleOpenMenu} sx={{ ml: 1 }}>
            <MoreVert fontSize="small" />
          </IconButton>
          <Menu anchorEl={anchorEl} open={open} onClose={handleCloseMenu}>
            <MenuItem onClick={handleManualRefresh}>
              <ListItemIcon>
                <Refresh fontSize="small" />
              </ListItemIcon>
              <ListItemText>Refresh</ListItemText>
              <Typography variant="body2" color="text.secondary" sx={{ ml: 3 }}>
                ⇧ ctrl ↵
              </Typography>
            </MenuItem>
            <MenuItem onClick={handleDownload}>
              <ListItemIcon>
                <FileDownload fontSize="small" />
              </ListItemIcon>
              <ListItemText>Download</ListItemText>
              <Typography variant="body2" color="text.secondary" sx={{ ml: 3 }}>
                alt ⇧ D
              </Typography>
            </MenuItem>
            <MenuItem onClick={handleResetFilters}>
              <ListItemIcon>
                <FilterAltOff fontSize="small" />
              </ListItemIcon>
              <ListItemText>Reset Filters</ListItemText>
              <Typography variant="body2" color="text.secondary" sx={{ ml: 3 }}>
                ctrl alt R
              </Typography>
            </MenuItem>
          </Menu>

          <Tooltip title="Open in New Tab">
            <IconButton size="small" onClick={() => {
              const dashUrl = document.querySelector('iframe')?.src;
              if (dashUrl) window.open(dashUrl.replace('/embed', ''), '_blank');
              else SnackbarUtils.error('Dashboard URL not found');
            }}>
              <Icon fontSize="small">open_in_new</Icon>
            </IconButton>
          </Tooltip>
        </Box>
      </Box>

      {renderAppIframe()}
    </ContentBox>
  );
};

export default Analytics;
