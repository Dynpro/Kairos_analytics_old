import {
  Box,
  Icon,
  IconButton,
  styled,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TablePagination,
  TableRow,
} from '@mui/material';
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

const StyledTable = styled(Table)(() => ({
  whiteSpace: 'pre',
  '& thead': {
    '& tr': { '& th': { paddingLeft: 0, paddingRight: 0 } },
  },
  '& tbody': {
    '& tr': { '& td': { paddingLeft: 0, textTransform: 'capitalize' } },
  },
}));

const PaginationTable = ({ data = [] }) => {
  const [page, setPage] = useState(0);
  const [rowsPerPage, setRowsPerPage] = useState(5);
  const navigate = useNavigate();

  const handleChangePage = (_, newPage) => {
    setPage(newPage);
  };

  const handleChangeRowsPerPage = (event) => {
    setRowsPerPage(+event.target.value);
    setPage(0);
  };

  return (
    <Box width="100%" overflow="auto">
      <StyledTable>
        <TableHead>
          <TableRow>
            <TableCell align="left">Name</TableCell>
            <TableCell align="center">Group</TableCell>
            <TableCell align="center">Role</TableCell>
            <TableCell align="right">Email/UserId</TableCell>
            <TableCell align="center"></TableCell>
            <TableCell align="center">Action</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {data.slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage).map((user, index) => (
            <TableRow key={index}>
              <TableCell align="left">{user.name + ' ' + user.last_name}</TableCell>
              <TableCell align="center">{user.group_name}</TableCell>
              <TableCell align="center">{user.role_name}</TableCell>
              <TableCell colSpan={2} align="center">
                {user.email}
              </TableCell>
              <TableCell align="right">
                <IconButton onClick={() => navigate('/material/usersEdit', { state: { ...user } })}>
                  <Icon color="primary">edit_square</Icon>
                </IconButton>
                <IconButton onClick={() => console.log('user delete', user)}>
                  <Icon color="error">delete</Icon>
                </IconButton>
                <IconButton>
                  <Icon color="action">logout</Icon>
                </IconButton>
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </StyledTable>

      <TablePagination
        sx={{ px: 2 }}
        page={page}
        component="div"
        rowsPerPage={rowsPerPage}
        count={data.length}
        onPageChange={handleChangePage}
        rowsPerPageOptions={[5, 10, 25, 50, 100]}
        onRowsPerPageChange={handleChangeRowsPerPage}
        nextIconButtonProps={{ 'aria-label': 'Next Page' }}
        backIconButtonProps={{ 'aria-label': 'Previous Page' }}
      />
    </Box>
  );
};

export default PaginationTable;
