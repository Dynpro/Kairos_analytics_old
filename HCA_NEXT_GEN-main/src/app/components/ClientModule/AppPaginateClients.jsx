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
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import SnackbarUtils from 'SnackbarUtils';
import commonConfig from '../commonConfig';
import commonRoutes from '../commonRoutes';
import AppEditIcon from '../ReusableComponents/AppEditIcon';
import AppPaginateTableFooter from '../ReusableComponents/AppPaginateTableFooter';
import AppTableLinearProgress from '../ReusableComponents/AppTableLinearProgress';
import { useSelector } from 'react-redux';
import AppConfirmationDialog from '../ReusableComponents/AppConfirmationDialog';

const StyledTable = styled(Table)(() => ({
  whiteSpace: 'pre',
  '& thead': {
    '& tr': { '& th': { paddingLeft: 0, paddingRight: 0 } },
  },
  '& tbody': {
    '& tr': { '& td': { paddingLeft: 0, textTransform: 'capitalize' } },
  },
}));

const PaginationTable = ({ data = [], fetchData, onPageSet, page, loading }) => {
  const currentClient = useRef(null);
  const [rowsPerPage, setRowsPerPage] = useState(25);
  const [shouldOpenConfirmationDialog, setShouldOpenConfirmationDialog] = useState(false);
  const navigate = useNavigate();
  const clientEditPermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.client_edit
  );
  const clientDeletePermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.client_delete
  );
  const handleChangePage = (_, newPage) => {
    onPageSet(newPage);
  };

  const handleChangeRowsPerPage = (event) => {
    setRowsPerPage(+event.target.value);
    onPageSet(0);
  };

  const handleDialogClose = () => {
    setShouldOpenConfirmationDialog(false);
  };

  const handleClientDelete = () => setShouldOpenConfirmationDialog(true);

  const handleConfirmationResponse = async (groupId) => {
    if (groupId) {
      const authToken = getAccessToken();
      try {
        const response = await axios.delete(commonConfig.urls.client + '/' + groupId, {
          headers: {
            Authorization: `Bearer ${authToken}`,
            'Content-Type': 'application/json',
          },
        });
        if (response && response.data.Status === 'Success') {
          fetchData();
          SnackbarUtils.success(response.data.Message);
          navigate(commonRoutes.clients.clientList);
        }
        currentClient.current = null;
        handleDialogClose();
      } catch (error) {
        SnackbarUtils.error(error?.message || 'Something went wrong!!');
      }
    }
  };
  const calculateYesNo = (val) => (val === 0 ? 'No' : val === 1 ? 'Yes' : 'UNEXPECTED');

  return (
    <Box width="100%" overflow="auto">
      <StyledTable>
        <TableHead>
          <TableRow>
            <TableCell align="left">Client Name</TableCell>
            <TableCell align="left">Schema Name</TableCell>
            <TableCell align="left">API Enabled?</TableCell>
            <TableCell align="left">is PHM?</TableCell>
            <TableCell align="left">Action</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {loading ? (
            <TableRow>
              <TableCell align="left" colSpan={5}>
                <AppTableLinearProgress />
              </TableCell>
            </TableRow>
          ) : data.length === 0 ? (
            <p>No Records found</p>
          ) : (
            data.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage).map((user, index) => (
              <TableRow key={index}>
                <TableCell align="left">{user.folder_name}</TableCell>
                <TableCell align="left">{user.schema_name}</TableCell>
                <TableCell align="left">{calculateYesNo(user.is_approved)}</TableCell>
                <TableCell align="left">{calculateYesNo(user.phm)}</TableCell>
                <TableCell align="left">
                  {clientEditPermission === 1 && (
                    <IconButton
                      onClick={() =>
                        navigate(commonRoutes.clients.clientEdit, {
                          state: { ...user },
                        })
                      }
                    >
                      <AppEditIcon />
                    </IconButton>
                  )}
                  {clientDeletePermission === 1 && (
                    <IconButton
                      onClick={() => {
                        handleClientDelete();
                        currentClient.current = user;
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
        page={page}
        data={data}
        rowsPerPage={rowsPerPage}
        handleChangePage={handleChangePage}
        handleChangeRowsPerPage={handleChangeRowsPerPage}
      />
      {shouldOpenConfirmationDialog && (
        <AppConfirmationDialog
          text="Are you sure to delete"
          delVal={currentClient.current?.folder_name}
          open={shouldOpenConfirmationDialog}
          onConfirmDialogClose={handleDialogClose}
          onYesClick={() => handleConfirmationResponse(currentClient.current?.id)}
        />
      )}
    </Box>
  );
};

export default PaginationTable;
