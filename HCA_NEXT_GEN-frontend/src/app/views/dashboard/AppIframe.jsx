import { styled } from '@mui/system';
import axios from 'axios';
import { useEffect, useRef, useState } from 'react';

import { Button, CircularProgress, Tooltip, Typography } from '@mui/material';
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

/**
 * Trigger a browser file download from a PDF blob returned by the backend.
 */
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

/** Strip the "Category | " prefix that some dashboard names include. */
function findDashName(str) {
  if (str?.includes('|')) return str.slice(str.indexOf('|') + 1).trim();
  return str || '';
}

/** Make a string safe to use as a filename (no slashes, colons, etc.). */
function toSafeFilename(name) {
  return name
    .replace(/[\\/:*?"<>|]/g, '') // remove chars illegal on Windows/Linux
    .replace(/\s+/g, '_')         // spaces → underscores
    .trim()
    || 'dashboard';
}

export default function AppIframe({ dashId = '', clientId = '', iframeUrl = '' }) {
  const [data, setData] = useState('');
  const [error, setError] = useState('');
  const [downloading, setDownloading] = useState(false);
  const currentUserPermission = useSelector((state) => state.userType.userIsA);
  const dashboardName = useSelector((state) => state.currentClient.client?.dashboard_name);

  const [loading, setLoading] = useState(false);
  const [iframeLoading, setIframeLoading] = useState(false);
  const authToken = getAccessToken();

  // Tracks the live embed URL — updated by Looker Studio postMessages when
  // the user applies filters/date changes inside the iframe, which change
  // the report's internal URL (with ?params=... appended).  Falls back to
  // the original `data` URL if Looker Studio never sends an update.
  const currentEmbedUrl = useRef('');

  const sendObj = (dId) =>
    dId
      ? { client_id: clientId, dashboard_id: dashId, flag: 0, permission: currentUserPermission }
      : { client_id: clientId, dashboard_id: null, flag: 1, permission: currentUserPermission };

  // Whenever the base embed URL changes (new dashboard selected), reset the
  // live URL tracker so the previous report's filter state is not carried over.
  useEffect(() => {
    currentEmbedUrl.current = data;
  }, [data]);

  // Looker Studio posts messages to the parent frame during its lifecycle and
  // when the report URL changes (e.g. after filter / date-range interaction).
  // We listen for any message that carries a Looker Studio URL and store it so
  // the download uses the current filtered state rather than the original URL.
  useEffect(() => {
    const handleMessage = (event) => {
      try {
        const msg = event.data;
        if (!msg || typeof msg !== 'object') return;

        // Looker Studio wraps its messages in a "datastudio" envelope.
        // Unwrap one level if present so we can inspect the payload uniformly.
        const payload = msg.type === 'datastudio' && msg.message ? msg.message : msg;

        // Extract a URL from the most common field names used by Looker Studio.
        const candidate =
          payload.url ||
          payload.reportUrl ||
          payload.embedUrl ||
          payload.currentUrl ||
          payload.value;

        if (
          candidate &&
          typeof candidate === 'string' &&
          candidate.includes('lookerstudio.google.com')
        ) {
          currentEmbedUrl.current = candidate;
        }
      } catch {
        // Ignore malformed messages from other origins.
      }
    };

    window.addEventListener('message', handleMessage);
    return () => window.removeEventListener('message', handleMessage);
  }, []);

  useEffect(() => {
    if (iframeUrl) {
      setIframeLoading(true);
      setData(iframeUrl);
      setError('');
      return;
    }

    if (!clientId || !dashId) return;

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
  }, [clientId, dashId, iframeUrl, authToken, currentUserPermission]);

  const handleDownload = async () => {
    // Prefer the live URL captured from Looker Studio postMessages (which
    // includes any ?params=... filter state the user applied), falling back
    // to the original base URL if no update has been received yet.
    const urlToDownload = currentEmbedUrl.current || data;
    if (!urlToDownload) return;
    setDownloading(true);
    try {
      const response = await axios.post(
        commonConfig.urls.downloadStudioPdf,
        { embed_url: urlToDownload },
        {
          headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' },
          responseType: 'blob',
          timeout: 120000, // 2 min — Looker Studio can take time for complex reports
        }
      );

      const filename = toSafeFilename(findDashName(dashboardName)) + '.pdf';
      triggerBlobDownload(response.data, filename);
    } catch (err) {
      // Axios wraps non-2xx blob responses; read the error JSON from the blob
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
        <Tooltip title="Export this dashboard as PDF via Looker Studio">
          <span>
            <Button
              size="small"
              variant="outlined"
              startIcon={
                downloading ? (
                  <CircularProgress size={14} thickness={5} color="inherit" />
                ) : (
                  <DownloadIcon />
                )
              }
              onClick={handleDownload}
              disabled={downloading}
              sx={{ textTransform: 'none', fontSize: '12px', minWidth: 148 }}
            >
              {downloading ? 'Preparing PDF…' : 'Download PDF'}
            </Button>
          </span>
        </Tooltip>
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
