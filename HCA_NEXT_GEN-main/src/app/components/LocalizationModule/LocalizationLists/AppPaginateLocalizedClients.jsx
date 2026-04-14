import { Box, styled, Table, TableBody, TableCell, TableHead, TableRow } from '@mui/material';
import { useState } from 'react';
import AppPaginateTableFooter from 'app/components/ReusableComponents/AppPaginateTableFooter';
import AppTableLinearProgress from 'app/components/ReusableComponents/AppTableLinearProgress';

const StyledTable = styled(Table)(() => ({
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

const AppPaginateLocalizedClients = ({ data = [], onPageSet, page, diff = false, loading }) => {
  const [rowsPerPage, setRowsPerPage] = useState(10);
  /*  const userEditPermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.user_edit
  );
  const userDeletePermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.user_delete
  ); */
  const handleChangePage = (_, newPage) => {
    onPageSet(newPage);
  };

  const handleChangeRowsPerPage = (event) => {
    setRowsPerPage(+event.target.value);
    onPageSet(0);
  };

  return (
    <AppBox width="100%" overflow="auto">
      <StyledTable>
        <TableHead>
          {!diff && (
            <TableRow>
              <TableCell align="left">Id</TableCell>
              <TableCell align="left">Name</TableCell>
            </TableRow>
          )}
          {diff && (
            <TableRow>
              <TableCell align="left">Client Primary Id</TableCell>
              <TableCell align="left">Client Name</TableCell>
              <TableCell align="left">Folder Name</TableCell>
              <TableCell align="left">Dashboard Name</TableCell>
            </TableRow>
          )}
        </TableHead>
        {!diff && (
          <TableBody>
            {loading ? (
              <TableRow>
                <TableCell align="left" colSpan={2}>
                  <AppTableLinearProgress />
                </TableCell>
              </TableRow>
            ) : data.length === 0 ? (
              <p>No Records found</p>
            ) : (
              data
                .slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage)
                .map((user, index) => (
                  <TableRow key={index}>
                    <TableCell align="left">{user.id}</TableCell>
                    <TableCell align="left">{user.name}</TableCell>
                  </TableRow>
                ))
            )}
          </TableBody>
        )}
        {diff && (
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
              data
                .slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage)
                .map((user, index) => (
                  <TableRow key={index}>
                    <TableCell align="left">{user.client_primary_id}</TableCell>
                    <TableCell align="left">{user.client_name}</TableCell>
                    <TableCell align="left">{user.folder_name}</TableCell>
                    <TableCell align="left">{user.title}</TableCell>
                  </TableRow>
                ))
            )}
          </TableBody>
        )}
      </StyledTable>

      <AppPaginateTableFooter
        handleChangeRowsPerPage={handleChangeRowsPerPage}
        page={page}
        data={data}
        rowsPerPage={rowsPerPage}
        handleChangePage={handleChangePage}
      />
    </AppBox>
  );
};

export default AppPaginateLocalizedClients;
