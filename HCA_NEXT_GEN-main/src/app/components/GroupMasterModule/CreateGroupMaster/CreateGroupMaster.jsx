import commonConfig from 'app/components/commonConfig';
import useApi from 'app/hooks/useApi';
import axios from 'axios';
import { Formik } from 'formik';
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

import { Box, Button, Card, Grid, MenuItem, styled } from '@mui/material';
import AppDashboardAccess from 'app/components/ReusableComponents/AppDashboardAccess';
import groupModuleUtils from '../util';

import SnackbarUtils from 'SnackbarUtils';
import { Breadcrumb } from 'app/components';
import AppGoBackBtn from 'app/components/ReusableComponents/AppGoBackBtn';
import AppthemeLoadingBtn from 'app/components/ReusableComponents/AppThemeLoadingBtn';
import AppThemeTextField from 'app/components/ReusableComponents/AppThemeTextField';
import commonRoutes from 'app/components/commonRoutes';
import { getAccessToken } from 'app/utils/utils';

const StyledButton = styled(Button)(({ theme }) => ({
  margin: theme.spacing(1),
}));

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
}));

export default function CreateGroupMaster() {
  const { data: userGroups } = useApi(commonConfig.urls.groupList);
  const { data: userGroupRoles } = useApi(commonConfig.urls.roles);
  const [loading, setLoading] = useState(false);
  const [updateOld, setUpdateOld] = useState(false);

  const [open, setOpen] = useState(false);
  const navigate = useNavigate();
  async function sendDataToServer(data) {
    try {
      setLoading(true);
      const authToken = getAccessToken();
      const response = await axios.post(commonConfig.urls.groups, data, {
        headers: { Authorization: `Bearer ${authToken}` },
      });

      setLoading(false);
      if (response && response.data.Code === 406) {
        SnackbarUtils.error(Object.values(response.data.Errors).map((item) => item.toString()));
      }
      if (response && response.data.Status === 401) {
        SnackbarUtils.error(Object.values(response.data.Errors).map((item) => item.toString()));
      }
      if (response && response.data.Code === 401) {
        SnackbarUtils.error(Object.values(response.data.Errors).map((item) => item.toString()));
      }
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);
        setOpen(true);
        navigate(commonRoutes.groupMaster.groupMasterlist);
      }
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.message || 'Something went wrong');
    }
  }

  const verifyErrors = (errors, touched, fieldName) => {
    if (Boolean(touched[fieldName] && errors[fieldName]))
      return <div style={{ color: 'red' }}>* {errors[fieldName]}</div>;
    return null;
  };

  return (
    <>
      <Box className="breadcrumb" sx={{ m: 1 }}>
        <Breadcrumb
          routeSegments={[
            {
              name: 'Group List',
              path: commonRoutes.groupMaster.groupMasterlist,
            },
            { name: 'Create Group' },
          ]}
        />
      </Box>
      <Container sx={{ display: 'flex', justifyContent: 'center' }}>
        <Formik
          enableReinitialize={true}
          validationSchema={
            updateOld ? groupModuleUtils.validationEditSchema : groupModuleUtils.validationSchema
          }
          initialValues={groupModuleUtils.initialValues}
          onSubmit={(values) => {
            sendDataToServer({
              group_id: values.group_id,
              group_name: values.group_name,
              role_id: values.role_id,
              group_flag: updateOld ? 1 : 0,
              dashboard_access: groupModuleUtils.retDashboardAccessObj(values.checked),
            });
          }}
        >
          {({ errors, touched, handleBlur, handleChange, handleSubmit, values, setFieldValue }) => {
            return (
              <Card sx={{ px: 3, pt: 1, pb: 2, width: '100%', maxWidth: '1000px' }}>
                <Box
                  component="form"
                  sx={{
                    '& .MuiTextField-root': { m: 1, width: '25ch' },
                    width: '100%',
                  }}
                  noValidate
                  autoComplete="off"
                >
                  <Grid container spacing={2}>
                    <Grid item xs={12} lg={6}>
                      {/* First Column */}
                      <Grid container direction="column" spacing={2}>
                        <Grid item>
                          <StyledButton color="secondary" onClick={() => setUpdateOld(!updateOld)}>
                            {updateOld ? 'Create Group' : 'Old Group'}
                          </StyledButton>
                        </Grid>
                        <Grid item>
                          {updateOld ? (
                            <div>
                              <AppThemeTextField
                                defaultValue={''}
                                id="group_id"
                                name="group_id"
                                value={values.group_id || ''}
                                style={{ width: '80%' }}
                                select
                                label="Groups"
                                placeholder="Select Group"
                                variant="standard"
                                onChange={handleChange('group_id')}
                                error={Boolean(errors.group_id && touched.group_id)}
                              >
                                {userGroups.map((option, index) => (
                                  <MenuItem key={index} value={option.group_id}>
                                    {option.group_name}
                                  </MenuItem>
                                ))}
                              </AppThemeTextField>
                              {verifyErrors(errors, touched, 'group_id')}
                            </div>
                          ) : (
                            <div>
                              <AppThemeTextField
                                id="group_name"
                                name="group_name"
                                required
                                label="Group Name"
                                placeholder="Enter Group Name"
                                style={{ width: '80%' }}
                                value={values.group_name}
                                onChange={handleChange('group_name')}
                                onBlur={handleBlur}
                                error={Boolean(errors.group_name && touched.group_name)}
                              />
                              {verifyErrors(errors, touched, 'group_name')}
                            </div>
                          )}{' '}
                        </Grid>
                        <Grid item>
                          <div>
                            <AppThemeTextField
                              id="role_id"
                              name="role_id"
                              required
                              defaultValue={''}
                              value={values.role_id || ''}
                              style={{ width: '80%' }}
                              select
                              label="Roles"
                              placeholder="Select Roles"
                              variant="standard"
                              onChange={handleChange('role_id')}
                              onBlur={handleBlur}
                              error={Boolean(
                                errors.role_id &&
                                  touched.role_id &&
                                  touched.role_id &&
                                  errors.role_id
                              )}
                            >
                              {userGroupRoles.map((option, index) => (
                                <MenuItem key={index} value={option.role_id}>
                                  {option.role}
                                </MenuItem>
                              ))}
                            </AppThemeTextField>
                            {verifyErrors(errors, touched, 'role_id')}
                          </div>
                        </Grid>
                      </Grid>
                    </Grid>
                    <Grid item xs={12} lg={6}>
                      {/* Second Column */}
                      <Grid container direction="column" spacing={2}>
                        <Grid item>
                          <div>
                            {verifyErrors(errors, touched, 'checked')}

                            <AppDashboardAccess checked={values.checked} onCheck={setFieldValue} />
                          </div>
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
                </Box>
              </Card>
            );
          }}
        </Formik>
      </Container>
    </>
  );
}
