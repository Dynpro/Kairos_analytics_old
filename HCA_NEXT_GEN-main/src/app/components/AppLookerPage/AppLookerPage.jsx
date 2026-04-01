import { Box, Card, styled } from '@mui/material';
import SnackbarUtils from 'SnackbarUtils';
import commonRoutes from 'app/components/commonRoutes';
import useApiOnce from 'app/hooks/useApiOnce';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { Formik } from 'formik';
import { useReducer, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import * as Yup from 'yup';
import AppGoBackBtn from '../ReusableComponents/AppGoBackBtn';
import AppthemeLoadingBtn from '../ReusableComponents/AppThemeLoadingBtn';
import AppThemeTextField from '../ReusableComponents/AppThemeTextField';
import commonConfig from '../commonConfig';

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
  '& .breadcrumb': {
    marginBottom: '30px',
    [theme.breakpoints.down('sm')]: { marginBottom: '16px' },
  },
}));

const initialValues = {
  api_url: '',
  client_id: '',
  client_secret: '',
  host: '',
  secret: '',
};

const validationSchema = Yup.object().shape({
  api_url: Yup.string().required('Kindly enter an Api URL').label('API URL'),
  client_id: Yup.string().required('Kindly enter an Client Id').label('Client Id'),
  client_secret: Yup.string()
    .required('Kindly enter a Client Secret Key')
    .label('Client Secret Key'),
  host: Yup.string().required('Kindly enter a Host Name').label('Host Name'),
  secret: Yup.string().required('Kindly enter a Secret Key').label('Secret Key'),
});

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

function createInitalValues(responseObj) {
  if (
    responseObj.api_url &&
    responseObj.client_id &&
    responseObj.client_secret &&
    responseObj.host &&
    responseObj.secret
  ) {
    return {
      ...initialValues,
      api_url: responseObj.api_url,
      client_id: responseObj.client_id,
      client_secret: responseObj.client_secret,
      host: responseObj.host,
      secret: responseObj.secret,
    };
  }
  return null;
}

export default function AppLookerPage() {
  const [state] = useReducer(reducer, {
    loading: false,
    open: false,
    responseError: '',
    hasError: false,
  });
  const [loading, setLoading] = useState(false);

  const location = useLocation();
  const { data: lookerObj } = useApiOnce(commonConfig.urls.looker + '/' + location.state.id);
  const navigate = useNavigate();
  async function sendDataToServer(data) {
    try {
      setLoading(true);
      const authToken = getAccessToken();
      const response = await axios.put(commonConfig.urls.looker + '/' + location.state.id, data, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      setLoading(false);
      if (response && response.data.Status === 'Failed') {
        SnackbarUtils.error(Object.values(response.data.Errors).map((item) => item.toString()));
      }
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);
        navigate(commonRoutes.lookerPage.defaultLookerList);
      }
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  }
  const verifyErrors = (errorName, touchedName) => {
    if (Boolean(touchedName && errorName)) return <div style={{ color: 'red' }}>* {errorName}</div>;
    return null;
  };
  const returnFormField = (
    id,
    label,
    placeholder,
    onChange,
    onBlur,
    error = false,
    touched = false,
    value,
    ...props
  ) => (
    <div>
      <AppThemeTextField
        id={id}
        name={id}
        required
        label={label}
        placeholder={placeholder}
        style={{ width: '80%' }}
        value={value}
        onChange={onChange}
        onBlur={onBlur}
        error={Boolean(error && touched)}
      />
      {verifyErrors(error, touched)}
    </div>
  );
  return (
    <Container sx={{ display: 'flex' }}>
      <Formik
        enableReinitialize={true}
        initialValues={createInitalValues(lookerObj) || initialValues}
        validationSchema={validationSchema}
        onSubmit={(values) => {
          sendDataToServer(values);
        }}
      >
        {({ errors, touched, values, handleBlur, handleChange, handleSubmit }) => {
          return (
            <Card sx={{ px: 3, pt: 1, pb: 2, width: '100%' }}>
              <Box
                component="form"
                sx={{
                  '& .MuiTextField-root': { m: 1, width: '25ch' },
                  width: '100%',
                }}
                noValidate
                autoComplete="off"
              >
                <p style={{ color: 'inherit' }}>Looker Setting</p>
                <div>
                  {returnFormField(
                    'api_url',
                    'API URL',
                    'Enter Api URL',
                    handleChange('api_url'),
                    handleBlur,
                    errors.api_url,
                    touched.api_url,
                    values['api_url']
                  )}
                  {returnFormField(
                    'client_id',
                    'Client Id',
                    'Enter Client Id',
                    handleChange('client_id'),
                    handleBlur,
                    errors.client_id,
                    touched.client_id,
                    values['client_id']
                  )}
                  {returnFormField(
                    'client_secret',
                    'Client Secret Key',
                    'Enter Client Secret Key',
                    handleChange('client_secret'),
                    handleBlur,
                    errors.client_secret,
                    touched.client_secret,
                    values['client_secret']
                  )}
                  {returnFormField(
                    'host',
                    'Host Name',
                    'Enter Host Name',
                    handleChange('host'),
                    handleBlur,
                    errors.host,
                    touched.host,
                    values['host']
                  )}
                  {returnFormField(
                    'secret',
                    'Secret Key',
                    'Enter Secret Key',
                    handleChange('secret'),
                    handleBlur,
                    errors.secret,
                    touched.secret,
                    values['secret']
                  )}
                </div>
                <div style={{ flex: 1 }}>
                  <AppthemeLoadingBtn
                    type="submit"
                    loading={state.loading}
                    variant="contained"
                    sx={{ my: 2 }}
                    onClick={handleSubmit}
                  >
                    Submit
                  </AppthemeLoadingBtn>
                  <AppGoBackBtn>goback</AppGoBackBtn>
                </div>
              </Box>
            </Card>
          );
        }}
      </Formik>
    </Container>
  );
}
