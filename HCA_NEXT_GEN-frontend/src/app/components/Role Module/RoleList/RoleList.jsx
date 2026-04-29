import { Box, Button, Grid, styled } from '@mui/material';
import { Breadcrumb, SimpleCard } from 'app/components';
import commonRoutes from 'app/components/commonRoutes';
import AppTableSearchBox from 'app/components/ReusableComponents/AppTableSearchBox';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { useEffect, useState } from 'react';
import { useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import commonConfig from '../../commonConfig';
import PaginationTable from './AppPaginateRole';
import SnackbarUtils from 'SnackbarUtils';

const StyledButton = styled(Button)(({ theme }) => ({
  margin: theme.spacing(1),
  backgroundColor: theme.palette.info.main,
  marginTop: 0,
}));

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
}));

const RoleList = () => {
  const [searched, setSearched] = useState('');
  const [loading, setLoading] = useState(false);
  const [page, setPage] = useState(0);
  const navigate = useNavigate();
  const requestSearch = (searchedVal) =>
    data.filter((row) => {
      return row.role.toLowerCase().includes(searchedVal.toLowerCase());
    });
  const [data, setData] = useState([]);
  const authToken = getAccessToken();
  const roleViewPermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.role_view
  );
  const roleCreatePermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.role_add
  );

  const fetchData = async () => {
    try {
      setLoading(true);
      const response = await axios(commonConfig.urls.getRoleList, {
        headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' },
      });
      setLoading(false);
      if (response && response['data'] && response['data']['Response'])
        setData(response['data']['Response']);
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.message || 'Something went wrong');
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleSearch = (e) => {
    setSearched(e.currentTarget.value);
    if (searched) {
      requestSearch(searched);
    }
  };
  return (
    <>
      <Box className="breadcrumb" sx={{ m: 1 }}>
        <Breadcrumb routeSegments={[{ name: 'Roles List' }]} />
      </Box>
      <Container>
        {roleViewPermission == 1 && (
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
                  Role List
                </span>
              </Grid>
              <Grid item xs={12} sm={3} md={3} lg={2}>
                <AppTableSearchBox onSearch={handleSearch} top="148px" />
              </Grid>
              <Grid item xs={12} sm={3} md={3} lg={2}>
                {roleCreatePermission === 1 && (
                  <StyledButton
                    variant="contained"
                    size="small"
                    color="primary"
                    onClick={() => navigate(commonRoutes.roles.roleAdd)}
                  >
                    Create Role
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
        {!(roleViewPermission === 1) && <h1>You dont have access to view this page</h1>}
      </Container>
    </>
  );
};

export default RoleList;
