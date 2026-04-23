import { styled } from '@mui/system';
import axios from 'axios';
import { useEffect, useRef, useState } from 'react';

import { Box, Button, CircularProgress, Popover, Tooltip, Typography } from '@mui/material';
import DownloadIcon from '@mui/icons-material/Download';
import SnackbarUtils from 'SnackbarUtils';
import { MatxLoading } from 'app/components';
import commonConfig from 'app/components/commonConfig';
import { getAccessToken } from 'app/utils/utils';
import { useSelector } from 'react-redux';

const ERROR_MSG = 'Error fetching Dashboard. Contact Admin';

const LoadingDiv = styled('div')(() => ({
  paddingTop: '25%',
}));

const DashboardWrapper = styled('div')(() => ({
  position: 'relative',
  width: '100%',
}));

const ToolbarRow = styled('div')(() => ({
  display: 'flex',
  justifyContent: 'flex-end',
  alignItems: 'center',
  padding: '4px 8px',
  gap: '8px',
}));

const DashboardDiv = styled('div')(() => ({
  position: 'relative',
  overflow: 'hidden',
  width: '100%',
  paddingTop: '56.25%',
  height: '100vh',
}));

const Iframe = styled('iframe')(() => ({
  position: 'absolute',
  top: 0,
  left: 0,
  bottom: 0,
  right: 0,
  width: '100%',
  height: '95%',
}));

const IframeOverlay = styled('div')(() => ({
  position: 'absolute',
  top: 0,
  left: 0,
  right: 0,
  bottom: 0,
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  backgroundColor: '#f5f5f5',
  zIndex: 10,
  gap: '16px',
}));

const ErrorContainer = styled('div')(() => ({
  display: 'flex',
  justifyContent: 'center',
  marginTop: '20px',
}));

function triggerBlobDownload(blob, filename) {
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

function findDashName(str) {
  if (str?.includes('|')) return str.slice(str.indexOf('|') + 1).trim();
  return str || '';
}

function toSafeFilename(name) {
  return name
    .replace(/[\\/:*?"<>|]/g, '')
    .replace(/\s+/g, '_')
    .trim()
    || 'dashboard';
}

export default function AppIframe({ dashId = '', clientId = '', iframeUrl = '' }) {
  const [data, setData] = useState('');
  const [error, setError] = useState('');
  const [downloading, setDownloading] = useState(false);
  const [anchorEl, setAnchorEl] = useState(null);
  const currentUserPermission = useSelector((state) => state.userType.userIsA);
  const dashboardName = useSelector((state) => state.currentClient.client?.dashboard_name);
  const preloadedUrls = useSelector((state) => state.userAccess.preloadedUrls);

  const [loading, setLoading] = useState(false);
  const [iframeLoading, setIframeLoading] = useState(false);
  const authToken = getAccessToken();

  const iframeRef = useRef(null);

  const sendObj = (dId) =>
    dId
      ? { client_id: clientId, dashboard_id: dashId, flag: 0, permission: currentUserPermission }
      : { client_id: clientId, dashboard_id: null, flag: 1, permission: currentUserPermission };

  useEffect(() => {
    if (iframeUrl) {
      setIframeLoading(true);
      setData(iframeUrl);
      setError('');
      return;
    }

    if (!clientId || !dashId) return;

    const cacheKey = `${clientId}_${dashId}`;
    const cachedUrl = preloadedUrls?.[cacheKey];
    if (cachedUrl) {
      setError('');
      setIframeLoading(true);
      setData(cachedUrl);
      return;
    }

    const fetchData = async () => {
      try {
        setError('');
        setLoading(true);
        const response = await axios.post(commonConfig.urls.getLookerDashboard, sendObj(dashId), {
          headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' },
        });
        setLoading(false);

        if (response?.data?.Response) {
          const allDashboards = [];
          Object.values(response.data.Response).forEach((folderDashboards) => {
            allDashboards.push(...folderDashboards);
          });

          const targetDashboard = allDashboards.find(
            (d) => String(d.dash_id) === String(dashId)
          );

          const url = targetDashboard?.embed_url || targetDashboard?.url;
          if (url) {
            setIframeLoading(true);
            return setData(url);
          }

          return setError(
            `Dashboard ID ${dashId} not found in database. Available IDs: ${allDashboards
              .map((d) => d.dash_id)
              .join(', ')}`
          );
        }

        if (response?.data?.url) return setData(response.data.url);
        if (response?.data?.LicenceMessage) return setError(response?.data?.LicenceMessage);
        return setError(ERROR_MSG);
      } catch (err) {
        setLoading(false);
        setError(ERROR_MSG);
        SnackbarUtils.error(err?.message || ERROR_MSG);
      }
    };
    fetchData();
  }, [clientId, dashId, iframeUrl, authToken, currentUserPermission, preloadedUrls]);

  const handleDownload = async () => {
    const urlToDownload = data;
    if (!urlToDownload) return;
    setDownloading(true);
    try {
      const response = await axios.post(
        commonConfig.urls.downloadStudioPdf,
        { embed_url: urlToDownload },
        {
          headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' },
          responseType: 'blob',
          timeout: 120000,
        }
      );

      const filename = toSafeFilename(findDashName(dashboardName)) + '.pdf';
      triggerBlobDownload(response.data, filename);
    } catch (err) {
      if (err.response?.data instanceof Blob) {
        const text = await err.response.data.text();
        try {
          const json = JSON.parse(text);
          SnackbarUtils.error(json.error || 'Failed to generate PDF.');
        } catch {
          SnackbarUtils.error('Failed to generate PDF.');
        }
      } else {
        SnackbarUtils.error(err?.message || 'Failed to generate PDF.');
      }
    } finally {
      setDownloading(false);
    }
  };

  if (loading) {
    return (
      <LoadingDiv>
        <MatxLoading />
      </LoadingDiv>
    );
  }

  if (error) {
    return (
      <ErrorContainer>
        <Typography variant="h5">{error}</Typography>
      </ErrorContainer>
    );
  }

  if (!data) return null;

  return (
    <DashboardWrapper>
      <ToolbarRow>
        <Button
          size="small"
          variant="outlined"
          startIcon={<DownloadIcon />}
          onClick={(e) => setAnchorEl(e.currentTarget)}
          sx={{ textTransform: 'none', fontSize: '12px', minWidth: 148 }}
        >
          Download PDF
        </Button>

        <Popover
          open={Boolean(anchorEl)}
          anchorEl={anchorEl}
          onClose={() => setAnchorEl(null)}
          anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
          transformOrigin={{ vertical: 'top', horizontal: 'right' }}
          PaperProps={{ sx: { p: 2, maxWidth: 300 } }}
        >
          <Typography variant="subtitle2" sx={{ mb: 1 }}>
            How to download this dashboard
          </Typography>

          <Box component="ol" sx={{ m: 0, pl: 2 }}>
            <Box component="li" sx={{ mb: 0.5 }}>
              <Typography variant="body2">
                <strong>With filters applied</strong> — right-click anywhere inside the dashboard
                and select <strong>"Download page as PDF"</strong>.
              </Typography>
            </Box>
            <Box component="li">
              <Typography variant="body2">
                <strong>Full dashboard (no filters)</strong> — click the button below to download
                via server.
              </Typography>
            </Box>
          </Box>

          <Button
            size="small"
            variant="contained"
            fullWidth
            startIcon={
              downloading ? (
                <CircularProgress size={14} thickness={5} color="inherit" />
              ) : (
                <DownloadIcon />
              )
            }
            onClick={() => { setAnchorEl(null); handleDownload(); }}
            disabled={downloading}
            sx={{ textTransform: 'none', mt: 1.5 }}
          >
            {downloading ? 'Preparing PDF…' : 'Download without filters'}
          </Button>
        </Popover>
      </ToolbarRow>

      <DashboardDiv>
        {iframeLoading && (
          <IframeOverlay>
            <CircularProgress size={48} thickness={4} />
            <Typography variant="body2" color="text.secondary">
              Loading dashboard…
            </Typography>
          </IframeOverlay>
        )}
        <Iframe
          ref={iframeRef}
          title="Dashboard"
          frameBorder={0}
          src={data}
          allowFullScreen
          onLoad={() => setIframeLoading(false)}
        />
      </DashboardDiv>
    </DashboardWrapper>
  );
}
