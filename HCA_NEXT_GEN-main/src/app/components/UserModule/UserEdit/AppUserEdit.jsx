import { Box, Grid, styled } from '@mui/material';
import axios from 'axios';
import { Formik } from 'formik';
import React, { useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';

import AppDashboardAccess from 'app/components/ReusableComponents/AppDashboardAccess';
import commonConfig from '../../commonConfig';
import PersonalInfoEdit from '../UserCreate/PersonalInfoEdit';
import UserAccess from '../UserCreate/UserAccess';
import userModuleUtils from '../util';

import SnackbarUtils from 'SnackbarUtils';
import { Breadcrumb } from 'app/components';
import AppGoBackBtn from 'app/components/ReusableComponents/AppGoBackBtn';
import AppthemeLoadingBtn from 'app/components/ReusableComponents/AppThemeLoadingBtn';
import commonRoutes from 'app/components/commonRoutes';
import useApi from 'app/hooks/useApi';
import useRefreshData from 'app/hooks/useRefreshData';
import { getAccessToken } from 'app/utils/utils';

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
  '& .breadcrumb': {
    marginBottom: '30px',
    [theme.breakpoints.down('sm')]: { marginBottom: '16px' },
  },
}));

/* Returned Edit React Component */
const AppUserEditForm = () => {
  const [loading, setLoading] = useState(false);
  const [open, setOpen] = React.useState(false);
  const navigate = useNavigate();
  const location = useLocation();
  const { data: userInfo } = useApi(commonConfig.urls.user + '/' + location.state.id);
  const { handleRefreshData } = useRefreshData(false);

  async function sendDataToServer(data) {
    try {
      setLoading(true);
      const authToken = getAccessToken();
      const response = await axios.put(commonConfig.urls.user + '/' + location.state.id, data, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      setLoading(false);
      if (response && response.data.Status === 'Failed') {
        SnackbarUtils.error(Object.values(response.data.Errors).map((item) => item.toString()));
      }
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);
        setOpen(true);
        navigate(commonRoutes.users.usertablist);
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
            { name: 'UserList', path: commonRoutes.users.usertablist },
            { name: 'Edit User' },
          ]}
        />
      </Box>
      <Container sx={{ display: 'flex' }}>
        <Formik
          enableReinitialize={true}
          validationSchema={userModuleUtils.EditUserSchema}
          initialValues={
            userModuleUtils.createInitalValues(userInfo) || userModuleUtils.initialValues
          }
          onSubmit={(values) => {
            sendDataToServer({
              ...values.personalInfo,
              User_Access: userModuleUtils.retUserAccessObj(values.userAccess),
              ...userModuleUtils.retDashboardAccessObj(values.checked),
              is_active: 1,
            });
          }}
        >
          {({ handleSubmit, values, setFieldValue }) => {
            return (
              <Grid container spacing={2}>
                <Grid item xs={12} lg={6}>
                  {/* First Column */}
                  <Grid container direction="column" spacing={2}>
                    <Grid item>
                      <PersonalInfoEdit showPassword={false} />
                    </Grid>
                    <Grid item>
                      <UserAccess />
                    </Grid>
                  </Grid>
                </Grid>
                <Grid item xs={12} lg={6}>
                  {/* Second Column */}
                  <Grid container direction="column" spacing={2}>
                    <Grid item>
                      <AppDashboardAccess checked={values.checked} onCheck={setFieldValue} />
                    </Grid>
                    <Grid item>
                      <AppthemeLoadingBtn
                        type="submit"
                        loading={loading}
                        variant="contained"
                        sx={{ my: 2 }}
                        onClick={handleSubmit}
                      >
                        Submit
                      </AppthemeLoadingBtn>
                      <AppGoBackBtn />
                    </Grid>
                  </Grid>
                </Grid>
              </Grid>
            );
          }}
        </Formik>
      </Container>
    </>
  );
};

export default AppUserEditForm;
