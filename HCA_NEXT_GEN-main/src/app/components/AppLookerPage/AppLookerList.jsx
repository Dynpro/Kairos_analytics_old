import React from 'react';
import { styled, Grid, Box } from '@mui/material';
import { Breadcrumb, SimpleCard } from 'app/components';
import commonConfig from '../commonConfig';
import PaginationTable from './AppPaginateLookers';
import { useState } from 'react';
import useApiOnce from 'app/hooks/useApiOnce';
import AppTableSearchBox from '../ReusableComponents/AppTableSearchBox';

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
  '& .breadcrumb': {
    marginBottom: '30px',
    [theme.breakpoints.down('sm')]: { marginBottom: '16px' },
  },
}));

export default function AppLookerList() {
  const [searched, setSearched] = useState('');
  const [page, setPage] = useState(0);
  const { data, loading } = useApiOnce(commonConfig.urls.looker);

  const requestSearch = (searchedVal) =>
    data.filter((row) => {
      return (row.api_url + ' ' + row.client_id).toLowerCase().includes(searchedVal.toLowerCase());
    });

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
        <Breadcrumb routeSegments={[{ name: 'Looker List' }]} />
      </Box>
      <Container>
        <SimpleCard>
          <Grid container spacing={1}>
            <Grid item xs={12} sm={9} md={9} lg={10}>
              <span
                style={{
                  fontSize: '1rem',
                  fontWeight: '500',
                  textTransform: 'capitalize',
                  marginBottom: '16px',
                }}
              >
                Looker Settings
              </span>
            </Grid>
            <Grid item xs={12} sm={3} md={3} lg={2}>
              <AppTableSearchBox onSearch={handleSearch} top="148px" />
            </Grid>
          </Grid>

          <PaginationTable
            loading={loading}
            data={searched ? requestSearch(searched) : data}
            page={page}
            onPageSet={setPage}
          />
        </SimpleCard>
      </Container>
    </>
  );
}
