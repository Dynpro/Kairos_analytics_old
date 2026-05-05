import {
  Box,
  Chip,
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
import AppReportStatusModal from './AppReportStatusModal';

const StyledTable = styled(Table)(({ theme }) => ({
  whiteSpace: 'pre',
  '& thead': {
    '& tr': { '& th': { paddingLeft: 0, paddingRight: 0, color: '#e67e22', fontWeight: 600, fontSize: '0.8rem' } },
  },
  '& tbody': {
    '& tr': { '& td': { paddingLeft: 0, textTransform: 'capitalize', fontSize: '0.82rem' } },
  },
}));

const AppBox = styled(Box)(({ theme }) => ({
  color: theme.palette.text.secondary,
}));

const statusColor = (status) => {
  switch (status) {
    case 'done':
      return 'success';
    case 'failed':
      return 'error';
    case 'in progress':
    case 'started':
      return 'warning';
    default:
      return 'default';
  }
};

const formatDateRange = (start, end) => {
  if (!start && !end) return '—';
  const s = start ? String(start).substring(0, 10) : '?';
  const e = end ? String(end).substring(0, 10) : '?';
  return `${s} → ${e}`;
};

const AppPaginateGenerateReports = ({ data = [], fetchData, onPageSet, page, loading }) => {
  const currentReport = useRef(null);
  const [rowsPerPage, setRowsPerPage] = useState(25);
  const [statusModalOpen, setStatusModalOpen] = useState(false);
  const [statusModalReport, setStatusModalReport] = useState(null);
  const navigate = useNavigate();
  const handleChangePage = (_, newPage) => {
    onPageSet(newPage);
  };

  const generateReportsCreatePermission = 1;
  const generateReportsDeletePermission = 1;

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

  const handleOpenStatusModal = (report) => {
    setStatusModalReport(report);
    setStatusModalOpen(true);
  };

  const handleCloseStatusModal = () => {
    setStatusModalOpen(false);
    setStatusModalReport(null);
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
            <TableRow>
              <TableCell colSpan={8} align="center">
                No Records found
              </TableCell>
            </TableRow>
          ) : (
            data.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage).map((user, index) => (
              <TableRow key={index}>
                <TableCell align="left">{user.name}</TableCell>
                <TableCell align="left">{user.folder_name}</TableCell>
                <TableCell align="left">{user.reporting_year}</TableCell>
                <TableCell align="left">
                  {formatDateRange(user.medical_start_date, user.medical_end_date)}
                </TableCell>
                <TableCell align="left">
                  {formatDateRange(user.pharmacy_start_date, user.pharmacy_end_date)}
                </TableCell>
                <TableCell align="left">{user.frequency}</TableCell>
                <TableCell align="left">
                  <Chip
                    label={user.looks_generated}
                    color={statusColor(user.looks_generated)}
                    size="small"
                    sx={{ fontWeight: 600, textTransform: 'capitalize', minWidth: 70 }}
                  />
                </TableCell>
                <TableCell align="left">
                  <Tooltip title="Report Info">
                    <IconButton size="small" onClick={() => handleOpenStatusModal(user)}>
                      <Icon sx={{ color: '#888' }}>info</Icon>
                    </IconButton>
                  </Tooltip>
                  <Tooltip title="Edit">
                    <IconButton
                      size="small"
                      onClick={() =>
                        navigate(commonRoutes.generate_reports.generate_reportsEdit, {
                          state: { reportData: user },
                        })
                      }
                    >
                      <Icon sx={{ color: '#888' }}>edit</Icon>
                    </IconButton>
                  </Tooltip>
                  {user.file_path?.includes('Generated_PHM/') &&
                    !(user.file_path === null || user.file_path === '') && (
                      <Tooltip title="Download PDF">
                        <IconButton size="small" onClick={() => handleDownload(user.file_path)}>
                          <Icon color="primary">download</Icon>
                        </IconButton>
                      </Tooltip>
                    )}
                  {generateReportsDeletePermission === 1 && (
                    <Tooltip title="Delete">
                      <IconButton
                        size="small"
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
      {statusModalOpen && statusModalReport && (
        <AppReportStatusModal
          open={statusModalOpen}
          report={statusModalReport}
          onClose={handleCloseStatusModal}
          onDownload={handleDownload}
          fetchData={fetchData}
        />
      )}
    </AppBox>
  );
};

export default AppPaginateGenerateReports;
