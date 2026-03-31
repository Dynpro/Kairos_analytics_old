import { Box, Button, Grid, styled } from '@mui/material';
import { Breadcrumb, SimpleCard } from 'app/components';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { useEffect, useState } from 'react';
import { useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import AppTableSearchBox from '../ReusableComponents/AppTableSearchBox';
import commonConfig from '../commonConfig';
import commonRoutes from '../commonRoutes';
import PaginationTable from './AppPaginateGroups';
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

export default function GroupList() {
  const [searched, setSearched] = useState('');
  const [loading, setLoading] = useState(false);
  const [page, setPage] = useState(0);
  const groupViewPermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.group_view
  );
  const groupCreatePermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.group_add
  );

  const navigate = useNavigate();
  const [data, setData] = useState([]);
  const requestSearch = (searchedVal) =>
    data.filter((row) => {
      return (row.group_name + ' ' + row.role).toLowerCase().includes(searchedVal.toLowerCase());
    });
  const authToken = getAccessToken();

  const fetchData = async () => {
    try {
      setLoading(true);
      const response = await axios(commonConfig.urls.groups, {
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
        <Breadcrumb routeSegments={[{ name: 'Group List' }]} />
      </Box>
      <Container>
        {groupViewPermission === 1 && (
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
                  Group List
                </span>
              </Grid>
              <Grid item xs={12} sm={3} md={3} lg={2}>
                <AppTableSearchBox onSearch={handleSearch} top="148px" />
              </Grid>
              <Grid item xs={12} sm={3} md={3} lg={2}>
                {groupCreatePermission === 1 && (
                  <StyledButton
                    variant="contained"
                    onClick={() => navigate(commonRoutes.groupMaster.groupMasterAdd)}
                  >
                    Create Group
                  </StyledButton>
                )}
              </Grid>
            </Grid>
            <PaginationTable
              fetchData={fetchData}
              data={searched ? requestSearch(searched) : data}
              page={page}
              onPageSet={setPage}
              loading={loading}
            />
          </SimpleCard>
        )}
        {!(groupViewPermission === 1) && <h1>You dont have access to view this page</h1>}
      </Container>
    </>
  );
}
