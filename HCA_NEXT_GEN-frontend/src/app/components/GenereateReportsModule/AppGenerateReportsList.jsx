import { Button, Grid, styled, Tooltip, IconButton, Icon } from '@mui/material';
import axios from 'axios';
import { useEffect, useReducer, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import SnackbarUtils from 'SnackbarUtils';
import { SimpleCard } from 'app/components';
import commonRoutes from 'app/components/commonRoutes';
import { getAccessToken } from 'app/utils/utils';
import { useSelector } from 'react-redux';
import AppTableSearchBox from '../ReusableComponents/AppTableSearchBox';
import commonConfig from '../commonConfig';
import PaginationTable from './AppPaginateGenerateReports';

const POLL_INTERVAL_MS = 15000;
const IN_FLIGHT_STATUSES = ['started', 'in progress'];

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
  const pollTimerRef = useRef(null);
  const requestSearch = (searchedVal) =>
    state.data.filter((row) => {
      return (
        (row.report_name || row.name || '') +
        ' ' +
        (row.year || '') +
        ' ' +
        (row.reporting_year || '') +
        ' ' +
        (row.frequency || '') +
        ' ' +
        (row.folder_name || '') +
        ' ' +
        (row.looks_generated || '') +
        ' ' +
        (row.medical_start_date || '') +
        ' ' +
        (row.medical_end_date || '') +
        ' ' +
        (row.pharmacy_start_date || '') +
        ' ' +
        (row.pharmacy_end_date || '')
      )
        .toLowerCase()
        .includes(searchedVal.toLowerCase());
    });
  const authToken = getAccessToken();

  const mapItem = (item) => ({
    ...item,
    frequency:
      item.frequency === 1 ? 'once'
      : item.frequency === 2 ? 'weekly'
      : item.frequency === 3 ? 'monthly'
      : item.frequency === 4 ? 'quarterly'
      : item.frequency,
    looks_generated:
      item.looks_generated === 0 ? 'started'
      : item.looks_generated === 1 || item.looks_generated === 2 ? 'started'
      : item.looks_generated === 3 || item.looks_generated === 4 || item.looks_generated === 5 ? 'in progress'
      : item.looks_generated === 6 ? 'done'
      : item.looks_generated === 7 ? 'failed'
      : item.looks_generated,
    report_name: item.report_name || item.name,
    medical_start_date: item.medical_start_date || null,
    medical_end_date: item.medical_end_date || null,
    pharmacy_start_date: item.pharmacy_start_date || null,
    pharmacy_end_date: item.pharmacy_end_date || null,
    studio_report_url: item.studio_report_url || null,
    looks_generated_raw: typeof item.looks_generated === 'number' ? item.looks_generated : null,
  });

  const fetchData = async (showSpinner = true) => {
    try {
      if (showSpinner) dispatch({ type: 'LOADING', bool: true });
      const response = await axios(commonConfig.urls.phmAutomationReportList, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        },
      });
      if (showSpinner) dispatch({ type: 'LOADING', bool: false });
      if (response?.data?.Response) {
        const mapped = response.data.Response.map(mapItem);
        dispatch({ type: 'DATA_CHANGED', newData: mapped });

        // Start or stop polling depending on whether any jobs are in flight
        const hasInFlight = mapped.some((r) => IN_FLIGHT_STATUSES.includes(r.looks_generated));
        schedulePoll(hasInFlight);
      }
    } catch (error) {
      if (showSpinner) dispatch({ type: 'LOADING', bool: false });
      SnackbarUtils.error(error?.message || 'Something went wrong');
    }
  };

  const schedulePoll = (shouldPoll) => {
    clearTimeout(pollTimerRef.current);
    if (shouldPoll) {
      pollTimerRef.current = setTimeout(() => fetchData(false), POLL_INTERVAL_MS);
    }
  };

  useEffect(() => {
    fetchData();
    return () => clearTimeout(pollTimerRef.current);
  }, []);

  const handleSearch = (e) => {
    dispatch({ type: 'PAGE_CHANGED', no: 0 });
    dispatch({ type: 'SEARCHED', newVal: e.currentTarget.value });

    if (state.searched) {
      requestSearch(state.searched);
    }
  };

  const isSuperAdmin = useSelector(
    (state) => state.userDetails?.user?.role === 'Super Admin'
  );
  const generateReportsViewPermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.generate_report_view
  );
  const generateReportsCreatePermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.generate_report_add
  );

  const canView = isSuperAdmin || generateReportsViewPermission === 1;
  const canCreate = isSuperAdmin || generateReportsCreatePermission === 1;

  return (
    <Container>
      {canView && (
        <SimpleCard>
          <Grid container spacing={1}>
            <Grid item xs={12} sm={6} md={6} lg={8} sx={{ display: 'flex', alignItems: 'center', gap: 1 }}>
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
              {state.data.some((r) => IN_FLIGHT_STATUSES.includes(r.looks_generated)) && (
                <Tooltip title="Reports are generating — auto-refreshing every 15s">
                  <Icon fontSize="small" sx={{ color: 'warning.main', mb: '14px', animation: 'spin 2s linear infinite', '@keyframes spin': { from: { transform: 'rotate(0deg)' }, to: { transform: 'rotate(360deg)' } } }}>
                    autorenew
                  </Icon>
                </Tooltip>
              )}
              <Tooltip title="Refresh now">
                <IconButton size="small" sx={{ mb: '10px' }} onClick={() => fetchData()}>
                  <Icon fontSize="small">refresh</Icon>
                </IconButton>
              </Tooltip>
            </Grid>
            <Grid item xs={12} sm={3} md={3} lg={2}>
              <AppTableSearchBox onSearch={handleSearch} top="148px" />
            </Grid>
            <Grid item xs={12} sm={3} md={3} lg={2}>
              {canCreate && (
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
      {!canView && <h1>You dont have access to view this page</h1>}
    </Container>
  );
};

export default AppGenerateReportsList;
