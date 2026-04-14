import { Box, styled } from '@mui/system';
import axios from 'axios';
import { useEffect, useState } from 'react';

import { Button, Grid } from '@mui/material';
import { Breadcrumb, SimpleCard } from 'app/components';
import { getAccessToken } from 'app/utils/utils';
import { useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import AppTableSearchBox from '../ReusableComponents/AppTableSearchBox';
import commonConfig from '../commonConfig';
import commonRoutes from '../commonRoutes';
import PaginationTable from './AppPaginateClients';
import SnackbarUtils from 'SnackbarUtils';

const StyledButton = styled(Button)(({ theme }) => ({
  margin: theme.spacing(1),
  backgroundColor: theme.palette.info.main,
  marginTop: 0,
}));

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
  '& .breadcrumb': {
    marginBottom: '30px',
    [theme.breakpoints.down('sm')]: { marginBottom: '16px' },
  },
}));

export default function ClientList() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const [searched, setSearched] = useState('');
  const [page, setPage] = useState(0);

  const clientViewPermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.client_view
  );
  const clientCreatePermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.client_add
  );

  const navigate = useNavigate();
  const requestSearch = (searchedVal) =>
    data.filter((row) => {
      return (row.folder_name + ' ' + row.schema_name)
        .toLowerCase()
        .includes(searchedVal.toLowerCase());
    });
  const fetchData = async () => {
    const authToken = getAccessToken();
    try {
      setLoading(true);
      const response = await axios(commonConfig.urls.client, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        },
      });
      setLoading(false);
      if (response && response['data'] && response['data']['Response']) {
        setData(response['data']['Response']);
        setPage(0);
      }
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.message || 'Something went wrong');
    }
  };
  useEffect(() => {
    fetchData();
  }, []);
  const handleSearch = (e) => {
    setPage(0);
    setSearched(e.currentTarget.value);
    if (searched) {
      requestSearch(searched);
    }
  };
  return (
    <>
      <Box className="breadcrumb" sx={{ m: 1 }}>
        <Breadcrumb routeSegments={[{ name: 'Clients' }]} />
      </Box>
      <Container>
        {clientViewPermission === 1 && (
          <SimpleCard>
            <Grid container spacing={1}>
              <Grid item xs={12} sm={6} md={6} lg={8}>
                <span
                  style={{
                    fontSize: '1rem',
                    fontWeight: '500',
                    textTransform: 'capitalize',
                    marginBottom: '16px',
                  }}
                >
                  Client List
                </span>
              </Grid>
              <Grid item xs={12} sm={3} md={3} lg={2}>
                <AppTableSearchBox onSearch={handleSearch} top="148px" />
              </Grid>
              <Grid item xs={12} sm={3} md={3} lg={2}>
                {clientCreatePermission === 1 && (
                  <StyledButton
                    variant="contained"
                    onClick={() => navigate(commonRoutes.clients.clientAdd)}
                  >
                    Create Client
                  </StyledButton>
                )}
              </Grid>
            </Grid>

            <PaginationTable
              loading={loading}
              fetchData={fetchData}
              data={searched ? requestSearch(searched) : data}
              page={page}
              onPageSet={setPage}
            />
          </SimpleCard>
        )}
        {!(clientViewPermission === 1) && <h1>You dont have access to view this page</h1>}
      </Container>
    </>
  );
}
