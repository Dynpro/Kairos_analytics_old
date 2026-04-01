import {
  Box,
  IconButton,
  styled,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
} from '@mui/material';
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import commonRoutes from '../commonRoutes';
import AppPaginateTableFooter from '../ReusableComponents/AppPaginateTableFooter';
import AppEditIcon from '../ReusableComponents/AppEditIcon';
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

export default function AppPaginateLookers({ data = [], onPageSet, page, loading }) {
  const [rowsPerPage, setRowsPerPage] = useState(10);
  const navigate = useNavigate();

  const handleChangePage = (_, newPage) => {
    onPageSet(newPage);
  };

  const handleChangeRowsPerPage = (event) => {
    setRowsPerPage(+event.target.value);
    onPageSet(0);
  };

  return (
    <Box width="100%" overflow="auto">
      <StyledTable>
        <TableHead>
          <TableRow>
            <TableCell align="left">Api URL</TableCell>
            <TableCell align="left">Client ID</TableCell>
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
            data
              .slice(page * rowsPerPage, page * rowsPerPage + rowsPerPage)
              .map((looker, index) => (
                <TableRow key={index}>
                  <TableCell align="left">{looker.api_url}</TableCell>
                  <TableCell align="left">{looker.client_id}</TableCell>
                  <TableCell align="left">
                    <IconButton
                      onClick={() =>
                        navigate(commonRoutes.lookerPage.defaultLookerPage, {
                          state: { id: looker.id },
                        })
                      }
                    >
                      <AppEditIcon />
                    </IconButton>
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
    </Box>
  );
}
