import React, { useRef, useState } from 'react';
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
import LogoutIcon from '@mui/icons-material/Logout';
import axios from 'axios';
import { useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import SnackbarUtils from 'SnackbarUtils';
import AppConfirmationDialog from 'app/components/ReusableComponents/AppConfirmationDialog';
import AppEditIcon from 'app/components/ReusableComponents/AppEditIcon';
import AppPaginateTableFooter from 'app/components/ReusableComponents/AppPaginateTableFooter';
import AppTableLinearProgress from 'app/components/ReusableComponents/AppTableLinearProgress';
import { getAccessToken } from 'app/utils/utils';
import commonConfig from 'app/components/commonConfig';
import commonRoutes from 'app/components/commonRoutes';

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

const PaginationTable = ({ data = [], fetchData, onPageSet, page, loading }) => {
  const currentUser = useRef(null);
  const [rowsPerPage, setRowsPerPage] = useState(25);
  const navigate = useNavigate();
  const userEditPermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.user_edit
  );
  const userDeletePermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.user_delete
  );
  const handleChangePage = (_, newPage) => {
    onPageSet(newPage);
  };

  const handleChangeRowsPerPage = (event) => {
    setRowsPerPage(+event.target.value);
    onPageSet(0);
  };

  const handleConfirmationResponse = async (userId) => {
    if (userId) {
      const authToken = getAccessToken();
      try {
        const response = await axios.delete(commonConfig.urls.user + '/' + userId, {
          headers: {
            Authorization: `Bearer ${authToken}`,
            'Content-Type': 'application/json',
          },
        });
        if (response && response.data.Status === 'Success') {
          fetchData();
          SnackbarUtils.success(response.data.Message);
          navigate(commonRoutes.users.usertablist, {
            state: { openSnackbar: true, msgSnackbar: 'DELETION SUCCESSFUL' },
          });
        }
        currentUser.current = null;
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

  const handleDeleteUser = () => {
    setShouldOpenConfirmationDialog(true);
  };

  return (
    <AppBox width="100%" overflow="auto">
      <StyledTable>
        <TableHead>
          <TableRow>
            <TableCell align="left">Name</TableCell>
            <TableCell align="left">Group</TableCell>
            <TableCell align="left">Role</TableCell>
            <TableCell align="left">Email/UserId</TableCell>
            <TableCell align="left"></TableCell>
            <TableCell align="left">Action</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {loading ? (
            <TableRow>
              <TableCell align="left" colSpan={6}>
                <AppTableLinearProgress />
              </TableCell>
            </TableRow>
          ) : data.length === 0 ? (
            <p>No Records found</p>
          ) : (
            data.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage).map((user, index) => (
              <TableRow key={index}>
                <TableCell align="left">{user.name + ' ' + user.last_name}</TableCell>
                <TableCell align="left">{user.group_name}</TableCell>
                <TableCell align="left">{user.role_name}</TableCell>
                <TableCell colSpan={2} align="left" style={{ textTransform: 'none' }}>
                  {user.email}
                </TableCell>
                <TableCell align="left">
                  {userEditPermission === 1 && (
                    <IconButton
                      onClick={() =>
                        navigate(commonRoutes.users.userEdit, {
                          state: { ...user },
                        })
                      }
                    >
                      <AppEditIcon />
                    </IconButton>
                  )}
                  {userDeletePermission === 1 && (
                    <IconButton
                      onClick={() => {
                        handleDeleteUser();
                        currentUser.current = user;
                      }}
                    >
                      <Icon color="error">delete</Icon>
                    </IconButton>
                  )}
                  {user.google2fa_enable === 1 && (
                    <>
                      <IconButton
                        onClick={async () => {
                          try {
                            // Call the same logic used for disabling 2FA here
                            const authToken = getAccessToken();
                            const headers = {
                              Authorization: `Bearer ${authToken}`,
                              'Content-Type': 'application/json',
                            };
                            const response = await axios.post(
                              'https://hcaapi.kairosrp.com/api/disable_2fa',
                              { email: user.email },
                              { headers }
                            );
                            if (response.status === 200 && response.data.Status === 'Success') {
                              SnackbarUtils.success(
                                response.data.Response.Message || '2FA disabled successfully.'
                              );
                            } else {
                              SnackbarUtils.error(
                                response.data.Response.Message || 'Failed to disable 2FA.'
                              );
                            }
                          } catch (error) {
                            SnackbarUtils.error(error?.message || 'Failed to disable 2FA.');
                          }
                        }}
                      >
                        <LogoutIcon />
                      </IconButton>
                    </>
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
          text="Are you sure to delete?"
          delVal={currentUser.current?.name}
          open={shouldOpenConfirmationDialog}
          onConfirmDialogClose={handleDialogClose}
          onYesClick={() => handleConfirmationResponse(currentUser.current?.id)}
        />
      )}
    </AppBox>
  );
};

export default PaginationTable;
