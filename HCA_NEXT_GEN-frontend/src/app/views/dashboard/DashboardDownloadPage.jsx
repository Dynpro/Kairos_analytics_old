import {
  Accordion,
  AccordionDetails,
  AccordionSummary,
  Box,
  Button,
  Chip,
  CircularProgress,
  InputAdornment,
  TextField,
  Tooltip,
  Typography,
} from '@mui/material';
import DownloadIcon from '@mui/icons-material/Download';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import SearchIcon from '@mui/icons-material/Search';
import axios from 'axios';
import commonConfig from 'app/components/commonConfig';
import { getAccessToken } from 'app/utils/utils';
import SnackbarUtils from 'SnackbarUtils';
import { useEffect, useMemo, useState } from 'react';

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

export default function DashboardDownloadPage() {
  const [dashboards, setDashboards] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  // dash_id of the row currently being downloaded (only that button shows loading)
  const [preparingId, setPreparingId] = useState(null);
  const authToken = getAccessToken();

  useEffect(() => {
    const fetchDashboards = async () => {
      try {
        setLoading(true);
        const resp = await axios.post(
          commonConfig.urls.getLookerDashboard,
          {},
          { headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' } }
        );

        if (resp?.data?.Response) {
          const flat = [];
          Object.entries(resp.data.Response).forEach(([folderKey, items]) => {
            if (Array.isArray(items)) {
              items.forEach((d) => {
                if (d?.dash_id) {
                  flat.push({
                    ...d,
                    folder: d.folder_name || folderKey,
                    embed_url: d.embed_url || d.url || '',
                  });
                }
              });
            }
          });
          setDashboards(flat);
        }
      } catch (e) {
        SnackbarUtils.error(e?.message || 'Failed to load dashboards');
      } finally {
        setLoading(false);
      }
    };

    fetchDashboards();
  }, [authToken]);

  const filtered = useMemo(() => {
    if (!search.trim()) return dashboards;
    const q = search.toLowerCase();
    return dashboards.filter(
      (d) =>
        (d.title || '').toLowerCase().includes(q) ||
        (d.folder || '').toLowerCase().includes(q) ||
        (d.client_name || '').toLowerCase().includes(q)
    );
  }, [dashboards, search]);

  const grouped = useMemo(() => {
    const map = new Map();
    filtered.forEach((d) => {
      const key = d.folder || 'Other';
      if (!map.has(key)) map.set(key, []);
      map.get(key).push(d);
    });
    return Array.from(map.entries());
  }, [filtered]);

  const handleDownload = async (d) => {
    if (!d.embed_url) return;
    setPreparingId(d.dash_id);
    try {
      const response = await axios.post(
        commonConfig.urls.downloadStudioPdf,
        { embed_url: d.embed_url },
        {
          headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' },
          responseType: 'blob',
          timeout: 120000,
        }
      );

      const filename = `${d.title || 'dashboard'}_${new Date().toISOString().slice(0, 10)}.pdf`;
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
      setPreparingId(null);
    }
  };

  return (
    <Box sx={{ p: 3, maxWidth: 900, mx: 'auto' }}>
      <Typography variant="h5" sx={{ mb: 0.5 }}>
        Download Dashboards
      </Typography>
      <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
        Click <strong>Download PDF</strong> — the server fetches the PDF from Looker Studio and
        delivers it directly to your browser.
      </Typography>

      <TextField
        size="small"
        placeholder="Search by name, folder or client…"
        value={search}
        onChange={(e) => setSearch(e.target.value)}
        sx={{ mb: 3, width: 360 }}
        InputProps={{
          startAdornment: (
            <InputAdornment position="start">
              <SearchIcon fontSize="small" />
            </InputAdornment>
          ),
        }}
      />

      {loading ? (
        <Box sx={{ display: 'flex', justifyContent: 'center', py: 10 }}>
          <CircularProgress />
        </Box>
      ) : grouped.length === 0 ? (
        <Typography color="text.secondary">No dashboards found.</Typography>
      ) : (
        grouped.map(([folder, items]) => (
          <Accordion key={folder} defaultExpanded disableGutters sx={{ mb: 1 }}>
            <AccordionSummary expandIcon={<ExpandMoreIcon />} sx={{ minHeight: 48 }}>
              <Typography fontWeight={600} sx={{ mr: 1 }}>
                {folder}
              </Typography>
              <Chip label={items.length} size="small" />
            </AccordionSummary>

            <AccordionDetails sx={{ p: 0 }}>
              {items.map((d) => {
                const isPreparing = preparingId === d.dash_id;
                return (
                  <Box
                    key={`${folder}-${d.dash_id}`}
                    sx={{
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'space-between',
                      px: 2,
                      py: 1.5,
                      borderTop: '1px solid',
                      borderColor: 'divider',
                      '&:hover': { bgcolor: 'action.hover' },
                    }}
                  >
                    <Box>
                      <Typography variant="body2" fontWeight={500}>
                        {d.title}
                      </Typography>
                      {d.client_name && (
                        <Typography variant="caption" color="text.secondary">
                          {d.client_name}
                        </Typography>
                      )}
                    </Box>

                    <Tooltip
                      title={
                        d.embed_url
                          ? 'PDF is prepared on the server and downloaded directly'
                          : 'No embed URL configured for this dashboard'
                      }
                    >
                      <span>
                        <Button
                          size="small"
                          variant="outlined"
                          startIcon={
                            isPreparing ? (
                              <CircularProgress size={14} thickness={5} color="inherit" />
                            ) : (
                              <DownloadIcon />
                            )
                          }
                          onClick={() => handleDownload(d)}
                          disabled={!d.embed_url || isPreparing}
                          sx={{ textTransform: 'none', minWidth: 148 }}
                        >
                          {isPreparing ? 'Preparing PDF…' : 'Download PDF'}
                        </Button>
                      </span>
                    </Tooltip>
                  </Box>
                );
              })}
            </AccordionDetails>
          </Accordion>
        ))
      )}
    </Box>
  );
}
