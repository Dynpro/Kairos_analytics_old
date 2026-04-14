import { Button, styled, TextField } from '@mui/material';
import { SimpleCard } from 'app/components';
import commonConfig from '../../commonConfig';
import PaginationTable from './AppPaginateUsers';
import { useState } from 'react';
import useApi from 'app/hooks/useApi';
import { useNavigate } from 'react-router-dom';
import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TableSortLabel,
  Paper,
  makeStyles,
} from '@mui/material';
import users from './users';

const StyledButton = styled(Button)(({ theme }) => ({
  margin: theme.spacing(1),
}));

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
  '& .breadcrumb': {
    marginBottom: '30px',
    [theme.breakpoints.down('sm')]: { marginBottom: '16px' },
  },
}));

function createData(name, group, role, email, action) {
  return { name, group, role, email, action };
}

const AppTable = () => {
  const [searched, setSearched] = useState('');
  const navigate = useNavigate();
  const requestSearch = (searchedVal) =>
    data.filter((row) => {
      return (row.name + ' ' + row.last_name + ' ' + row.email)
        .toLowerCase()
        .includes(searchedVal.toLowerCase());
    });
  const { data } = useApi(commonConfig.urls.user);

  return (
    <Container>
      <SimpleCard title="User List">
        <TextField
          id="outlined-search"
          label="Search Users"
          type="search"
          style={{ position: 'absolute', right: 200, top: 40 }}
          onChange={(e) => {
            setSearched(e.currentTarget.value);
            if (searched) {
              requestSearch(searched);
            }
          }}
        />
        <StyledButton
          variant="contained"
          color="primary"
          style={{ position: 'absolute', right: 50, top: 40 }}
          onClick={() => navigate('/material/usersCreate')}
        >
          Create User
        </StyledButton>
        <PaginationTable data={searched ? requestSearch(searched) : data} />
      </SimpleCard>
    </Container>
  );
};

export default AppTable;
