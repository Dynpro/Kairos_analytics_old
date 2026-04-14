import React from 'react';

import { TablePagination } from '@mui/material';
import { useTheme } from '@mui/system';

export default function AppPaginateTableFooter({
  page,
  rowsPerPage,
  data = [],
  handleChangePage,
  handleChangeRowsPerPage,
}) {
  const theme = useTheme();

  return (
    <TablePagination
      sx={{
        color: theme.palette.text.primary,
        px: 2,
        '& .css-pqjvzy-MuiSvgIcon-root-MuiSelect-icon': {
          color: 'inherit',
        },
        '& .css-1mf6u8l-MuiSvgIcon-root-MuiSelect-icon': {
          color: 'inherit',
        },
        '& .css-pdct74-MuiTablePagination-selectLabel': {
          color: 'inherit',
        },
        '& .css-levciy-MuiTablePagination-displayedRows': {
          color: 'inherit',
        },
      }}
      page={page}
      component="div"
      rowsPerPage={rowsPerPage}
      count={data.length}
      onPageChange={handleChangePage}
      rowsPerPageOptions={[10, 25, 50, { value: data.length, label: 'All' }]}
      onRowsPerPageChange={handleChangeRowsPerPage}
      nextIconButtonProps={{
        'aria-label': 'Next Page',
      }}
      backIconButtonProps={{
        'aria-label': 'Previous Page',
      }}
    />
  );
}
