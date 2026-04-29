import {
  Box,
  Chip,
  CircularProgress,
  Icon,
  IconButton,
  styled,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
  Tooltip,
} from '@mui/material';
import commonConfig from 'app/components/commonConfig';
import commonRoutes from 'app/components/commonRoutes';
import AppPaginateTableFooter from 'app/components/ReusableComponents/AppPaginateTableFooter';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { useRef, useState } from 'react';
import { useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import SnackbarUtils from 'SnackbarUtils';
import AppConfirmationDialog from '../ReusableComponents/AppConfirmationDialog';
import AppTableLinearProgress from '../ReusableComponents/AppTableLinearProgress';
import PHMProgressDialog from './PHMProgressDialog';

const StyledTable = styled(Table)(({ theme }) => ({
  whiteSpace: 'pre',
  '& thead': {
    '& tr': { '& th': { paddingLeft: 0, paddingRight: 0 } },
  },
  '& tbody': {
    '& tr': { '& td': { paddingLeft: 0, textTransform: 'capitalize' } },
  },
}));

const AppBox = styled(Box)(({ theme }) => ({
  color: theme.palette.text.secondary,
}));

const statusConfig = {
  started: { label: 'Started', color: 'default' },
  'in progress': { label: 'In Progress', color: 'warning' },
  done: { label: 'Done', color: 'success' },
  failed: { label: 'Failed', color: 'error' },
};

const StatusChip = ({ status }) => {
  const cfg = statusConfig[status] || { label: status || '—', color: 'default' };
  return <Chip label={cfg.label} color={cfg.color} size="small" />;
};

const AppPaginateGenerateReports = ({ data = [], fetchData, onPageSet, page, loading }) => {
  const currentReport = useRef(null);
  const [rowsPerPage, setRowsPerPage] = useState(25);
  const navigate = useNavigate();
  const handleChangePage = (_, newPage) => {
    onPageSet(newPage);
  };

  const isSuperAdmin = useSelector(
    (state) => state.userDetails?.user?.role === 'Super Admin'
  );
  const generateReportsDeletePermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.generate_report_delete
  );
  const generateReportsEditPermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.generate_report_edit
  );

  const canDelete = isSuperAdmin || generateReportsDeletePermission === 1;
  const canEdit = isSuperAdmin || generateReportsEditPermission === 1;

  const [retryingId, setRetryingId] = useState(null);

  const handleRetry = async (report) => {
    setRetryingId(report.report_id);
    try {
      const resp = await axios.post(
        `${commonConfig.urls.phmAutomationRetry}/${report.report_id}`,
        {},
        { headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' } }
      );
      if (resp?.data?.Status === 'Success') {
        SnackbarUtils.success('Report re-queued for generation');
        fetchData();
      } else {
        SnackbarUtils.error(resp?.data?.Message || 'Retry failed');
      }
    } catch (e) {
      SnackbarUtils.error(e?.message || 'Something went wrong');
    } finally {
      setRetryingId(null);
    }
  };

  const handleChangeRowsPerPage = (event) => {
    setRowsPerPage(+event.target.value);
    onPageSet(0);
  };
  //ps_report_id
  const authToken = getAccessToken();
  const handleConfirmationResponse = async (reportId) => {
    if (reportId) {
      try {
        const response = await axios.delete(
          commonConfig.urls.phmAutomationDeleteReportRequest + '/' + reportId,
          {
            headers: {
              Authorization: `Bearer ${authToken}`,
              'Content-Type': 'application/json',
            },
          }
        );
        if (response && response.data.Status === 'Success') {
          fetchData();
          SnackbarUtils.success(response.data.Message);
          navigate(commonRoutes.generate_reports.generate_reportsTabList, {
            state: { openSnackbar: true, msgSnackbar: 'DELETION SUCCESSFUL' },
          });
        }
        currentReport.current = null;
        handleDialogClose();
      } catch (error) {
        SnackbarUtils.error(error?.message || 'Something went wrong!!');
      }
    }
  };

  const handleDownload = async (fullPath) => {
    const path = fullPath.split('/').pop();
    const authToken = getAccessToken();
    try {
      const response = await axios(
        `${commonConfig.urls.phmAutomationDownloadReport}?file_name=${path}`,
        {
          headers: { Authorization: `Bearer ${authToken}` },
          responseType: 'blob',
        }
      );
      if (response && response['data'] && response['data']) {
        const url = window.URL.createObjectURL(response.data);
        var a = document.createElement('a');
        document.body.appendChild(a);
        a.style = 'display: none';
        a.href = url;
        a.download = path;
        a.click();
        window.URL.revokeObjectURL(url);
      }
    } catch (error) {
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  };

  const [shouldOpenConfirmationDialog, setShouldOpenConfirmationDialog] = useState(false);
  const handleDialogClose = () => {
    setShouldOpenConfirmationDialog(false);
  };
  const handleUserDelete = () => setShouldOpenConfirmationDialog(true);

  const [progressReportId, setProgressReportId] = useState(null);
  const progressReport = progressReportId ? data.find(r => r.report_id === progressReportId) : null;
  const handleProgressClose = (filePath) => {
    setProgressReportId(null);
    // If onClose was called with a file path, trigger the download
    if (typeof filePath === 'string' && filePath.includes('Generated_PHM/')) {
      handleDownload(filePath);
    }
  };

  return (
    <AppBox width="100%" overflow="auto">
      <StyledTable>
        <TableHead>
          <TableRow>
            <TableCell align="left">Report Name</TableCell>
            <TableCell align="left">Client Name</TableCell>
            <TableCell align="left">Reporting Year</TableCell>
            <TableCell align="left">Medical Dates</TableCell>
            <TableCell align="left">Pharmacy Dates</TableCell>
            <TableCell align="left">Frequency</TableCell>
            <TableCell align="left">Status</TableCell>
            <TableCell align="left">Action</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {loading ? (
            <TableRow>
              <TableCell align="left" colSpan={8}>
                <AppTableLinearProgress />
              </TableCell>
            </TableRow>
          ) : data.length === 0 ? (
            <p>No Records found</p>
          ) : (
            data.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage).map((user, index) => (
              <TableRow key={index}>
                <TableCell align="left">{user.report_name || user.name}</TableCell>
                <TableCell align="left">{user.folder_name}</TableCell>
                <TableCell align="left">{user.reporting_year}</TableCell>
                <TableCell align="left" style={{ whiteSpace: 'nowrap', fontSize: '0.78rem' }}>
                  {user.medical_start_date && user.medical_end_date
                    ? `${user.medical_start_date} → ${user.medical_end_date}`
                    : '—'}
                </TableCell>
                <TableCell align="left" style={{ whiteSpace: 'nowrap', fontSize: '0.78rem' }}>
                  {user.pharmacy_start_date && user.pharmacy_end_date
                    ? `${user.pharmacy_start_date} → ${user.pharmacy_end_date}`
                    : '—'}
                </TableCell>
                <TableCell align="left">{user.frequency}</TableCell>
                <TableCell align="left">
                  <StatusChip status={user.looks_generated} />
                </TableCell>
                <TableCell align="left">
                  {/* Progress */}
                  <Tooltip title="View progress">
                    <IconButton onClick={() => setProgressReportId(user.report_id)}>
                      <Icon>info</Icon>
                    </IconButton>
                  </Tooltip>

                  {/* Edit — only when not actively generating */}
                  {canEdit &&
                    user.looks_generated !== 'in progress' &&
                    user.looks_generated !== 'started' && (
                      <Tooltip title="Edit">
                        <IconButton
                          onClick={() =>
                            navigate(commonRoutes.generate_reports.generate_reportsEdit, {
                              state: { report_id: user.report_id },
                            })
                          }
                        >
                          <Icon color="action">edit</Icon>
                        </IconButton>
                      </Tooltip>
                    )}

                  {/* Retry — only for failed */}
                  {user.looks_generated === 'failed' && (
                    <Tooltip title="Retry generation">
                      <span>
                        <IconButton
                          onClick={() => handleRetry(user)}
                          disabled={retryingId === user.report_id}
                        >
                          {retryingId === user.report_id ? (
                            <CircularProgress size={18} />
                          ) : (
                            <Icon color="warning">replay</Icon>
                          )}
                        </IconButton>
                      </span>
                    </Tooltip>
                  )}

                  {/* Open in Looker Studio */}
                  {user.studio_report_url && (
                    <Tooltip title="Open in Looker Studio">
                      <IconButton
                        component="a"
                        href={user.studio_report_url}
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        <Icon color="primary">open_in_new</Icon>
                      </IconButton>
                    </Tooltip>
                  )}

                  {/* Download PDF */}
                  {user.file_path?.includes('Generated_PHM/') && (
                    <Tooltip title="Download PDF">
                      <IconButton onClick={() => handleDownload(user.file_path)}>
                        <Icon color="primary">download</Icon>
                      </IconButton>
                    </Tooltip>
                  )}

                  {/* Delete */}
                  {canDelete && (
                    <Tooltip title="Delete">
                      <IconButton
                        onClick={() => {
                          handleUserDelete();
                          currentReport.current = user;
                        }}
                      >
                        <Icon color="error">delete</Icon>
                      </IconButton>
                    </Tooltip>
                  )}
                </TableCell>
              </TableRow>
            ))
          )}
        </TableBody>
      </StyledTable>

      <AppPaginateTableFooter
        handleChangeRowsPerPage={handleChangeRowsPerPage}
        page={page}
        data={data}
        rowsPerPage={rowsPerPage}
        handleChangePage={handleChangePage}
      />
      {shouldOpenConfirmationDialog && (
        <AppConfirmationDialog
          text="Are you sure to delete"
          delVal={currentReport.current?.name}
          open={shouldOpenConfirmationDialog}
          onConfirmDialogClose={handleDialogClose}
          onYesClick={() => handleConfirmationResponse(currentReport.current?.report_id)}
        />
      )}
      <PHMProgressDialog
        open={Boolean(progressReport)}
        onClose={handleProgressClose}
        report={progressReport}
      />
    </AppBox>
  );
};

export default AppPaginateGenerateReports;
