import {
  Box,
  Button,
  Card,
  Chip,
  CircularProgress,
  Divider,
  Icon,
  IconButton,
  styled,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
  TextField,
  Tooltip,
  Typography,
} from '@mui/material';
import axios from 'axios';
import { useEffect, useState } from 'react';
import SnackbarUtils from 'SnackbarUtils';
import commonConfig from 'app/components/commonConfig';
import { getAccessToken } from 'app/utils/utils';

// Extract a Looker Studio report ID from a full URL or bare ID
// URL format: https://lookerstudio.google.com/reporting/<REPORT_ID>/page/...
const extractReportId = (input = '') => {
  const match = input.match(/reporting\/([a-zA-Z0-9_-]+)/);
  return match ? match[1] : input.trim();
};

const buildReportUrl = (id) =>
  id ? `https://lookerstudio.google.com/reporting/${id}` : '';

const Container = styled(Box)(({ theme }) => ({
  padding: theme.spacing(3),
  maxWidth: 900,
}));

export default function StudioTemplateConfig() {
  const authToken = getAccessToken();

  const [templateInput, setTemplateInput] = useState('');
  const [savedTemplate, setSavedTemplate] = useState(null); // { report_id, report_url, updated_at }
  const [loadingTemplate, setLoadingTemplate] = useState(true);
  const [saving, setSaving] = useState(false);

  const [clientReports, setClientReports] = useState([]);
  const [loadingReports, setLoadingReports] = useState(true);

  // ------------------------------------------------------------------
  // Load current master template
  // ------------------------------------------------------------------
  const fetchTemplate = async () => {
    try {
      setLoadingTemplate(true);
      const resp = await axios.get(commonConfig.urls.studioPhmTemplate, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      if (resp?.data?.Response) {
        setSavedTemplate(resp.data.Response);
        setTemplateInput(resp.data.Response.report_url || resp.data.Response.report_id || '');
      }
    } catch {
      // No template saved yet — that's fine
    } finally {
      setLoadingTemplate(false);
    }
  };

  // ------------------------------------------------------------------
  // Load client-level generated reports that have a studio_report_url
  // ------------------------------------------------------------------
  const fetchClientReports = async () => {
    try {
      setLoadingReports(true);
      const resp = await axios.get(commonConfig.urls.phmAutomationReportList, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      if (resp?.data?.Response) {
        const withStudio = resp.data.Response.filter((r) => r.studio_report_url);
        setClientReports(withStudio);
      }
    } catch {
      // silently ignore
    } finally {
      setLoadingReports(false);
    }
  };

  useEffect(() => {
    fetchTemplate();
    fetchClientReports();
  }, []);

  // ------------------------------------------------------------------
  // Save master template
  // ------------------------------------------------------------------
  const handleSave = async () => {
    const reportId = extractReportId(templateInput);
    if (!reportId) {
      SnackbarUtils.error('Please enter a valid Looker Studio report URL or ID');
      return;
    }
    try {
      setSaving(true);
      const resp = await axios.post(
        commonConfig.urls.studioPhmTemplate,
        { report_id: reportId, report_url: buildReportUrl(reportId) },
        { headers: { Authorization: `Bearer ${authToken}` } }
      );
      if (resp?.data?.Status === 'Success') {
        SnackbarUtils.success('Master template saved');
        fetchTemplate();
      } else {
        SnackbarUtils.error(resp?.data?.Message || 'Failed to save template');
      }
    } catch (e) {
      SnackbarUtils.error(e?.message || 'Something went wrong');
    } finally {
      setSaving(false);
    }
  };

  return (
    <Container>
      {/* ── Master Template Section ── */}
      <Card sx={{ p: 3, mb: 4 }}>
        <Typography variant="h6" sx={{ mb: 1 }}>
          Master PHM Template
        </Typography>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
          This Looker Studio report is copied for every new PHM client. Paste the full report URL
          or just the report ID. The backend will copy it and rebind the data source to the
          client's BigQuery schema on each PHM generation request.
        </Typography>

        {loadingTemplate ? (
          <CircularProgress size={24} />
        ) : (
          <>
            {savedTemplate && (
              <Box
                sx={{
                  display: 'flex',
                  alignItems: 'center',
                  gap: 1,
                  mb: 2,
                  p: 1.5,
                  bgcolor: 'success.50',
                  borderRadius: 1,
                  border: '1px solid',
                  borderColor: 'success.light',
                }}
              >
                <Icon color="success" fontSize="small">check_circle</Icon>
                <Typography variant="body2" sx={{ flexGrow: 1 }}>
                  Current: <strong>{savedTemplate.report_id}</strong>
                  {savedTemplate.updated_at && (
                    <span style={{ color: '#888', marginLeft: 8 }}>
                      (saved {savedTemplate.updated_at})
                    </span>
                  )}
                </Typography>
                <Tooltip title="Open master template in Looker Studio">
                  <IconButton
                    size="small"
                    component="a"
                    href={buildReportUrl(savedTemplate.report_id)}
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    <Icon fontSize="small">open_in_new</Icon>
                  </IconButton>
                </Tooltip>
              </Box>
            )}

            <Box sx={{ display: 'flex', gap: 2, alignItems: 'flex-start' }}>
              <TextField
                label="Looker Studio Report URL or ID"
                placeholder="https://lookerstudio.google.com/reporting/XXXXXX  or  XXXXXX"
                value={templateInput}
                onChange={(e) => setTemplateInput(e.target.value)}
                fullWidth
                size="small"
                variant="outlined"
                InputLabelProps={{ shrink: true }}
              />
              <Button
                variant="contained"
                onClick={handleSave}
                disabled={saving || !templateInput.trim()}
                sx={{ whiteSpace: 'nowrap', minWidth: 120 }}
              >
                {saving ? <CircularProgress size={18} color="inherit" /> : 'Save Template'}
              </Button>
            </Box>

            <Typography variant="caption" color="text.secondary" sx={{ mt: 1, display: 'block' }}>
              The report ID will be extracted automatically from the URL.
              Example ID: <code>1BxCEF3a…</code>
            </Typography>
          </>
        )}
      </Card>

      <Divider sx={{ mb: 3 }} />

      {/* ── Per-Client Generated Reports ── */}
      <Typography variant="h6" sx={{ mb: 1 }}>
        Client Reports in Looker Studio
      </Typography>
      <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
        PHM reports that have been successfully generated and have a Looker Studio link.
      </Typography>

      {loadingReports ? (
        <CircularProgress size={24} />
      ) : clientReports.length === 0 ? (
        <Typography color="text.secondary" variant="body2">
          No generated reports with Looker Studio links yet.
        </Typography>
      ) : (
        <Table size="small">
          <TableHead>
            <TableRow>
              <TableCell>Report Name</TableCell>
              <TableCell>Client</TableCell>
              <TableCell>Medical Dates</TableCell>
              <TableCell>Pharmacy Dates</TableCell>
              <TableCell>Status</TableCell>
              <TableCell>Studio Report</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {clientReports.map((r, i) => (
              <TableRow key={i}>
                <TableCell>{r.report_name || r.name}</TableCell>
                <TableCell>{r.folder_name}</TableCell>
                <TableCell sx={{ fontSize: '0.78rem', whiteSpace: 'nowrap' }}>
                  {r.medical_start_date && r.medical_end_date
                    ? `${r.medical_start_date} → ${r.medical_end_date}`
                    : '—'}
                </TableCell>
                <TableCell sx={{ fontSize: '0.78rem', whiteSpace: 'nowrap' }}>
                  {r.pharmacy_start_date && r.pharmacy_end_date
                    ? `${r.pharmacy_start_date} → ${r.pharmacy_end_date}`
                    : '—'}
                </TableCell>
                <TableCell>
                  <Chip label="Done" color="success" size="small" />
                </TableCell>
                <TableCell>
                  <Tooltip title="Open in Looker Studio">
                    <Button
                      size="small"
                      variant="outlined"
                      endIcon={<Icon fontSize="small">open_in_new</Icon>}
                      component="a"
                      href={r.studio_report_url}
                      target="_blank"
                      rel="noopener noreferrer"
                    >
                      Open
                    </Button>
                  </Tooltip>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      )}
    </Container>
  );
}
