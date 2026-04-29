import { useTheme } from '@emotion/react';
import { LoadingButton } from '@mui/lab';
import { Button, Card, Grid, TextField, ThemeProvider } from '@mui/material';
import { Box, styled } from '@mui/system';
import SnackbarUtils from 'SnackbarUtils';
import BgImg from 'app/components/AppLandingPage/assets/images/5.jpg';
import { lightModeTheme } from 'app/components/MatxTheme/themeColors';
import { Paragraph } from 'app/components/Typography';
import commonConfig from 'app/components/commonConfig';
import commonRoutes from 'app/components/commonRoutes';
import KairosLogo from 'app/components/icons/kairos.png';
import axios from 'axios';
import { Formik } from 'formik';
import { useReducer, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import * as Yup from 'yup';

const FlexBox = styled(Box)(() => ({ display: 'flex', alignItems: 'center' }));

const JustifyBox = styled(FlexBox)(() => ({ justifyContent: 'center' }));

const ContentBox = styled(JustifyBox)(() => ({
  height: '100%',
  padding: '32px',
  background: 'rgba(0, 0, 0, 0.01)',
}));

const JwtValidateOTp = styled(JustifyBox)(() => ({
  //background: '#1A2038',
  backgroundImage: `url(${BgImg})`,
  backgroundSize: 'cover',
  backgroundRepeat: 'no-repeat',
  minHeight: '100vh !important',
  '& .card': {
    maxWidth: 800,
    minHeight: 400,
    margin: '1rem',
    display: 'flex',
    borderRadius: 12,
    alignItems: 'center',
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

const initialValues = {
  validOTP: '',
};

const validationSchema = Yup.object().shape({
  validOTP: Yup.string('enter the 4 digit OTP').required('Please enter the OTP'),
});

const ValidateOtp = () => {
  const location = useLocation();
  const [state, dispatch] = useReducer(reducer, {
    loading: false,
    open: false,
    responseError: '',
    hasError: false,
  });
  const [loading, setLoading] = useState(false);
  const theme = useTheme();
  const obj = { ...location.state };

  const navigate = useNavigate();

  const handleFormSubmit = async (values) => {
    try {
      const response = await axios.post(commonConfig.urls.validateOtp, {
        ...obj,
        otp: values.validOTP,
      });
      dispatch({ type: 'LOADING', bool: false });
      if (response && response.data.Status === 401) {
        SnackbarUtils.error(response.data?.Message || 'Invalid');
      }
      setLoading(false);
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);
        navigate(commonRoutes.landingPage.defaultLandingPage);
      }
    } catch (e) {
      // console.log(e);
    }
  };

  const handleResendSubmt = async () => {
    try {
      const response = await axios.post(commonConfig.urls.resendOtp, obj);
      if (response.data?.Status === 402) {
        SnackbarUtils.error('Too many attempts. Register again');
        navigate(commonRoutes.session.signup);
      } else if (response.data?.Status === 401) {
        SnackbarUtils.error('Invalid otp. try again');
      } else if (response.data?.Code === 200) {
        SnackbarUtils.success('OTP sent. Kindly check your mailbox.');
      }
    } catch (error) {
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  };

  return (
    <ThemeProvider theme={lightModeTheme}>
      <JwtValidateOTp>
        <Card className="card">
          <Grid container>
            <Grid item sm={6} xs={12}>
              <ContentBox>
                <img width="100%" alt="Register" src={KairosLogo} />
              </ContentBox>
            </Grid>
            <Grid item sm={6} xs={12}>
              <Box p={4} height="100%">
                <Formik
                  onSubmit={handleFormSubmit}
                  initialValues={initialValues}
                  validationSchema={validationSchema}
                >
                  {({ values, errors, touched, handleBlur, handleSubmit, handleChange }) => (
                    <form onSubmit={handleSubmit}>
                      <Paragraph>Kindly enter the OTP sent to your Email Id.</Paragraph>
                      <br />
                      <TextField
                        fullWidth
                        size="small"
                        type="number"
                        name="validOTP"
                        label="OTP"
                        variant="outlined"
                        onBlur={handleBlur}
                        value={values.validOTP}
                        onChange={handleChange}
                        error={Boolean(errors.validOTP && touched.validOTP)}
                        sx={{ mb: 4 }}
                      />
                      {Boolean(errors.validOTP && touched.validOTP) && (
                        <div style={{ color: 'red' }}>* {errors.validOTP}</div>
                      )}
                      <LoadingButton
                        type="submit"
                        color="primary"
                        variant="contained"
                        sx={{ mb: 4, mt: 3 }}
                        onClick={handleSubmit}
                      >
                        submit
                      </LoadingButton>
                    </form>
                  )}
                </Formik>
                <Button
                  onClick={handleResendSubmt}
                  style={{
                    cursor: 'pointer',
                    margin: '-60px 0  30px',
                    float: 'right',
                    color: theme.palette.primary.main,
                  }}
                >
                  Resend OTP
                </Button>
              </Box>
            </Grid>
          </Grid>
        </Card>
      </JwtValidateOTp>
    </ThemeProvider>
  );
};

export default ValidateOtp;
