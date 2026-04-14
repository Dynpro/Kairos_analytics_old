import { Button, Grid, styled } from '@mui/material';
import axios from 'axios';
import { useEffect, useReducer } from 'react';
import { useNavigate } from 'react-router-dom';

import SnackbarUtils from 'SnackbarUtils';
import { SimpleCard } from 'app/components';
import commonRoutes from 'app/components/commonRoutes';
import { getAccessToken } from 'app/utils/utils';
import { useSelector } from 'react-redux';
import AppTableSearchBox from '../ReusableComponents/AppTableSearchBox';
import commonConfig from '../commonConfig';
import PaginationTable from './AppPaginateGenerateReports';

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
    case 'DELETED':
      return { ...state, deleted: action.bool };
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

const AppGenerateReportsList = () => {
  const [state, dispatch] = useReducer(reducer, {
    searched: '',
    deleted: false,
    page: 0,
    data: [],
    open: false,
    loading: false,
  });
  const navigate = useNavigate();
  const requestSearch = (searchedVal) =>
    state.data.filter((row) => {
      return (
        row.name +
        ' ' +
        row.year +
        ' ' +
        row.reporting_year +
        ' ' +
        row.frequency +
        ' ' +
        row.folder_name +
        ' ' +
        row.looks_generated
      )
        .toLowerCase()
        .includes(searchedVal.toLowerCase());
    });
  const authToken = getAccessToken();

  const fetchData = async () => {
    try {
      dispatch({ type: 'LOADING', bool: true });
      const response = await axios(commonConfig.urls.phmAutomationReportList, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        },
      });
      dispatch({ type: 'LOADING', bool: false });
      if (response && response['data'] && response['data']['Response']) {
        dispatch({
          type: 'DATA_CHANGED',
          //         newData: response['data']['Response'],
          newData: response.data?.Response.map((item) => ({
            ...item,
            frequency:
              item.frequency === 1
                ? 'once'
                : item.frequency === 2
                ? 'weekly'
                : item.frequency === 3
                ? 'monthly'
                : item.frequency === 4
                ? 'quarterly'
                : item.frequency,
            looks_generated:
              item.looks_generated === 0 || item.looks_generated === 1 || item.looks_generated === 2
                ? 'started'
                : item.looks_generated === 3 ||
                  item.looks_generated === 4 ||
                  item.looks_generated === 5
                ? 'in progress'
                : item.looks_generated === 6
                ? 'done'
                : item.looks_generated === 7
                ? 'failed'
                : item.looks_generated,
          })),
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

  const generateReportsViewPermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.generate_report_view
  );
  const generateReportsCreatePermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.generate_report_add
  );

  return (
    <Container>
      {generateReportsViewPermission === 1 && (
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
                PHM Reports List
              </span>
            </Grid>
            <Grid item xs={12} sm={3} md={3} lg={2}>
              <AppTableSearchBox onSearch={handleSearch} top="148px" />
            </Grid>
            <Grid item xs={12} sm={3} md={3} lg={2}>
              {generateReportsCreatePermission === 1 && (
                <StyledButton
                  variant="contained"
                  onClick={() => navigate(commonRoutes.generate_reports.generate_reportsAdd)}
                >
                  Create Report
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
      {!(generateReportsViewPermission === 1) && <h1>You dont have access to view this page</h1>}
    </Container>
  );
};

export default AppGenerateReportsList;
