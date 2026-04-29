import { Grid, styled } from '@mui/material';
import SnackbarUtils from 'SnackbarUtils';
import { SimpleCard } from 'app/components';
import AppTableSearchBox from 'app/components/ReusableComponents/AppTableSearchBox';
import AppthemeLoadingBtn from 'app/components/ReusableComponents/AppThemeLoadingBtn';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { useEffect, useReducer, useState } from 'react';
import AppPaginateLocalizedClients from './AppPaginateLocalizedClients';

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
    case 'OPEN':
      return { ...state, open: action.bool };
    case 'LOADING':
      return { ...state, loading: action.isLoading };
    default:
      return state;
  }
};

const AppLocalizedClients = ({ fetchListUrl, refreshUrl, diff, listName }) => {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const authToken = getAccessToken();
  const fetchData = async () => {
    try {
      setLoading(true);
      const response = await axios(fetchListUrl, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        },
      });
      setLoading(false);
      if (response && response['data'] && response['data']['Response']) {
        setData(response['data']['Response']);
      }
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.message || 'Something went wrong');
    }
  };
  useEffect(() => {
    fetchData();
  }, []);

  const [state, dispatch] = useReducer(reducer, {
    searched: '',
    page: 0,
    open: false,
    loading: false,
  });
  const requestSearch = (searchedVal) => {
    if (diff) {
      return data.filter((row) => {
        return (
          row.client_primary_id +
          ' ' +
          row.client_name +
          ' ' +
          row.folder_name +
          ' ' +
          row.title
        )
          .toLowerCase()
          .includes(searchedVal.toLowerCase());
      });
    } else
      return data.filter((row) => {
        return (row.id + ' ' + row.name).toLowerCase().includes(searchedVal.toLowerCase());
      });
  };

  const handleSearch = (e) => {
    dispatch({ type: 'PAGE_CHANGED', no: 0 });
    dispatch({ type: 'SEARCHED', newVal: e.currentTarget.value });

    if (state.searched) {
      requestSearch(state.searched);
    }
  };

  const handleRefresh = async () => {
    const authToken = getAccessToken();
    dispatch({ type: 'LOADING', isLoading: true });
    try {
      const response = await axios(refreshUrl, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        },
      });
      dispatch({ type: 'LOADING', isLoading: false });
      if (response.data?.Status === 'Success') {
        SnackbarUtils.success(response.data?.Message);
        fetchData();
      }
      if (response.data?.Status === 'Failed') {
        SnackbarUtils.error('Something went wrong');
      }
    } catch (error) {
      dispatch({ type: 'LOADING', isLoading: false });
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  };

  return (
    <Container>
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
              {listName}
            </span>
          </Grid>
          <Grid item xs={12} sm={3} md={3} lg={2}>
            <AppTableSearchBox onSearch={handleSearch} top="148px" />
          </Grid>
          <Grid item xs={12} sm={3} md={3} lg={2}>
            <AppthemeLoadingBtn
              type="submit"
              loading={state.loading}
              variant="contained"
              sx={{ marginTop: 0 }}
              onClick={handleRefresh}
            >
              Refresh
            </AppthemeLoadingBtn>{' '}
          </Grid>
        </Grid>

        <AppPaginateLocalizedClients
          diff={diff}
          data={state.searched ? requestSearch(state.searched) : data}
          page={state.page}
          loading={loading}
          onPageSet={(no) => dispatch({ type: 'PAGE_CHANGED', no })}
        />
      </SimpleCard>
    </Container>
  );
};

export default AppLocalizedClients;
