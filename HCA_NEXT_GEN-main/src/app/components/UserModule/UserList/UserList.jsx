import { Button, styled, Grid } from '@mui/material';
import { SimpleCard } from 'app/components';
import commonConfig from 'app/components/commonConfig';
import PaginationTable from './AppPaginateUsers';
import { useEffect, useReducer } from 'react';
import { useNavigate } from 'react-router-dom';
import { useSelector } from 'react-redux';
import commonRoutes from 'app/components/commonRoutes';
import AppTableSearchBox from 'app/components/ReusableComponents/AppTableSearchBox';
import httpService from 'httpService';
import { getAccessToken } from 'app/utils/utils';
import SnackbarUtils from 'SnackbarUtils';

const StyledButton = styled(Button)(({ theme }) => ({
  margin: theme.spacing(1),
  backgroundColor: theme.palette.info.main,
  marginTop: 0,
}));

const Container = styled('div')(({ theme }) => {
  return {
    margin: '30px',
    [theme.breakpoints.down('sm')]: { margin: '16px' },
    '& .breadcrumb': {
      marginBottom: '30px',
      [theme.breakpoints.down('sm')]: { marginBottom: '16px' },
    },
  };
});

const reducer = (state, action) => {
  switch (action.type) {
    case 'SEARCHED':
      return { ...state, searched: action.newVal };
    case 'PAGE_CHANGED':
      return { ...state, page: action.no };
    case 'DATA_CHANGED':
      return { ...state, data: [...action.newData] };
    case 'OPEN':
      return { ...state, open: action.bool };
    case 'LOADING':
      return { ...state, loading: action.bool };
    default:
      return state;
  }
};

const UserList = ({ flag }) => {
  const [state, dispatch] = useReducer(reducer, {
    searched: '',
    page: 0,
    data: [],
    open: false,
    loading: false,
  });
  const userViewPermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.user_view
  );
  const userCreatePermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.user_add
  );
  const navigate = useNavigate();
  const requestSearch = (searchedVal) =>
    state.data.filter((row) => {
      return (
        row.name +
        ' ' +
        row.last_name +
        ' ' +
        row.email +
        ' ' +
        row.group_name +
        ' ' +
        row.role_name
      )
        .toLowerCase()
        .includes(searchedVal.toLowerCase());
    });
  const authToken = getAccessToken();

  const fetchData = async () => {
    try {
      dispatch({ type: 'LOADING', bool: true });
      const response = await httpService.get(commonConfig.urls.user + `?flag=${flag}`, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        },
      });
      dispatch({ type: 'LOADING', bool: false });
      if (response && response['data'] && response['data']['Response']) {
        dispatch({
          type: 'DATA_CHANGED',
          newData: response['data']['Response'],
        });
      }
    } catch (error) {
      dispatch({ type: 'LOADING', bool: false });
      SnackbarUtils.error(error?.message || 'Something went wrong');
    }
  };
  useEffect(() => {
    fetchData();
  }, []);

  const handleSearch = (e) => {
    dispatch({ type: 'PAGE_CHANGED', no: 0 });
    dispatch({ type: 'SEARCHED', newVal: e.currentTarget.value });

    if (state.searched) {
      requestSearch(state.searched);
    }
  };

  return (
    <Container>
      {userViewPermission === 1 && (
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
                User List
              </span>
            </Grid>
            <Grid item xs={12} sm={3} md={3} lg={2}>
              <AppTableSearchBox onSearch={handleSearch} top="148px" />
            </Grid>
            <Grid item xs={12} sm={3} md={3} lg={2}>
              {userCreatePermission === 1 && (
                <StyledButton
                  variant="contained"
                  onClick={() => navigate(commonRoutes.users.userAdd)}
                >
                  Create User
                </StyledButton>
              )}
            </Grid>
          </Grid>
          <PaginationTable
            fetchData={fetchData}
            data={state.searched ? requestSearch(state.searched) : state.data}
            page={state.page}
            loading={state.loading}
            onPageSet={(no) => dispatch({ type: 'PAGE_CHANGED', no })}
          />
        </SimpleCard>
      )}
      {!(userViewPermission === 1) && <h1>You dont have access to view this page</h1>}
    </Container>
  );
};

export default UserList;
