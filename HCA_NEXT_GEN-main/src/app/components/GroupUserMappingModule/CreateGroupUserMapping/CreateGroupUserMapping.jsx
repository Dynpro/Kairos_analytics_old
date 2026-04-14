import commonConfig from 'app/components/commonConfig';
import axios from 'axios';
import { Formik } from 'formik';
import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';

import { Box, Card, styled } from '@mui/material';
import SnackbarUtils from 'SnackbarUtils';
import { Breadcrumb } from 'app/components';
import AppDashboardAccess from 'app/components/ReusableComponents/AppDashboardAccess';
import AppGoBackBtn from 'app/components/ReusableComponents/AppGoBackBtn';
import AppthemeLoadingBtn from 'app/components/ReusableComponents/AppThemeLoadingBtn';
import UserAccess from 'app/components/UserModule/UserCreate/UserAccess';
import commonRoutes from 'app/components/commonRoutes';
import { getAccessToken } from 'app/utils/utils';
import AppAutoCompleteUsers from '../AppAutoCompleteUsers';
import groupUserMappingUtils from '../util';
import AppGroupRoleAccess from './AppGroupRoleAccess';

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
}));

export default function CreateGroupUserMapping() {
  const [loading, setLoading] = useState(false);
  const [open, setOpen] = React.useState(false);
  const navigate = useNavigate();

  async function sendDataToServer(data) {
    try {
      setLoading(true);
      const authToken = getAccessToken();
      const response = await axios.post(commonConfig.urls.getUserAccessControl, data, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      setLoading(false);
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);

        setOpen(true);
        navigate(commonRoutes.groupUserMapping.groupUserMappinglist);
      }
      if (response && response.data.Status === 'Failed') {
        SnackbarUtils.error(Object.values(response.data.Errors).map((item) => item.toString()));
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
            { name: 'User Mapping List', path: commonRoutes.groupUserMapping.groupUserMappinglist },
            { name: 'Add User Access' },
          ]}
        />
      </Box>

      <Container sx={{ display: 'flex', justifyContent: 'center' }}>
        <Formik
          validationSchema={groupUserMappingUtils.validationSchema}
          initialValues={groupUserMappingUtils.initialValues}
          onSubmit={(values) => {
            sendDataToServer({
              group_id: values.group_id,
              role_id: values.role_id,
              Userslist: groupUserMappingUtils.getUsers(values.finalUserlist),
              role_access: groupUserMappingUtils.retUserAccessObj(values.userAccess),
              ...groupUserMappingUtils.retDashboardAccessObj(values.checked),
            });
          }}
        >
          {({ handleSubmit, values, setFieldValue }) => {
            return (
              <Card sx={{ px: 3, pt: 1, pb: 2, width: '100%', maxWidth: '1000px' }}>
                <Box
                  component="form"
                  sx={{
                    '& .MuiTextField-root': { my: 1, width: '25ch' },
                    width: '100%',
                  }}
                  noValidate
                  autoComplete="off"
                >
                  <AppGroupRoleAccess />
                  {values.Userslist.length > 0 && (
                    <div>
                      <AppAutoCompleteUsers label="Users" placeholder="Select Users" />
                    </div>
                  )}
                  {values.Userslist.length === 0 && (
                    <div>
                      <AppAutoCompleteUsers label="Users" placeholder="Select Users" />
                    </div>
                  )}
                  <hr />
                  <UserAccess />
                  <hr />
                  <AppDashboardAccess checked={values.checked} onCheck={setFieldValue} />
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
                </Box>
              </Card>
            );
          }}
        </Formik>
      </Container>
    </>
  );
}
