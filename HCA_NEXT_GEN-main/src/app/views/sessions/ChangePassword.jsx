import { LoadingButton } from '@mui/lab';
import { Card, Grid, TextField, ThemeProvider } from '@mui/material';
import { Box, styled } from '@mui/system';
import SnackbarUtils from 'SnackbarUtils';
import BgImg from 'app/components/AppLandingPage/assets/images/13.jpg';
import { lightModeTheme } from 'app/components/MatxTheme/themeColors';
import commonConfig from 'app/components/commonConfig';
import commonRoutes from 'app/components/commonRoutes';
import KairosLogo from 'app/components/icons/kairos.png';
import axios from 'axios';
import { Formik } from 'formik';
import { useState } from 'react';
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

// inital login credentials
const initialValues = {
  email: '',
  otp: '',
  password: '',
  repassword: '',
};

const validationSchema = Yup.object().shape({
  otp: Yup.string('').required('Please enter otp'),
  password: Yup.string()
    .matches(
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})/,
      'The password must be at least 8 characters.Password must contain at least one number and both uppercase and lowercase letters and one special character.'
    )
    .required('Password is required'),
  repassword: Yup.string()
    .oneOf([Yup.ref('password'), null], 'The confirm password and password must match')
    .required('Confirm password is required'),
});

const ChangePassword = () => {
  const location = useLocation();
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleFormSubmit = async (values) => {
    setLoading(true);
    try {
      const response = await axios.post(commonConfig.urls.forgetPasswordChange, {
        email: location.state,
        otp: values.otp,
        password: values.password,
        repassword: values.repassword,
        entity_id: process.env.REACT_APP_env_entity_id,
      });
      if (response.data && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data?.Message);
        navigate(commonRoutes.userProfile.viewProfile);
        setLoading(false);
      } else {
        SnackbarUtils.error(' Invalid OTP..!!');
      }
    } catch (res) {
      setLoading(false);
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
                      <TextField
                        fullWidth
                        size="small"
                        type="email"
                        name="email"
                        label="email"
                        variant="outlined"
                        onBlur={handleBlur}
                        value={location.state || values.email}
                        disabled={true}
                        onChange={handleChange}
                        helperText={touched.email && errors.email}
                        error={Boolean(errors.email && touched.email)}
                        sx={{ mb: 4 }}
                      />
                      <TextField
                        fullWidth
                        size="small"
                        type="number"
                        name="otp"
                        label="Enter otp"
                        variant="outlined"
                        onBlur={handleBlur}
                        value={values.otp}
                        onChange={handleChange}
                        helperText={touched.otp && errors.otp}
                        error={Boolean(errors.otp && touched.otp)}
                        sx={{ mb: 4 }}
                      />
                      <TextField
                        fullWidth
                        size="small"
                        type="password"
                        name="password"
                        label="Please Enter Password"
                        variant="outlined"
                        onBlur={handleBlur}
                        value={values.password}
                        onChange={handleChange}
                        helperText={touched.password && errors.password}
                        error={Boolean(errors.password && touched.password)}
                        sx={{ mb: 4 }}
                      />
                      <TextField
                        fullWidth
                        size="small"
                        type="password"
                        name="repassword"
                        label="Please Re-enter Password"
                        variant="outlined"
                        onBlur={handleBlur}
                        value={values.repassword}
                        onChange={handleChange}
                        helperText={touched.repassword && errors.repassword}
                        error={Boolean(errors.repassword && touched.repassword)}
                        sx={{ mb: 4 }}
                      />
                      <LoadingButton
                        type="submit"
                        color="primary"
                        variant="contained"
                        sx={{ mb: 4, mt: 3 }}
                        onClick={handleSubmit}
                      >
                        Update Password
                      </LoadingButton>
                    </form>
                  )}
                </Formik>
              </Box>
            </Grid>
          </Grid>
        </Card>
      </JwtValidateOTp>
    </ThemeProvider>
  );
};

export default ChangePassword;
