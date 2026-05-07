import {
  Box,
  Button,
  Chip,
  Dialog,
  DialogContent,
  Divider,
  Icon,
  IconButton,
  Step,
  StepContent,
  StepLabel,
  Stepper,
  Typography,
  Alert,
  styled,
} from '@mui/material';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import commonConfig from 'app/components/commonConfig';
import SnackbarUtils from 'SnackbarUtils';
import { useEffect, useState, useRef } from 'react';

const STEPS = [
  { label: 'Job Queued', statusThreshold: 0 },
  { label: 'Building Chart Links', statusThreshold: 1 },
  { label: 'Chart Links Ready', statusThreshold: 2 },
  { label: 'Generating Charts', statusThreshold: 3 },
  { label: 'Generating PDF', statusThreshold: 4 },
  { label: 'Complete', statusThreshold: 6 },
];

const STATUS_MAP = {
  0: 0,
  1: 1,
  2: 2,
  3: 3,
  4: 4,
  5: 5,
  6: 5,
  7: -1, // failed
};

const StyledDialog = styled(Dialog)(() => ({
  '& .MuiDialog-paper': {
    minWidth: 460,
    maxWidth: 560,
    borderRadius: 12,
    padding: '8px',
  },
}));

const MetaLabel = styled(Typography)(() => ({
  fontSize: '0.72rem',
  color: '#999',
  textTransform: 'uppercase',
  fontWeight: 600,
  marginBottom: 2,
}));

const MetaValue = styled(Typography)(() => ({
  fontSize: '0.9rem',
  fontWeight: 500,
  color: '#333',
}));

const formatDateRange = (start, end) => {
  if (!start && !end) return '—';
  const s = start ? String(start).substring(0, 10) : '?';
  const e = end ? String(end).substring(0, 10) : '?';
  return `${s} → ${e}`;
};

const frequencyLabel = (freq) => {
  if (typeof freq === 'string') return freq;
  switch (freq) {
    case 1: return 'Once';
    case 2: return 'Weekly';
    case 3: return 'Monthly';
    case 4: return 'Quarterly';
    default: return String(freq);
  }
};

const rawStatus = (report) => {
  const lg = report?.raw_looks_generated ?? report?.looks_generated;
  if (typeof lg === 'number') return lg;
  switch (String(lg).toLowerCase()) {
    case 'done': return 6;
    case 'failed': return 7;
    case 'started': return 0;
    case 'in progress': return 3;
    default: return 0;
  }
};

const AppReportStatusModal = ({ open, report, onClose, onDownload, fetchData }) => {
  const authToken = getAccessToken();
  const [liveReport, setLiveReport] = useState(report);
  const pollRef = useRef(null);

  const pollStatus = async () => {
    try {
      const res = await axios.get(
        `${commonConfig.urls.phmAutomationShowReport}/${report.report_id}`,
        { headers: { Authorization: `Bearer ${authToken}` } }
      );
      if (res?.data?.Response) {
        setLiveReport(res.data.Response);
        const lg = res.data.Response.looks_generated;
        if (lg === 6 || lg === 7) {
          clearInterval(pollRef.current);
          pollRef.current = null;
          if (fetchData) fetchData();
        }
      }
    } catch (_) {
      // ignore poll errors
    }
  };

  useEffect(() => {
    const status = rawStatus(liveReport);
    if (status >= 0 && status < 6) {
      pollRef.current = setInterval(pollStatus, 5000);
    }
    return () => {
      if (pollRef.current) clearInterval(pollRef.current);
    };
    // eslint-disable-next-line
  }, []);

  const handleRetry = async () => {
    try {
      const res = await axios.post(
        `${commonConfig.urls.phmAutomationRetryReport}/${report.report_id}`,
        {},
        { headers: { Authorization: `Bearer ${authToken}` } }
      );
      if (res?.data?.Status === 'Success') {
        SnackbarUtils.success(res.data.Message);
        setLiveReport((prev) => ({ ...prev, looks_generated: 0, file_path: null }));
        if (fetchData) fetchData();
        // restart polling
        if (pollRef.current) clearInterval(pollRef.current);
        pollRef.current = setInterval(pollStatus, 5000);
      }
    } catch (err) {
      SnackbarUtils.error(err?.message || 'Retry failed');
    }
  };

  const status = rawStatus(liveReport);
  const activeStep = STATUS_MAP[status] ?? 0;
  const isFailed = status === 7;
  const isComplete = status === 6;

  return (
    <StyledDialog open={open} onClose={onClose}>
      <DialogContent sx={{ p: 3 }}>
        {/* Header */}
        <Box display="flex" justifyContent="space-between" alignItems="flex-start">
          <Box>
            <Typography variant="h5" fontWeight={700}>
              {liveReport?.name || liveReport?.report_name || 'Report'}
            </Typography>
            <Typography variant="body2" color="textSecondary" sx={{ mt: 0.5 }}>
              {liveReport?.folder_name || ''}
            </Typography>
          </Box>
          <IconButton onClick={onClose} size="small">
            <Icon>close</Icon>
          </IconButton>
        </Box>

        <Divider sx={{ my: 2 }} />

        {/* Metadata row */}
        <Box display="flex" gap={4} mb={2}>
          <Box>
            <MetaLabel>Medical Data</MetaLabel>
            <MetaValue>
              {formatDateRange(liveReport?.medical_start_date, liveReport?.medical_end_date)}
            </MetaValue>
          </Box>
          <Box>
            <MetaLabel>Pharmacy Data</MetaLabel>
            <MetaValue>
              {formatDateRange(liveReport?.pharmacy_start_date, liveReport?.pharmacy_end_date)}
            </MetaValue>
          </Box>
          <Box>
            <MetaLabel>Reporting Year</MetaLabel>
            <MetaValue>{liveReport?.reporting_year || '—'}</MetaValue>
          </Box>
          <Box>
            <MetaLabel>Frequency</MetaLabel>
            <MetaValue>{frequencyLabel(liveReport?.frequency)}</MetaValue>
          </Box>
        </Box>

        <Divider sx={{ mb: 2 }} />

        {/* Status alert */}
        {isComplete && (
          <Alert severity="success" icon={<CheckCircleIcon />} sx={{ mb: 2 }}>
            Report generated successfully.
          </Alert>
        )}
        {isFailed && (
          <Alert severity="error" sx={{ mb: 2 }}>
            Report generation failed.
            <Button size="small" onClick={handleRetry} sx={{ ml: 2 }}>
              Retry
            </Button>
          </Alert>
        )}

        {/* Stepper */}
        <Stepper activeStep={isFailed ? -1 : activeStep} orientation="vertical">
          {STEPS.map((step, index) => {
            const done = !isFailed && activeStep >= index;
            const current = !isFailed && activeStep === index && !isComplete;
            return (
              <Step key={step.label} completed={done && !current}>
                <StepLabel
                  StepIconComponent={() =>
                    done ? (
                      <CheckCircleIcon sx={{ color: '#2e7d32', fontSize: 28 }} />
                    ) : (
                      <CheckCircleIcon sx={{ color: '#ccc', fontSize: 28 }} />
                    )
                  }
                >
                  <Box display="flex" alignItems="center" gap={1}>
                    <Typography fontWeight={600} sx={{ fontSize: '0.95rem' }}>
                      {step.label}
                    </Typography>
                    {done && !current && (
                      <Chip
                        label="Done"
                        size="small"
                        sx={{
                          bgcolor: '#2e7d32',
                          color: '#fff',
                          fontWeight: 600,
                          height: 20,
                          fontSize: '0.7rem',
                        }}
                      />
                    )}
                    {current && (
                      <Chip
                        label="In progress"
                        size="small"
                        sx={{
                          bgcolor: '#ed6c02',
                          color: '#fff',
                          fontWeight: 600,
                          height: 20,
                          fontSize: '0.7rem',
                        }}
                      />
                    )}
                  </Box>
                </StepLabel>
                {index === STEPS.length - 1 && isComplete && (
                  <StepContent>
                    <Typography variant="body2" color="textSecondary" sx={{ mt: 1 }}>
                      Report is ready. PDF is available for download.
                    </Typography>
                  </StepContent>
                )}
              </Step>
            );
          })}
        </Stepper>

        {/* Download button */}
        {isComplete && liveReport?.file_path && (
          <Box mt={3}>
            <Button
              variant="outlined"
              color="primary"
              endIcon={<Icon>download</Icon>}
              onClick={() => onDownload(liveReport.file_path)}
              sx={{
                textTransform: 'none',
                fontWeight: 600,
                borderRadius: 1,
                px: 3,
                borderColor: '#1976d2',
                '&:hover': {
                  borderColor: '#115293',
                  backgroundColor: 'rgba(25, 118, 210, 0.04)',
                },
              }}
            >
              Download PDF
            </Button>
          </Box>
        )}
      </DialogContent>
    </StyledDialog>
  );
};

export default AppReportStatusModal;
