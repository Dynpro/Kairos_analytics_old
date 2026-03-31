import commonConfig from 'app/components/commonConfig';
import axios from 'axios';
import { Formik } from 'formik';
import { useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';

import { Box, Card, styled } from '@mui/material';
import SnackbarUtils from 'SnackbarUtils';
import { Breadcrumb } from 'app/components';
import AppDashboardAccess from 'app/components/ReusableComponents/AppDashboardAccess';
import AppGoBackBtn from 'app/components/ReusableComponents/AppGoBackBtn';
import AppthemeLoadingBtn from 'app/components/ReusableComponents/AppThemeLoadingBtn';
import UserAccess from 'app/components/UserModule/UserCreate/UserAccess';
import commonRoutes from 'app/components/commonRoutes';
import useRefreshData from 'app/hooks/useRefreshData';
import { getAccessToken } from 'app/utils/utils';
import groupUserMappingUtils from '../util';
import AppGroupRoleAccessEdit from './AppGroupRoleAccessEdit';

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
}));

export default function EditGroupUserMapping() {
  const [loading, setLoading] = useState(false);

  const [open, setOpen] = useState(false);
  const navigate = useNavigate();
  const location = useLocation();
  const { handleRefreshData } = useRefreshData(false);

  async function sendDataToServer(data) {
    const authToken = getAccessToken();
    try {
      setLoading(true);
      const response = await axios.put(
        commonConfig.urls.getUserAccessControl + '/' + location.state.grp_usr_mapping_id,
        data,
        {
          headers: { Authorization: `Bearer ${authToken}` },
        }
      );
      setLoading(false);
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);
        setOpen(true);
        navigate(commonRoutes.groupUserMapping.groupUserMappinglist);
        handleRefreshData();
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
            { name: 'Edit User Access' },
          ]}
        />
      </Box>

      <Container sx={{ display: 'flex', justifyContent: 'center' }}>
        <Formik
          validationSchema={groupUserMappingUtils.validationEditSchema}
          initialValues={{
            ...groupUserMappingUtils.initialEditValues,
            group_id: location.state.group_id,
            role_id: location.state.role_id,
          }}
          onSubmit={(values) => {
            sendDataToServer({
              group_id: values.group_id,
              role_id: values.role_id,
              user_id: location.state.user_id,
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
                  <p>{`${location.state.name} ${location.state.last_name}`}</p>
                  <AppGroupRoleAccessEdit userMappingId={location.state.grp_usr_mapping_id} />
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
