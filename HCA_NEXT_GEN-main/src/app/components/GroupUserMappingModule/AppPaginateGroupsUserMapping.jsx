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
import { useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import SnackbarUtils from 'SnackbarUtils';
import commonConfig from '../commonConfig';
import commonRoutes from '../commonRoutes';
import AppConfirmationDialog from '../ReusableComponents/AppConfirmationDialog';
import AppEditIcon from '../ReusableComponents/AppEditIcon';
import AppPaginateTableFooter from '../ReusableComponents/AppPaginateTableFooter';
import AppTableLinearProgress from '../ReusableComponents/AppTableLinearProgress';

const StyledTable = styled(Table)(() => ({
  whiteSpace: 'pre',
  '& thead': {
    '& tr': { '& th': { paddingLeft: 0, paddingRight: 0 } },
  },
  '& tbody': {
    '& tr': { '& td': { paddingLeft: 0, textTransform: 'capitalize' } },
  },
}));

export default function AppPaginateGroupsUserMapping({
  data = [],
  fetchData,
  onPageSet,
  page,
  loading,
}) {
  const currentUserAccess = useRef(null);
  const [rowsPerPage, setRowsPerPage] = useState(25);
  const navigate = useNavigate();
  const groupUserEditPermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.group_edit
  );
  const groupUserDeletePermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.group_delete
  );

  const handleChangePage = (_, newPage) => {
    onPageSet(newPage);
  };

  const handleChangeRowsPerPage = (event) => {
    setRowsPerPage(+event.target.value);
    onPageSet(0);
  };

  const handleConfirmationResponse = async (user_id) => {
    if (user_id) {
      const authToken = getAccessToken();
      try {
        const response = await axios.delete(
          commonConfig.urls.getUserAccessControl + '/' + user_id,
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
          navigate(commonRoutes.groupUserMapping.groupUserMappinglist);
        }
        currentUserAccess.current = null;
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

  const handleUserDelete = () => setShouldOpenConfirmationDialog(true);
  return (
    <Box width="100%" overflow="auto">
      <StyledTable>
        <TableHead>
          <TableRow>
            <TableCell align="left">Group Name</TableCell>
            <TableCell align="left">Role Name</TableCell>
            <TableCell align="left">User Name</TableCell>
            <TableCell align="left">Action</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {loading ? (
            <TableRow>
              <TableCell align="left" colSpan={4}>
                <AppTableLinearProgress />
              </TableCell>
            </TableRow>
          ) : data.length === 0 ? (
            <p>No Records found</p>
          ) : (
            data.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage).map((user, index) => (
              <TableRow key={index}>
                <TableCell align="left">{user.group_name}</TableCell>
                <TableCell align="left">{user.role}</TableCell>
                <TableCell align="left">{user.name + ' ' + user.last_name}</TableCell>
                <TableCell align="left">
                  {groupUserEditPermission === 1 && (
                    <IconButton
                      onClick={() =>
                        navigate(commonRoutes.groupUserMapping.groupUserMappingEdit, {
                          state: { ...user },
                        })
                      }
                    >
                      <AppEditIcon />
                    </IconButton>
                  )}
                  {groupUserDeletePermission === 1 && (
                    <IconButton
                      onClick={() => {
                        handleUserDelete();
                        currentUserAccess.current = user;
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
          delVal={`${currentUserAccess.current?.group_name} - ${currentUserAccess.current?.role} - ${currentUserAccess.current?.name} ${currentUserAccess.current?.last_name}`}
          open={shouldOpenConfirmationDialog}
          onConfirmDialogClose={handleDialogClose}
          onYesClick={() => handleConfirmationResponse(currentUserAccess.current?.user_id)}
        />
      )}
    </Box>
  );
}
