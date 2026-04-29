import axios from 'axios';
import { Formik } from 'formik';
import React, { useState } from 'react';

import { Box, styled } from '@mui/material';
import { useLocation, useNavigate } from 'react-router-dom';

import commonConfig from 'app/components/commonConfig';
import commonRoutes from 'app/components/commonRoutes';

import SnackbarUtils from 'SnackbarUtils';
import { Breadcrumb } from 'app/components';
import useApiOnce from 'app/hooks/useApiOnce';
import useRefreshData from 'app/hooks/useRefreshData';
import { getAccessToken } from 'app/utils/utils';
import clientModuleUtils from '../util';
import EditClientContents from './EditClientContents';

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
}));

export default function EditClient() {
  const location = useLocation();
  const { data: clientInfo } = useApiOnce(commonConfig.urls.client + '/' + location.state.id);
  const { data: phmFolderList } = useApiOnce(commonConfig.urls.getparentphm);
  const [loading, setLoading] = useState(false);
  const [open, setOpen] = React.useState(false);
  const navigate = useNavigate();
  const { handleRefreshData } = useRefreshData(false);

  async function sendDataToServer(recdData = {}) {
    const data = new FormData();
    data.append('id', location.state.id);

    Object.keys(recdData).forEach((item) => {
      if (Array.isArray(recdData[item])) {
        for (const itemArr in recdData[item])
          data.append(`${item}[${itemArr}]`, recdData[item][itemArr]);
      } else data.append(item, recdData[item]);
    });
    try {
      setLoading(true);
      const authToken = getAccessToken();
      const response = await axios.post(commonConfig.urls.updateClient, data, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      setLoading(false);
      if (response && response.data.Status === 'Failed') {
        SnackbarUtils.error(Object.values(response.data.Errors).map((item) => item.toString()));
      }
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);
        setOpen(true);
        navigate(commonRoutes.clients.clientList);
        handleRefreshData();
      }
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  }

  return (
    <>
      <Box className="breadcrumb" sx={{ m: 1 }}>
        <Breadcrumb
          routeSegments={[
            {
              name: 'Client List',
              path: commonRoutes.clients.clientList,
            },
            { name: 'Edit Client' },
          ]}
        />
      </Box>
      <Container sx={{ display: 'flex', justifyContent: 'center' }}>
        <Formik
          enableReinitialize={true}
          initialValues={
            clientModuleUtils.createInitialValues(
              clientModuleUtils.initialValues,
              clientInfo?.client_details,
              phmFolderList
            ) || clientModuleUtils.initialValues
          }
          validationSchema={clientModuleUtils.validationSchema}
          onSubmit={(values) => {
            sendDataToServer({
              ...values,
              phm_folder_id: values.phm_folder_id.map((item) => item.id).filter((item) => item),
            });
          }}
        >
          {() => <EditClientContents id={location.state.id} loading={loading} />}
        </Formik>
      </Container>
    </>
  );
}
