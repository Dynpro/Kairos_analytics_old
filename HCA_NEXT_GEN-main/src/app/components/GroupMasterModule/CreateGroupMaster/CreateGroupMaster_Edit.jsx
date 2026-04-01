import { LoadingButton } from '@mui/lab';
import { Button, styled } from '@mui/material';
import commonConfig from 'app/components/commonConfig';
import useApi from 'app/hooks/useApi';
import axios from 'axios';
import { Formik } from 'formik';
import React, { useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';

import { Box, Container, MenuItem, TextField } from '@mui/material';
import AppDashboardAccess from 'app/components/ReusableComponents/AppDashboardAccess';
import groupModuleUtils from '../util';

import SnackbarUtils from 'SnackbarUtils';
import { Breadcrumb } from 'app/components';
import commonRoutes from 'app/components/commonRoutes';
import { getAccessToken } from 'app/utils/utils';

const StyledButton = styled(Button)(({ theme }) => ({
  margin: theme.spacing(1),
}));

export default function CreateEditGroupMaster() {
  const { data: userGroups } = useApi(commonConfig.urls.groupList);
  const { data: userGroupRoles } = useApi(commonConfig.urls.roles);
  const [loading, setLoading] = useState(false);
  const [updateOld, setUpdateOld] = useState(false);

  const [open, setOpen] = React.useState(false);
  const navigate = useNavigate();
  const location = useLocation();
  const { data: groupInfo } = useApi(
    commonConfig.urls.groups + '/' + location.state.group_id + '/' + location.state.role_id
  );
  async function sendDataToServer(data) {
    const authToken = getAccessToken();
    try {
      setLoading(true);
      const response = updateOld
        ? await axios.put(commonConfig.urls.groups + '/' + location.state.id, data, {
            headers: { Authorization: `Bearer ${authToken}` },
          })
        : await axios.post(commonConfig.urls.groups, data, {
            headers: { Authorization: `Bearer ${authToken}` },
          });
      setLoading(false);
      if (response && response.data.Status === 'Failed') {
        SnackbarUtils.error(Object.values(response.data.Errors).map((item) => item.toString()));
      }
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);

        setOpen(true);
        navigate('/material/group-master');
      }
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
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
            { name: 'UserList', path: commonRoutes.users.usertablist },
            { name: 'Add User' },
          ]}
        />
      </Box>
      <Container sx={{ display: 'flex' }}>
        <Formik
          enableReinitialize={true}
          initialValues={
            groupModuleUtils.createInitalValues(groupInfo) || groupModuleUtils.initialValues
          }
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
              <Box
                component="form"
                sx={{
                  '& .MuiTextField-root': { m: 1, width: '25ch' },
                  width: '100%',
                  bgcolor: 'white',
                  borderColor: 'white',
                  m: 1,
                  p: 2,
                  border: 1,
                  borderRadius: '16px',
                  boxShadow: '0 0 4px 4px #888888',
                }}
                style={{
                  outline: 'none',
                }}
                noValidate
                autoComplete="off"
              >
                <div style={{ width: '50%', float: 'left' }}>
                  {updateOld ? (
                    <div>
                      <TextField
                        defaultValue={''}
                        id="group_id"
                        name="group_id"
                        value={values.group_id || ''}
                        style={{ width: '80%' }}
                        select
                        label="Groups"
                        placeholder="Select Group"
                        //helperText="Please select User group"
                        onChange={handleChange('group_id')}
                        error={Boolean(errors.group_id && touched.group_id)}
                      >
                        {userGroups.map((option, index) => (
                          <MenuItem key={index} value={option.group_id}>
                            {option.group_name}
                          </MenuItem>
                        ))}
                      </TextField>
                      {verifyErrors(errors, touched, 'group_id')}
                    </div>
                  ) : (
                    <div>
                      <TextField
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
                  )}

                  <div>
                    <TextField
                      id="role_id"
                      name="role_id"
                      required
                      defaultValue={''}
                      value={values.role_id || ''}
                      style={{ width: '80%' }}
                      select
                      label="Roles"
                      placeholder="Select Roles"
                      //helperText="Please select User Role"
                      onChange={handleChange('role_id')}
                      onBlur={handleBlur}
                      error={Boolean(
                        errors.role_id && touched.role_id && touched.role_id && errors.role_id
                      )}
                    >
                      {userGroupRoles.map((option, index) => (
                        <MenuItem key={index} value={option.role_id}>
                          {option.role}
                        </MenuItem>
                      ))}
                    </TextField>
                    {verifyErrors(errors, touched, 'role_id')}
                  </div>
                  <LoadingButton
                    type="submit"
                    color="primary"
                    loading={loading}
                    variant="contained"
                    sx={{ my: 2 }}
                    onClick={handleSubmit}
                  >
                    Submit
                  </LoadingButton>
                  <StyledButton
                    variant="outlined"
                    color="primary"
                    onClick={() => setUpdateOld(!updateOld)}
                  >
                    {updateOld ? 'Create Group' : 'Old Group'}
                  </StyledButton>
                </div>
                <div
                  style={{
                    width: '50%',
                    marginLeft: '20px',
                    height: '80vh',
                    overflow: 'hidden',
                    overflowY: 'visible',
                  }}
                >
                  <AppDashboardAccess checked={values.checked} onCheck={setFieldValue} />
                </div>
              </Box>
            );
          }}
        </Formik>
      </Container>
    </>
  );
}
