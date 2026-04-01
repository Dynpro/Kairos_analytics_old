import { Box, Grid, styled } from '@mui/material';
import axios from 'axios';
import { Formik } from 'formik';
import { useReducer } from 'react';
import { useNavigate } from 'react-router-dom';

import commonConfig from '../../commonConfig';
import userModuleUtils from '../util';

import SnackbarUtils from 'SnackbarUtils';
import { Breadcrumb } from 'app/components';
import AppDashboardAccess from 'app/components/ReusableComponents/AppDashboardAccess';
import AppGoBackBtn from 'app/components/ReusableComponents/AppGoBackBtn';
import AppthemeLoadingBtn from 'app/components/ReusableComponents/AppThemeLoadingBtn';
import commonRoutes from 'app/components/commonRoutes';
import { getAccessToken } from 'app/utils/utils';
import PersonalInfo from './PersonalInfo';
import UserAccess from './UserAccess';

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
  '& .breadcrumb': {
    marginBottom: '30px',
    [theme.breakpoints.down('sm')]: { marginBottom: '16px' },
  },
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

const UserCreate = () => {
  const [state, dispatch] = useReducer(reducer, {
    loading: false,
    open: false,
    responseError: '',
    hasError: false,
  });

  const navigate = useNavigate();
  async function sendDataToServer(data) {
    const authToken = getAccessToken();
    try {
      dispatch({ type: 'LOADING', bool: true });
      const response = await axios.post(commonConfig.urls.user, data, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      dispatch({ type: 'LOADING', bool: false });
      if (response && response.data.Status === 'Failed') {
        SnackbarUtils.error(
          Object.values(response.data.Errors)
            .map((item) => item.toString())
            .toString()
        );
      }
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success('User Created Successfully');
        navigate(commonRoutes.users.usertablist);
      }
    } catch (error) {
      dispatch({ type: 'LOADING', bool: false });
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  }

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
      <Container>
        <Formik
          initialValues={userModuleUtils.initialValues}
          validationSchema={userModuleUtils.CreateUserSchema}
          onSubmit={(values) => {
            sendDataToServer({
              ...values.personalInfo,
              User_Access: userModuleUtils.retUserAccessObj(values.userAccess),
              ...userModuleUtils.retDashboardAccessObj(values.checked),
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
                      <PersonalInfo showPassword={true} />
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
                        loading={state.loading}
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

export default UserCreate;
