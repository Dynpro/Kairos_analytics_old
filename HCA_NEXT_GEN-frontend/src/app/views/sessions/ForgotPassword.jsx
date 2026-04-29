import { LoadingButton } from '@mui/lab';
import { Card, Grid, TextField, ThemeProvider } from '@mui/material';
import { Box, styled } from '@mui/system';
import axios from 'axios';
import { useState } from 'react';

import SnackbarUtils from 'SnackbarUtils';
import BgImg from 'app/components/AppLandingPage/assets/images/7.jpg';
import commonConfig from 'app/components/commonConfig';
import commonRoutes from 'app/components/commonRoutes';
import KairosLogo from 'app/components/icons/kairos.png';
import { Formik } from 'formik';
import { useNavigate } from 'react-router-dom';
import * as Yup from 'yup';
import { lightModeTheme } from 'app/components/MatxTheme/themeColors';

const FlexBox = styled(Box)(() => ({ display: 'flex', alignItems: 'center' }));

const JustifyBox = styled(FlexBox)(() => ({ justifyContent: 'center' }));

const ContentBox = styled(JustifyBox)(() => ({
  height: '100%',
  padding: '32px',
  background: 'rgba(0, 0, 0, 0.01)',
}));

const JwtValidateOTp = styled(JustifyBox)(() => ({
  // background: '#1A2038',
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
};

const validationSchema = Yup.object().shape({
  email: Yup.string().email('Email is invalid').required('Please Enter Email'),
});

const ForgotPassword = () => {
  const [loading, setLoading] = useState(false);

  const navigate = useNavigate();

  const handleFormSubmit = async (values) => {
    setLoading(true);
    try {
      const response = await axios.post(commonConfig.urls.forget_password, {
        email: values.email,
        entity_id: process.env.REACT_APP_env_entity_id,
      });
      setLoading(false);
      if (response.data && response.data.Status === 'Success') {
        navigate(commonRoutes.session.change_password, { state: values.email });
        setLoading(false);
      } else {
        SnackbarUtils.error('Email Not Avaialble...!!!');
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
              <p style={{ paddingRight: '4px' }}>
                You forgot your password? Here you can easily retrieve a new password.
              </p>
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
                        value={values.email}
                        onChange={handleChange}
                        helperText={touched.email && errors.email}
                        error={Boolean(errors.email && touched.email)}
                        sx={{ mb: 4 }}
                      />
                      <LoadingButton
                        type="submit"
                        color="primary"
                        variant="contained"
                        sx={{ mb: 4, mt: 3 }}
                        onClick={handleSubmit}
                        loading={loading}
                      >
                        Send OTP
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

export default ForgotPassword;
