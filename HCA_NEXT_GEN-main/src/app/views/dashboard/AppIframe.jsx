import { styled } from '@mui/system';
import axios from 'axios';
import { useEffect, useState } from 'react';

import { Typography } from '@mui/material';
import SnackbarUtils from 'SnackbarUtils';
import { MatxLoading } from 'app/components';
import commonConfig from 'app/components/commonConfig';
import { getAccessToken } from 'app/utils/utils';
import { useSelector } from 'react-redux';

const ERROR_MSG = 'Error fetching Dashboard. Contact Admin';

const LoadingDiv = styled('div')(() => ({
  paddingTop: '25%',
}));

const DashboardDiv = styled('div')(() => ({
  position: 'relative',
  overflow: 'hidden',
  width: '100%',
  height: '100vh',
}));

const Iframe = styled('iframe')(() => ({
  position: 'absolute',
  top: 0,
  left: 0,
  bottom: 0,
  right: 0,
  width: '100%',
  height: '100%',
}));

const ErrorContainer = styled('div')(() => ({
  display: 'flex',
  justifyContent: 'center',
  marginTop: '20px',
}));

export default function AppIframe({ dashId = '', clientId = '' }) {
  const [data, setData] = useState('');
  const [error, setError] = useState('');
  const currentUserPermission = useSelector((state) => state.userType.userIsA);

  const [loading, setLoading] = useState(false);
  const authToken = getAccessToken();
  const sendObj = (dId) =>
    dId
      ? { client_id: clientId, dashboard_id: dashId, flag: 0, permission: currentUserPermission }
      : { client_id: clientId, dashboard_id: null, flag: 1, permission: currentUserPermission };

  useEffect(() => {
    if (clientId) {
      const fetchData = async () => {
        try {
          setError('');
          setLoading(true);
          const response = await axios.post(commonConfig.urls.getLookerDashboard, sendObj(dashId), {
            headers: { Authorization: `Bearer ${authToken}`, 'Content-Type': 'application/json' },
          });
          setLoading(false);

          if (response?.data?.url !== '') return setData(response.data.url);

          if (response?.data?.url === '' && response?.data?.LicenceMessage !== '')
            return setError(response?.data?.LicenceMessage);

          if (response?.data?.url === '' && response?.data?.LicenceMessage === '')
            return setError(ERROR_MSG);
        } catch (error) {
          setLoading(false);
          setError(ERROR_MSG);
          SnackbarUtils.error(error?.message || ERROR_MSG);
        }
      };
      fetchData();
    }
  }, [clientId, dashId, authToken, currentUserPermission]);
  return loading ? (
    <LoadingDiv>
      <MatxLoading />
    </LoadingDiv>
  ) : error ? (
    <ErrorContainer>
      <Typography variant="h5">{error}</Typography>
    </ErrorContainer>
  ) : data ? (
    <DashboardDiv>{data && <Iframe title="Dashboard" frameBorder={0} src={data} />}</DashboardDiv>
  ) : null;
}
