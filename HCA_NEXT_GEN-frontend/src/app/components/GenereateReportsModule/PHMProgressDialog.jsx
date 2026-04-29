import { useState, useEffect, useRef } from 'react';
import {
  Box,
  Button,
  Chip,
  Dialog,
  DialogContent,
  DialogTitle,
  Divider,
  Icon,
  IconButton,
  Step,
  StepContent,
  StepLabel,
  Stepper,
  Typography,
} from '@mui/material';

/**
 * Maps the raw looks_generated integer to the active step index.
 *   0  → step 0 (job queued)
 *   1  → step 1 (building chart links)
 *   2  → step 2 (chart links ready)
 *   3  → step 3 (generating charts — PHMDataCron running)
 *   4  → step 4 (generating PDF — DownloadPdfCron queued)
 *   5  → step 4 (generating PDF — DownloadPdfCron running)
 *   6  → step 5 (complete — all done)
 *   7  → -2 (failed sentinel)
 *   null/NaN → -1 (no data)
 */
const rawToStep = (raw) => {
  if (raw === null || raw === undefined || isNaN(raw)) return -1;
  if (raw === 7) return -2;
  if (raw === 6) return 5;
  return Math.min(raw, 4);
};

const PIPELINE_STEPS = [
  {
    label: 'Job Queued',
    description: 'PHM generation request received and added to the processing queue.',
    icon: 'queue',
  },
  {
    label: 'Building Chart Links',
    description:
      'Looking up Looker Studio dashboards and building year-filtered embed URLs for each report section.',
    icon: 'insert_chart',
  },
  {
    label: 'Chart Links Ready',
    description:
      'All chart embed URLs saved. Report is queued for the PDF generation step.',
    icon: 'task_alt',
  },
  {
    label: 'Generating Charts',
    description:
      'Querying Snowflake for PHM data and generating chart images for each report section.',
    icon: 'bar_chart',
  },
  {
    label: 'Generating PDF',
    description:
      'Rendering all report sections into a PDF and uploading to storage.',
    icon: 'picture_as_pdf',
  },
  {
    label: 'Complete',
    description: 'Report is ready. PDF is available for download.',
    icon: 'check_circle',
  },
];

const formatElapsed = (secs) => {
  if (secs < 60) return `${secs}s`;
  const m = Math.floor(secs / 60);
  const s = secs % 60;
  return `${m}m ${s.toString().padStart(2, '0')}s`;
};

export default function PHMProgressDialog({ open, onClose, report }) {
  const [elapsed, setElapsed] = useState(0);
  const timerRef = useRef(null);

  const raw = report?.looks_generated_raw;
  const activeStep = rawToStep(raw);
  const isFailed = activeStep === -2;
  const isDone = activeStep === 5;
  const isInProgress = open && !isFailed && !isDone && activeStep >= 0;

  useEffect(() => {
    if (!isInProgress) {
      clearInterval(timerRef.current);
      return;
    }
    // Start from created_at if available, else count from now
    const startMs = report?.created_at ? new Date(report.created_at).getTime() : Date.now();
    const tick = () => setElapsed(Math.floor((Date.now() - startMs) / 1000));
    tick();
    timerRef.current = setInterval(tick, 1000);
    return () => clearInterval(timerRef.current);
  }, [isInProgress, report?.created_at]);

  if (!report) return null;

  return (
    <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
      <DialogTitle sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <Box>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 1.5 }}>
            <Typography variant="h6">{report.report_name || report.name}</Typography>
            {isInProgress && (
              <Chip
                icon={<Icon sx={{ fontSize: '14px !important' }}>timer</Icon>}
                label={formatElapsed(elapsed)}
                size="small"
                color="warning"
                variant="outlined"
                sx={{ fontWeight: 700, fontFamily: 'monospace' }}
              />
            )}
          </Box>
          <Typography variant="caption" color="text.secondary">
            {report.folder_name}
          </Typography>
        </Box>
        <IconButton onClick={onClose} size="small">
          <Icon>close</Icon>
        </IconButton>
      </DialogTitle>

      <DialogContent dividers>
        {/* ── Date summary ── */}
        <Box sx={{ display: 'flex', gap: 3, flexWrap: 'wrap', mb: 2 }}>
          <Box>
            <Typography variant="caption" color="text.secondary">
              Medical Data
            </Typography>
            <Typography variant="body2">
              {report.medical_start_date && report.medical_end_date
                ? `${report.medical_start_date} → ${report.medical_end_date}`
                : '—'}
            </Typography>
          </Box>
          <Box>
            <Typography variant="caption" color="text.secondary">
              Pharmacy Data
            </Typography>
            <Typography variant="body2">
              {report.pharmacy_start_date && report.pharmacy_end_date
                ? `${report.pharmacy_start_date} → ${report.pharmacy_end_date}`
                : '—'}
            </Typography>
          </Box>
          <Box>
            <Typography variant="caption" color="text.secondary">
              Reporting Year
            </Typography>
            <Typography variant="body2">{report.reporting_year || '—'}</Typography>
          </Box>
          <Box>
            <Typography variant="caption" color="text.secondary">
              Frequency
            </Typography>
            <Typography variant="body2" sx={{ textTransform: 'capitalize' }}>
              {report.frequency || '—'}
            </Typography>
          </Box>
        </Box>

        <Divider sx={{ mb: 2 }} />

        {/* ── Status banner ── */}
        {isFailed && (
          <Box
            sx={{
              display: 'flex',
              alignItems: 'center',
              gap: 1,
              p: 1.5,
              mb: 2,
              bgcolor: 'error.50',
              border: '1px solid',
              borderColor: 'error.light',
              borderRadius: 1,
            }}
          >
            <Icon color="error">error</Icon>
            <Typography variant="body2" color="error.main">
              Generation failed. Check backend logs for the error. You can delete and re-submit this
              report.
            </Typography>
          </Box>
        )}

        {isDone && (
          <Box
            sx={{
              display: 'flex',
              alignItems: 'center',
              gap: 1,
              p: 1.5,
              mb: 2,
              bgcolor: 'success.50',
              border: '1px solid',
              borderColor: 'success.light',
              borderRadius: 1,
            }}
          >
            <Icon color="success">check_circle</Icon>
            <Typography variant="body2" color="success.main">
              Report generated successfully.
            </Typography>
          </Box>
        )}

        {/* ── Pipeline stepper ── */}
        <Stepper
          activeStep={isFailed ? 0 : activeStep}
          orientation="vertical"
          sx={{ '& .MuiStepContent-root': { ml: '20px' } }}
        >
          {PIPELINE_STEPS.map((step, index) => {
            const stepDone = !isFailed && activeStep > index;
            const stepActive = !isFailed && activeStep === index;
            const stepFailed = isFailed && index === 0;

            return (
              <Step key={step.label} completed={stepDone}>
                <StepLabel
                  error={stepFailed}
                  StepIconProps={{
                    icon: stepDone ? (
                      <Icon sx={{ color: 'success.main', fontSize: 20 }}>check_circle</Icon>
                    ) : (
                      <Icon
                        sx={{
                          fontSize: 20,
                          color: stepActive
                            ? 'primary.main'
                            : stepFailed
                              ? 'error.main'
                              : 'text.disabled',
                        }}
                      >
                        {step.icon}
                      </Icon>
                    ),
                  }}
                >
                  <Box sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
                    <Typography variant="body2" fontWeight={stepActive ? 600 : 400}>
                      {step.label}
                    </Typography>
                    {stepActive && !isFailed && (
                      <Chip label="In progress" color="warning" size="small" />
                    )}
                    {stepDone && <Chip label="Done" color="success" size="small" />}
                    {stepFailed && <Chip label="Failed" color="error" size="small" />}
                  </Box>
                </StepLabel>
                <StepContent>
                  <Typography variant="caption" color="text.secondary">
                    {step.description}
                  </Typography>
                </StepContent>
              </Step>
            );
          })}
        </Stepper>

        {/* ── Actions ── */}
        {(isDone || report.studio_report_url || report.file_path?.includes('Generated_PHM/')) && (
          <>
            <Divider sx={{ my: 2 }} />
            <Box sx={{ display: 'flex', gap: 2 }}>
              {report.studio_report_url && (
                <Button
                  variant="contained"
                  endIcon={<Icon>open_in_new</Icon>}
                  component="a"
                  href={report.studio_report_url}
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  Open in Looker Studio
                </Button>
              )}
              {report.file_path?.includes('Generated_PHM/') && (
                <Button
                  variant="outlined"
                  endIcon={<Icon>download</Icon>}
                  onClick={() => onClose(report.file_path)}
                >
                  Download PDF
                </Button>
              )}
            </Box>
          </>
        )}
      </DialogContent>
    </Dialog>
  );
}
