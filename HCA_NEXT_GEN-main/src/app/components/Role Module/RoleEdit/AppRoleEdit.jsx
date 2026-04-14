import { Box, Card, styled } from '@mui/material';
import SnackbarUtils from 'SnackbarUtils';
import { Breadcrumb } from 'app/components';
import AppGoBackBtn from 'app/components/ReusableComponents/AppGoBackBtn';
import AppthemeLoadingBtn from 'app/components/ReusableComponents/AppThemeLoadingBtn';
import commonRoutes from 'app/components/commonRoutes';
import useApi from 'app/hooks/useApi';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { Formik } from 'formik';
import React, { useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import commonConfig from '../../commonConfig';
import PersonalInfo from '../Role Create/PersonalInfo';
import RoleAccess from '../Role Create/RoleAccess';
import roleModuleUtils from '../util';
import useRefreshData from 'app/hooks/useRefreshData';

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
  '& .breadcrumb': {
    marginBottom: '30px',
    [theme.breakpoints.down('sm')]: { marginBottom: '16px' },
  },
}));

/* Returned Edit React Component */
const AppRoleEditForm = () => {
  const [loading, setLoading] = useState(false);
  const [open, setOpen] = React.useState(false);
  const navigate = useNavigate();
  const location = useLocation();
  const { data: userInfo } = useApi(commonConfig.urls.getRoleList + '/' + location.state.role_id);
  const { handleRefreshData } = useRefreshData(false);

  async function sendDataToServer(data) {
    const authToken = getAccessToken();
    try {
      setLoading(true);
      const response = await axios.put(
        commonConfig.urls.createRole + '/' + location.state.role_id,
        data,
        {
          headers: { Authorization: `Bearer ${authToken}` },
        }
      );
      setLoading(false);
      if (response && response.data.Status === 'Failed') {
        SnackbarUtils.error(Object.values(response.data.Errors).map((item) => item.toString()));
      }
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);
        setOpen(true);
        navigate(commonRoutes.roles.roleList);
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
            { name: 'Role List', path: commonRoutes.roles.roleList },
            { name: 'Edit Role' },
          ]}
        />
      </Box>

      <Container sx={{ display: 'flex', justifyContent: 'center' }}>
        <Formik
          enableReinitialize={true}
          initialValues={
            roleModuleUtils.createInitalValues(userInfo) || roleModuleUtils.initialValues
          }
          onSubmit={(values) => {
            sendDataToServer(roleModuleUtils.retRoleAccessObj(values));
          }}
        >
          {({ handleSubmit }) => {
            return (
              <Card sx={{ px: 3, pt: 1, pb: 2, width: '100%', maxWidth: '700px' }}>
                <Box
                  component="form"
                  sx={{
                    '& .MuiTextField-root': { m: 1, width: '25ch' },
                    width: '100%',
                  }}
                  noValidate
                  autoComplete="off"
                >
                  <div>
                    <PersonalInfo />
                    <RoleAccess />
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
                  </div>
                </Box>
              </Card>
            );
          }}
        </Formik>
      </Container>
    </>
  );
};

export default AppRoleEditForm;
