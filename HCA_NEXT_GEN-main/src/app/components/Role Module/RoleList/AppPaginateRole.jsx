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
import AppConfirmationDialog from 'app/components/ReusableComponents/AppConfirmationDialog';
import AppEditIcon from 'app/components/ReusableComponents/AppEditIcon';
import AppPaginateTableFooter from 'app/components/ReusableComponents/AppPaginateTableFooter';
import AppTableLinearProgress from 'app/components/ReusableComponents/AppTableLinearProgress';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { useRef, useState } from 'react';
import { useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import SnackbarUtils from 'SnackbarUtils';

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
  const currentRole = useRef(null);
  const [rowsPerPage, setRowsPerPage] = useState(25);
  const navigate = useNavigate();
  const roleEditPermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.role_edit
  );
  const roleDeletePermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.role_delete
  );

  const handleChangePage = (_, newPage) => {
    onPageSet(newPage);
  };

  const handleChangeRowsPerPage = (event) => {
    setRowsPerPage(+event.target.value);
    onPageSet(0);
  };

  const handleConfirmationResponse = async (roleId) => {
    if (roleId) {
      const authToken = getAccessToken();
      try {
        const response = await axios.delete(commonConfig.urls.role + '/' + roleId, {
          headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' },
        });
        if (response && response.data.Status === 'Success') {
          fetchData();
          SnackbarUtils.success(response.data.Message);
          navigate(commonRoutes.roles.roleList, {
            state: { openSnackbar: true, msgSnackbar: 'DELETION SUCCESSFUL' },
          });
        }
        currentRole.current = null;
        handleDialogClose();
      } catch (error) {
        SnackbarUtils.error(error?.message || 'Something went wrong!!');
      }
    }
  };

  const [shouldOpenConfirmationDialog, setShouldOpenConfirmationDialog] = useState(false);
  const handleDialogClose = () => {
    setShouldOpenConfirmationDialog(false);
  };

  const handleDeleteRole = () => {
    setShouldOpenConfirmationDialog(true);
  };

  return (
    <Box width="100%" overflow="auto">
      <StyledTable>
        <TableHead>
          <TableRow>
            <TableCell align="left">Role</TableCell>
            <TableCell align="left">is_active</TableCell>
            <TableCell align="left">Action</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {loading ? (
            <TableRow>
              <TableCell align="left" colSpan={3}>
                <AppTableLinearProgress />
              </TableCell>
            </TableRow>
          ) : data.length === 0 ? (
            <p>No Records found</p>
          ) : (
            data.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage).map((role, index) => (
              <TableRow key={index}>
                <TableCell align="left">{role.role}</TableCell>
                <TableCell align="left">{role.is_active === 1 ? 'Yes' : 'No'}</TableCell>
                <TableCell align="left">
                  {roleEditPermission === 1 && (
                    <IconButton
                      onClick={() => navigate(commonRoutes.roles.roleEdit, { state: { ...role } })}
                    >
                      <AppEditIcon />
                    </IconButton>
                  )}
                  {roleDeletePermission === 1 && (
                    <IconButton
                      onClick={() => {
                        handleDeleteRole();
                        currentRole.current = role;
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
          delVal={currentRole.current?.role}
          open={shouldOpenConfirmationDialog}
          onConfirmDialogClose={handleDialogClose}
          onYesClick={() => handleConfirmationResponse(currentRole.current?.role_id)}
        />
      )}
    </Box>
  );
};

export default PaginationTable;
