// import React, { useState, useEffect } from 'react';
// import {
//   Card,
//   Checkbox,
//   Grid,
//   IconButton,
//   Input,
//   InputAdornment,
//   Dialog,
//   DialogTitle,
//   DialogContent,
//   DialogActions,
//   Button,
//   TextField,
//   ThemeProvider,
// } from '@mui/material';
// import { Visibility, VisibilityOff } from '@mui/icons-material';
// import { LoadingButton } from '@mui/lab';
// import { styled, useTheme } from '@mui/system';
// import BgImg from 'app/components/AppLandingPage/assets/images/1.jpg';
// import { lightModeTheme } from 'app/components/MatxTheme/themeColors';
// import { Paragraph } from 'app/components/Typography';
// import commonRoutes from 'app/components/commonRoutes';
// import KairosLogo from 'app/components/icons/MyCharlie_Logo_light.png';
// import useAuth from 'app/hooks/useAuth';
// import { Formik } from 'formik';
// import * as Yup from 'yup';
// import { getAccessToken } from 'app/utils/utils';
// import { defaultThemeOption } from 'app/utils/utils';
// import axios from 'axios';
// import SnackbarUtils from 'SnackbarUtils';
// import ReCAPTCHA from 'react-google-recaptcha';
// import { NavLink, useNavigate } from 'react-router-dom';

// const verifyErrors = (errorName, touchedName) => {
//   if (Boolean(touchedName && errorName)) return <div style={{ color: 'red' }}>* {errorName}</div>;
//   return null;
// };

// const FlexBox = styled('div')(({ theme }) => ({
//   display: 'flex',
//   alignItems: 'center',
// }));

// const JustifyBox = styled(FlexBox)(({ theme }) => ({
//   justifyContent: 'center',
// }));

// const ContentBox = styled('div')(({ theme }) => ({
//   height: '100%',
//   padding: '32px',
//   position: 'relative',
//   background: 'rgba(0, 0, 0, 0.01)',
// }));

// const JWTRoot = styled(JustifyBox)(({ theme }) => ({
//   backgroundImage: `url(${BgImg})`,
//   backgroundSize: 'cover',
//   backgroundRepeat: 'no-repeat',
//   minHeight: '100% !important',
//   '& .card': {
//     maxWidth: 800,
//     minHeight: 400,
//     margin: '1rem',
//     display: 'flex',
//     borderRadius: 12,
//     alignItems: 'center',
//   },
// }));

// // Initial login credentials
// const initialValues = {
//   email: '',
//   password: '',
//   remember: true,
// };

// // Form field validation schema
// const validationSchema = Yup.object().shape({
//   password: Yup.string().required('Password is required!'),
//   email: Yup.string().email('Invalid Email address').required('Email is required!'),
// });

// const JwtLogin = () => {
//   const [showPassword, setShowPassword] = useState(false);
//   const [disableSubmit, setDisableSubmit] = useState(true);
//   const [twoFactorEnabled, setTwoFactorEnabled] = useState(false);
//   const [userData, setUserData] = useState([]);
//   const [showOTPDialog, setShowOTPDialog] = useState(false);
//   const [userObj, setUserObj] = useState({ email: '' });
//   const [otp, setOtp] = useState('');
//   const [is2FAEnabled, setIs2FAEnabled] = useState(false); // Define is2FAEnabled state
//   const captchaRef = React.useRef(null);
//   const theme = useTheme();
//   const navigate = useNavigate();
//   const [loading, setLoading] = useState(false);
//   const { login } = useAuth();
//   const authToken = getAccessToken();

//   const handleClickShowPassword = () => {
//     setShowPassword(!showPassword);
//   };

//   const handleMouseDownPassword = (event) => {
//     event.preventDefault();
//   };

//   const handleOtpChange = (event) => {
//     setOtp(event.target.value);
//   };

//   useEffect(() => {
//     const storedTwoFactorEnabled = localStorage.getItem('twoFactorEnabled');
//     if (storedTwoFactorEnabled !== null) {
//       setTwoFactorEnabled(JSON.parse(storedTwoFactorEnabled));
//     }
//   }, []);

//   const fetchTwoFactorStatus = async (email) => {
//     try {
//       const response = await axios.post('https://hcaapi.kairosrp.com/api/twofa_check', {
//         email: email,
//       });

//       const is2FAEnabled =
//         response.data && response.data.Response && response.data.Response.google2fa_enable;

//       return is2FAEnabled;
//     } catch (error) {
//       console.error('Error fetching 2FA status:', error);
//       throw new Error(error?.message || 'Failed to fetch 2FA status');
//     }
//   };

//   useEffect(() => {
//     const fetchTwoFactorStatusIfNeeded = async () => {
//       if (userObj.email) {
//         await fetchTwoFactorStatus(userObj.email);
//       }
//     };
//     if (!twoFactorEnabled) {
//       fetchTwoFactorStatusIfNeeded();
//     }
//   }, [userObj.email, twoFactorEnabled]);

//   const handleFormSubmit = async (values) => {
//     try {
//       await login(values.email, values.password);
//       // setUserData(user);
//       setUserObj({ email: values.email });

//       // Fetch 2FA status
//       const is2FAEnabled = await fetchTwoFactorStatus(values.email);

//       if (is2FAEnabled) {
//         SnackbarUtils.success('Two-Factor Authentication is enabled.');
//         setShowOTPDialog(true);
//       } else {
//         SnackbarUtils.info('Two-Factor Authentication is disabled.');
//         navigate('/');
//       }
//     } catch (error) {
//       setLoading(false);

//     }
//   };

//   const handleVerifyOtp = async () => {
//     try {
//       setLoading(true);
//       const authToken = getAccessToken();
//       const headers = {
//         Authorization: `Bearer ${authToken}`,
//         'x-api-key': 'vXMSa5wKXT57V2DA6ZKgJ6I4TcV5MIRU1ezNMvOa',
//         'Content-Type': 'application/json',
//       };

//       const requestBody = {
//         email: userObj.email,
//         otp: otp,
//       };

//       const response = await axios.post(
//         'https://hcaapi.kairosrp.com/api/validate_2fa_otp',
//         requestBody,
//         { headers }
//       );

//       setLoading(false);

//       if (response.data && response.data.Response && response.data.Response.Status === 'Success') {
//         SnackbarUtils.success('OTP verification successful');
//         setShowOTPDialog(false);
//         navigate('/');

//         navigate('/');
//       } else {
//         SnackbarUtils.error('OTP verification failed');
//       }
//     } catch (error) {
//       console.error('Error verifying OTP:', error);
//     }
//   };

//   return (
//     <ThemeProvider theme={lightModeTheme}>
//       <JWTRoot>
//         <Card className="card">
//           <Grid container justifyContent="center" alignItems="center">
//             <Grid item sm={6} xs={12}>
//               <JustifyBox p={4} height="100%" sx={{ minWidth: 320 }}>
//                 <img src={KairosLogo} width="100%" alt="Brandlogo" />
//               </JustifyBox>
//             </Grid>
//             <Grid item sm={6} xs={12}>
//               <ContentBox>
//                 <Formik
//                   onSubmit={handleFormSubmit}
//                   initialValues={initialValues}
//                   validationSchema={validationSchema}
//                 >
//                   {({
//                     values,
//                     errors,
//                     touched,
//                     handleChange,
//                     handleBlur,
//                     handleSubmit,
//                     setFieldTouched,
//                     setFieldValue,
//                   }) => (
//                     <form onSubmit={handleSubmit}>
//                       {verifyErrors(errors.email, touched.email)}
//                       <Input
//                         autoComplete="on"
//                         type="email"
//                         fullWidth
//                         size="small"
//                         name="email"
//                         label="Email"
//                         variant="standard"
//                         onBlur={handleBlur}
//                         onChange={(e) => {
//                           setFieldValue('email', e.currentTarget.value);
//                           setFieldTouched('email', true, false);
//                         }}
//                         value={values.email}
//                         error={Boolean(errors.email && touched.email)}
//                         placeholder="Email"
//                         sx={{ mb: 3 }}
//                       />
//                       {verifyErrors(errors.password, touched.password)}
//                       <Input
//                         autoComplete="on"
//                         type={showPassword ? 'text' : 'password'}
//                         fullWidth
//                         size="small"
//                         name="password"
//                         label="Password"
//                         variant="outlined"
//                         onBlur={handleBlur}
//                         onChange={(e) => {
//                           setFieldValue('password', e.currentTarget.value);
//                           setFieldTouched('password', true, false);
//                         }}
//                         value={values.password}
//                         error={Boolean(errors.password && touched.password)}
//                         placeholder="Password"
//                         endAdornment={
//                           <InputAdornment position="end">
//                             <IconButton
//                               onClick={handleClickShowPassword}
//                               onMouseDown={handleMouseDownPassword}
//                             >
//                               {showPassword ? <Visibility /> : <VisibilityOff />}
//                             </IconButton>
//                           </InputAdornment>
//                         }
//                         sx={{ mb: 3 }}
//                       />
//                       <ReCAPTCHA
//                         sitekey={process.env.REACT_APP_SITE_KEY}
//                         ref={captchaRef}
//                         onChange={(value) => {
//                           if (value) setDisableSubmit(false);
//                         }}
//                         onExpired={() => setDisableSubmit(true)}
//                       />
//                       <FlexBox justifyContent="space-between">
//                         <FlexBox gap={1} sx={{ marginRight: 'auto' }}>
//                           {' '}
//                           <Checkbox
//                             size="small"
//                             name="remember"
//                             onChange={handleChange}
//                             checked={values.remember}
//                             sx={{ padding: 0 }}
//                           />
//                           <Paragraph>Remember Me</Paragraph>
//                         </FlexBox>

//                         <NavLink
//                           to={commonRoutes.session.forgot_password}
//                           style={{ color: theme.palette.primary.main }}
//                         >
//                           Forgot password?
//                         </NavLink>
//                       </FlexBox>

//                       <LoadingButton
//                         disabled={
//                           !(
//                             Object.keys(errors).length === 0 &&
//                             Boolean(touched.email) &&
//                             Boolean(touched.password) &&
//                             !disableSubmit
//                           )
//                         }
//                         type="submit"
//                         color="primary"
//                         loading={loading}
//                         variant="contained"
//                         sx={{ my: 2 }}
//                         onClick={() => {
//                           if (twoFactorEnabled && is2FAEnabled) {
//                             setShowOTPDialog(true); // Open OTP dialog
//                           } else {
//                             handleFormSubmit(); // Proceed with login
//                           }
//                         }}
//                       >
//                         Login
//                       </LoadingButton>
//                       <Dialog open={showOTPDialog} onClose={() => setShowOTPDialog(false)}>
//                         <DialogTitle>Enter OTP</DialogTitle>
//                         <DialogContent>
//                           <TextField
//                             autoFocus
//                             margin="dense"
//                             id="otp"
//                             label="OTP"
//                             type="text"
//                             fullWidth
//                             value={otp}
//                             onChange={handleOtpChange}
//                           />
//                         </DialogContent>
//                         <DialogActions>
//                           <Button onClick={() => setShowOTPDialog(false)}>Cancel</Button>
//                           <Button onClick={handleVerifyOtp} variant="contained" color="primary">
//                             Confirm
//                           </Button>
//                         </DialogActions>
//                       </Dialog>
//                       <Paragraph>
//                         Don't have an account?
//                         <NavLink
//                           to={commonRoutes.session.signup}
//                           style={{ color: theme.palette.primary.main, marginLeft: 5 }}
//                         >
//                           Register
//                         </NavLink>
//                       </Paragraph>
//                     </form>
//                   )}
//                 </Formik>
//               </ContentBox>
//             </Grid>
//           </Grid>
//         </Card>
//       </JWTRoot>
//     </ThemeProvider>
//   );
// };

// export default JwtLogin;
import React, { useState, useEffect } from 'react';
import {
  Card,
  Checkbox,
  Grid,
  IconButton,
  Input,
  InputAdornment,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  Button,
  TextField,
  ThemeProvider,
} from '@mui/material';
import { Visibility, VisibilityOff } from '@mui/icons-material';
import BgImg from 'app/components/AppLandingPage/assets/images/1.jpg';
import { LoadingButton } from '@mui/lab';
import { styled, useTheme } from '@mui/system';
import { lightModeTheme } from 'app/components/MatxTheme/themeColors';
import { Paragraph } from 'app/components/Typography';
import commonRoutes from 'app/components/commonRoutes';
import KairosLogo from 'app/components/icons/kairos.png';
import commonConfig from 'app/components/commonConfig';
import useAuth from 'app/hooks/useAuth';
import { Formik } from 'formik';
import * as Yup from 'yup';
import axios from 'axios';
import { getAccessToken } from 'app/utils/utils';
import SnackbarUtils from 'SnackbarUtils';
import { NavLink, useNavigate } from 'react-router-dom';
import userAgentParser from 'user-agent-parser';

const verifyErrors = (errorName, touchedName) => {
  if (Boolean(touchedName && errorName)) return <div style={{ color: 'red' }}>* {errorName}</div>;
  return null;
};

const FlexBox = styled('div')(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
}));

const JustifyBox = styled(FlexBox)(({ theme }) => ({
  justifyContent: 'center',
}));

const ContentBox = styled('div')(({ theme }) => ({
  height: '100%',
  padding: '32px',
  position: 'relative',
  background: 'rgba(0, 0, 0, 0.01)',
}));

const JWTRoot = styled(JustifyBox)(({ theme }) => ({
  backgroundImage: `url(${BgImg})`,
  backgroundSize: 'cover',
  backgroundRepeat: 'no-repeat',
  minHeight: '100% !important',
  '& .card': {
    maxWidth: 800,
    minHeight: 400,
    margin: '1rem',
    display: 'flex',
    borderRadius: 12,
    alignItems: 'center',
  },
}));

// Initial login credentials
const initialValues = {
  email: '',
  password: '',
  remember: true,
};

// Form field validation schema
const validationSchema = Yup.object().shape({
  password: Yup.string().required('Password is required!'),
  email: Yup.string().email('Invalid Email address').required('Email is required!'),
});

const JwtLogin = () => {
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [showOTPDialog, setShowOTPDialog] = useState(false);
  const [otp, setOtp] = useState('');
  const [browserInfo, setBrowserInfo] = useState({
    browserName: '',
    language: '',
    platform: '',
  });
  const [userLocation, setUserLocation] = useState({
    city: '',
    state: '',
    country: '',
  });
  const theme = useTheme();
  const navigate = useNavigate();
  const { login } = useAuth();

  const handleClickShowPassword = () => {
    setShowPassword(!showPassword);
  };

  const handleMouseDownPassword = (event) => {
    event.preventDefault();
  };

  const handleOtpChange = (event) => {
    setOtp(event.target.value);
  };

  const fetchBrowserInfo = () => {
    const parsedUA = userAgentParser(navigator.userAgent);
    const browserName = parsedUA.browser.name;

    const browserInfo = {
      browserName,
      language: navigator.language,
      platform: navigator.platform,
    };
    setBrowserInfo(browserInfo);
  };

  useEffect(() => {
    fetchBrowserInfo();
  }, []);

  const handleFormSubmit = async (values) => {
    const primaryApiKey = 'b133c278534f4d998e419494d17c419f'; // ipgeolocation.io API key
    const fallbackApiKey = '68ceeb1d0f554717be5b50fc18799f58'; // abstractapi API key

    try {
      setLoading(true);

      const loginResponse = await login(values.email, values.password);

      if (loginResponse && loginResponse.Response) {
        SnackbarUtils.success('Login successful');

        const { user, accessToken } = loginResponse.Response;
        const { browserName } = browserInfo;

        // Fetch IP address
        let ip = '';
        try {
          const ipResponse = await axios.get('https://api.ipify.org?format=json');
          ip = ipResponse.data.ip;
        } catch (ipError) {
          SnackbarUtils.error('Failed to fetch IP address');
        }

        // Fetch user location
        let userLocation = {
          city: 'Unknown City',
          state: 'Unknown State',
          country: 'Unknown Country',
        };
        try {
          const locationResponse = await axios.get(
            `https://api.ipgeolocation.io/ipgeo?apiKey=${primaryApiKey}&ip=${ip}`
          );
          const { city, state_prov, country_name } = locationResponse.data;
          userLocation = { city, state: state_prov, country: country_name };
        } catch (primaryError) {
          if (primaryError.response && primaryError.response.status === 429) {
          } else {
            // console.error('Primary API failed:', primaryError);
            // console.log('Attempting to fetch location from fallback API...');

            try {
              const fallbackResponse = await axios.get(
                `https://ipgeolocation.abstractapi.com/v1/?api_key=${fallbackApiKey}&ip_address=${ip}`
              );
              const { city, region, country } = fallbackResponse.data;
              userLocation = { city, state: region, country };
            } catch (fallbackError) {
              if (fallbackError.response && fallbackError.response.status === 429) {
              } else {
                SnackbarUtils.error('Failed to fetch user location');
              }
            }
          }
        }

        // Prepare user data for logging
        const userData = {
          user_id: user.id,
          ip,
          browser: browserName,
          country: userLocation.country,
          state: userLocation.state,
          city: userLocation.city,
        };

        // Send activity log request
        try {
          const authToken = getAccessToken();
          const activityLogUrl = commonConfig.urls.userActivitylogs;

          const response = await axios.post(activityLogUrl, userData, {
            headers: {
              'Content-Type': 'application/json',
              Authorization: `Bearer ${authToken}`,
            },
          });
          navigate('/');
        } catch (activityLogError) {
          SnackbarUtils.error('Failed to log activity');
        }
      } else {
        setLoading(false);
        SnackbarUtils.error('Login failed');
      }
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error('Login failed');
    }
  };

  return (
    <ThemeProvider theme={lightModeTheme}>
      <JWTRoot>
        <Card className="card">
          <Grid container justifyContent="center" alignItems="center">
            <Grid item sm={6} xs={12}>
              <JustifyBox p={4} height="100%" sx={{ minWidth: 320 }}>
                <img src={KairosLogo} width="100%" alt="Brandlogo" />
              </JustifyBox>
            </Grid>
            <Grid item sm={6} xs={12}>
              <ContentBox>
                <Formik
                  onSubmit={handleFormSubmit}
                  initialValues={initialValues}
                  validationSchema={validationSchema}
                >
                  {({
                    values,
                    errors,
                    touched,
                    handleChange,
                    handleBlur,
                    handleSubmit,
                    setFieldTouched,
                    setFieldValue,
                  }) => (
                    <form onSubmit={handleSubmit}>
                      {verifyErrors(errors.email, touched.email)}
                      <Input
                        autoComplete="on"
                        type="email"
                        fullWidth
                        size="small"
                        name="email"
                        label="Email"
                        variant="standard"
                        onBlur={handleBlur}
                        onChange={(e) => {
                          setFieldValue('email', e.currentTarget.value);
                          setFieldTouched('email', true, false);
                        }}
                        value={values.email}
                        error={Boolean(errors.email && touched.email)}
                        placeholder="Email"
                        sx={{ mb: 3 }}
                      />
                      {verifyErrors(errors.password, touched.password)}
                      <Input
                        autoComplete="on"
                        type={showPassword ? 'text' : 'password'}
                        fullWidth
                        size="small"
                        name="password"
                        label="Password"
                        variant="outlined"
                        onBlur={handleBlur}
                        onChange={(e) => {
                          setFieldValue('password', e.currentTarget.value);
                          setFieldTouched('password', true, false);
                        }}
                        value={values.password}
                        error={Boolean(errors.password && touched.password)}
                        placeholder="Password"
                        endAdornment={
                          <InputAdornment position="end">
                            <IconButton
                              onClick={handleClickShowPassword}
                              onMouseDown={handleMouseDownPassword}
                            >
                              {showPassword ? <Visibility /> : <VisibilityOff />}
                            </IconButton>
                          </InputAdornment>
                        }
                        sx={{ mb: 3 }}
                      />
                      <FlexBox justifyContent="space-between">
                        <FlexBox gap={1} sx={{ marginRight: 'auto' }}>
                          {' '}
                          <Checkbox
                            size="small"
                            name="remember"
                            onChange={handleChange}
                            checked={values.remember}
                            sx={{ padding: 0 }}
                          />
                          <Paragraph>Remember Me</Paragraph>
                        </FlexBox>

                        <NavLink
                          to={commonRoutes.session.forgot_password}
                          style={{ color: theme.palette.primary.main }}
                        >
                          Forgot password?
                        </NavLink>
                      </FlexBox>

                      <LoadingButton
                        disabled={
                          !(
                            Object.keys(errors).length === 0 &&
                            Boolean(touched.email) &&
                            Boolean(touched.password)
                          )
                        }
                        type="submit"
                        color="primary"
                        loading={loading}
                        variant="contained"
                        sx={{ my: 2 }}
                      >
                        Login
                      </LoadingButton>
                      <Paragraph>
                        Don't have an account?
                        <NavLink
                          to={commonRoutes.session.signup}
                          style={{ color: theme.palette.primary.main, marginLeft: 5 }}
                        >
                          Register
                        </NavLink>
                      </Paragraph>
                    </form>
                  )}
                </Formik>
              </ContentBox>
            </Grid>
          </Grid>
        </Card>
      </JWTRoot>
    </ThemeProvider>
  );
};

export default JwtLogin;
