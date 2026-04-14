import {
  Box,
  Icon,
  IconButton,
  styled,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
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

const AppPaginateGenerateReports = ({ data = [], fetchData, onPageSet, page, loading }) => {
  const currentReport = useRef(null);
  const [rowsPerPage, setRowsPerPage] = useState(25);
  const navigate = useNavigate();
  const handleChangePage = (_, newPage) => {
    onPageSet(newPage);
  };

  const generateReportsDeletePermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.generate_report_delete
  );

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

  return (
    <AppBox width="100%" overflow="auto">
      <StyledTable>
        <TableHead>
          <TableRow>
            <TableCell align="left">Name</TableCell>
            <TableCell align="left">Client Name</TableCell>
            <TableCell align="left">Reporting Year</TableCell>
            <TableCell align="left">Year</TableCell>
            <TableCell align="left">Frequency</TableCell>
            <TableCell align="left">Status</TableCell>
            <TableCell align="left">Action</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {loading ? (
            <TableRow>
              <TableCell align="left" colSpan={7}>
                <AppTableLinearProgress />
              </TableCell>
            </TableRow>
          ) : data.length === 0 ? (
            <p>No Records found</p>
          ) : (
            data.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage).map((user, index) => (
              <TableRow key={index}>
                <TableCell align="left">{user.name}</TableCell>
                <TableCell align="left">{user.folder_name}</TableCell>
                <TableCell align="left">{user.reporting_year}</TableCell>
                <TableCell align="left">{user.year}</TableCell>
                <TableCell align="left">{user.frequency}</TableCell>
                <TableCell align="left">{user.looks_generated}</TableCell>
                <TableCell align="left">
                  {user.file_path?.includes('Generated_PHM/') &&
                    !(user.file_path === null || user.file_path === '') && (
                      <IconButton onClick={() => handleDownload(user.file_path)}>
                        <Icon color="primary">download</Icon>
                      </IconButton>
                    )}
                  {generateReportsDeletePermission === 1 && (
                    <IconButton
                      onClick={() => {
                        handleUserDelete();
                        currentReport.current = user;
                      }}
                    >
                      <Icon color="error">delete</Icon>
                    </IconButton>
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
    </AppBox>
  );
};

export default AppPaginateGenerateReports;
