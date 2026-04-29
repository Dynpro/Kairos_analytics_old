import { Card, Box, styled } from '@mui/material';
import SnackbarUtils from 'SnackbarUtils';
import { Breadcrumb } from 'app/components';
import AppGoBackBtn from 'app/components/ReusableComponents/AppGoBackBtn';
import AppthemeLoadingBtn from 'app/components/ReusableComponents/AppThemeLoadingBtn';
import commonRoutes from 'app/components/commonRoutes';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { Formik } from 'formik';
import React, { useReducer, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import commonConfig from '../../commonConfig';
import roleModuleUtils from '../util';
import PersonalInfo from './PersonalInfo';
import RoleAccess from './RoleAccess';

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
}));

const reducer = (state, action) => {
  switch (action.type) {
    case 'LOADING':
      return { ...state, loading: action.bool };
    case 'OPEN':
      return { ...state, open: action.bool };
    case 'RESPONSE_ERROR':
      return {
        ...state,
        hasError: action.bool,
        responseError: action.error_name,
      };
    default:
      return state;
  }
};

const AppTable = () => {
  const [state, dispatch] = useReducer(reducer, {
    loading: false,
    open: false,
    responseError: '',
    hasError: false,
  });
  const [loading, setLoading] = useState(false);
  const [open, setOpen] = React.useState(false);
  const navigate = useNavigate();

  async function sendDataToServer(data) {
    const authToken = getAccessToken();
    try {
      setLoading(true);
      const response = await axios.post(commonConfig.urls.createRole, data, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      setLoading(false);
      if (response && response.data.Status === 'Failed') {
        SnackbarUtils.error(
          Object.values(response.data.Errors)
            .map((item) => item.toString())
            .toString()
        );
      }
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success('Role Created Successfully');
        setOpen(true);
        navigate(commonRoutes.roles.roleList);
      }
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  }

  const handleClose = (event, reason) => {
    if (reason === 'clickaway') {
      return;
    }
    setOpen(false);
  };

  return (
    <>
      <Box className="breadcrumb" sx={{ m: 1 }}>
        <Breadcrumb
          routeSegments={[
            { name: 'Role List', path: commonRoutes.roles.roleList },
            { name: 'Add Role' },
          ]}
        />
      </Box>

      <Container sx={{ display: 'flex', justifyContent: 'center', minWidth: '470px' }}>
        <Formik
          initialValues={roleModuleUtils.initialValues}
          validationSchema={roleModuleUtils.CreateRoleSchema}
          onSubmit={(values) => {
            sendDataToServer(roleModuleUtils.retRoleAccessObj(values));
          }}
        >
          {({ errors, touched, handleSubmit }) => {
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
                    {Boolean(touched.role && errors.role) && (
                      <div style={{ color: 'red' }}>* {errors.role}</div>
                    )}
                    <hr />
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

export default AppTable;
