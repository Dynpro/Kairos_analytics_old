// import { useTheme } from '@emotion/react';
// import { LoadingButton } from '@mui/lab';
// import { Card, Grid, TextField, ThemeProvider, Button } from '@mui/material';
// import { Box, styled } from '@mui/system';
// import BgImg from 'app/components/AppLandingPage/assets/images/7.jpg';
// import { lightModeTheme } from 'app/components/MatxTheme/themeColors';
// import { Paragraph } from 'app/components/Typography';
// import commonRoutes from 'app/components/commonRoutes';
// import KairosLogo from 'app/components/icons/MyCharlie_Logo_light.png';
// import useAuth from 'app/hooks/useAuth';
// import { Formik } from 'formik';
// import { useRef, useState } from 'react';
// import * as Yup from 'yup';
// import QRCodeGenerator from './QRCodePage';
// import { Link } from 'react-router-dom';
// import { NavLink } from 'react-router-dom';
// import { useNavigate } from 'react-router-dom'; // Add this import for useNavigate

// const FlexBox = styled(Box)(() => ({ display: 'flex', alignItems: 'center' }));

// const JustifyBox = styled(FlexBox)(() => ({ justifyContent: 'center' }));

// const ContentBox = styled(JustifyBox)(() => ({
//   height: '100%',
//   padding: '32px',
//   background: 'rgba(0, 0, 0, 0.01)',
// }));

// const JWTRegister = styled(JustifyBox)(() => ({
//   //background: '#1A2038',
//   backgroundImage: `url(${BgImg})`,
//   backgroundSize: 'cover',
//   backgroundRepeat: 'no-repeat',
//   minHeight: '100vh !important',
//   '& .card': {
//     maxWidth: 800,
//     minHeight: 400,
//     margin: '1rem',
//     display: 'flex',
//     borderRadius: 12,
//     alignItems: 'center',
//   },
// }));

// // initial login credentials
// const initialValues = {
//   firstname: '',
//   lastname: '',
//   email: '',
//   password: '',
//   confirm_password: '',
//   group_code: '',
//   entity_id: process.env.REACT_APP_env_entity_id,
// };

// // form field validation schema
// const validationSchema = Yup.object().shape({
//   firstname: Yup.string().required('Required'),
//   lastname: Yup.string().required('Required'),
//   email: Yup.string().email('Email is invalid').required('Email is required'),
//   password: Yup.string()
//     .matches(
//       /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})/,
//       'The password must be at least 8 characters.Password must contain at least one number and both uppercase and lowercase letters and one special character.'
//     )
//     .required('Password is required'),
//   confirm_password: Yup.string()
//     .oneOf([Yup.ref('password'), null], 'The confirm password and password must match')
//     .required('Confirm password is required'),
//   group_code: Yup.string().required('Group code is required'),
// });

// const JwtRegister = () => {
//   const navigate = useNavigate(); // Define the navigate variable using useNavigate
//   const theme = useTheme();
//   const { register } = useAuth();
//   const [loading, setLoading] = useState(false);
//   const [registrationDetails, setRegistrationDetails] = useState(initialValues);
//   const [disableSubmit, setDisableSubmit] = useState(true);
//   const [userEmail, setUserEmail] = useState('');

//   const handleFormSubmit = async (values) => {
//     try {
//       register(
//         values.firstname,
//         values.lastname,
//         values.email,
//         values.password,
//         values.confirm_password,
//         values.group_code,
//         values.entity_id
//       );
//       setLoading(false);
//     } catch (e) {
//       //console.log(e);
//       setLoading(false);
//       setRegistrationDetails(values);
//     }
//     navigate('/qr-code-page', { state: values });
//   };

//   return (
//     <ThemeProvider theme={lightModeTheme}>
//       <JWTRegister>
//         <Card className="card">
//           <Grid container>
//             <Grid item sm={6} xs={12}>
//               <ContentBox>
//                 <img width="100%" alt="Register" src={KairosLogo} />
//               </ContentBox>
//             </Grid>

//             <Grid item sm={6} xs={12}>
//               <Box p={4} height="100%">
//                 <Formik
//                   onSubmit={handleFormSubmit}
//                   initialValues={initialValues}
//                   validationSchema={validationSchema}
//                 >
//                   {({ values, errors, touched, handleChange, handleBlur, handleSubmit }) => (
//                     <form onSubmit={handleSubmit}>
//                       <TextField
//                         fullWidth
//                         size="small"
//                         type="text"
//                         name="firstname"
//                         label="First Name"
//                         variant="outlined"
//                         onBlur={handleBlur}
//                         value={values.firstname}
//                         onChange={handleChange}
//                         helperText={touched.firstname && errors.firstname}
//                         error={Boolean(errors.firstname && touched.firstname)}
//                         sx={{ mb: 3 }}
//                       />
//                       <TextField
//                         fullWidth
//                         size="small"
//                         type="text"
//                         name="lastname"
//                         label="Last Name"
//                         variant="outlined"
//                         onBlur={handleBlur}
//                         value={values.lastname}
//                         onChange={handleChange}
//                         helperText={touched.lastname && errors.lastname}
//                         error={Boolean(errors.lastname && touched.lastname)}
//                         sx={{ mb: 3 }}
//                       />
//                       <TextField
//                         fullWidth
//                         size="small"
//                         type="email"
//                         name="email"
//                         label="Email"
//                         variant="outlined"
//                         onBlur={handleBlur}
//                         value={values.email}
//                         onChange={handleChange}
//                         helperText={touched.email && errors.email}
//                         error={Boolean(errors.email && touched.email)}
//                         sx={{ mb: 3 }}
//                       />
//                       <TextField
//                         fullWidth
//                         size="small"
//                         type="password"
//                         name="password"
//                         label="Password"
//                         variant="outlined"
//                         onBlur={handleBlur}
//                         value={values.password}
//                         onChange={handleChange}
//                         helperText={touched.password && errors.password}
//                         error={Boolean(errors.password && touched.password)}
//                         sx={{ mb: 3 }}
//                       />
//                       <TextField
//                         fullWidth
//                         size="small"
//                         type="password"
//                         name="confirm_password"
//                         label="ConfirmPassword"
//                         variant="outlined"
//                         onBlur={handleBlur}
//                         value={values.confirm_password}
//                         onChange={handleChange}
//                         helperText={touched.confirm_password && errors.confirm_password}
//                         error={Boolean(errors.confirm_password && touched.confirm_password)}
//                         sx={{ mb: 3 }}
//                       />
//                       <TextField
//                         fullWidth
//                         size="small"
//                         name="group_code"
//                         type="text"
//                         label="Group Code"
//                         variant="outlined"
//                         onBlur={handleBlur}
//                         value={values.group_code}
//                         onChange={handleChange}
//                         helperText={touched.group_code && errors.group_code}
//                         error={Boolean(errors.group_code && touched.group_code)}
//                         sx={{ mb: 2 }}
//                       />
//                       <LoadingButton
//                         type="submit"
//                         color="primary"
//                         variant="contained"
//                         sx={{ mb: 4, mt: 3 }}
//                         // onClick={handleSubmit}
//                         loading={loading}
//                       >
//                         Register
//                       </LoadingButton>

//                       <Paragraph>
//                         Already have an account?
//                         <NavLink
//                           to={commonRoutes.session.signin}
//                           style={{ color: theme.palette.primary.main, marginLeft: 5 }}
//                         >
//                           Login
//                         </NavLink>
//                       </Paragraph>
//                     </form>
//                   )}
//                 </Formik>
//               </Box>
//             </Grid>
//           </Grid>
//         </Card>
//       </JWTRegister>
//     </ThemeProvider>
//   );
// };

// export default JwtRegister;

import { useTheme } from '@emotion/react';
import { LoadingButton } from '@mui/lab';
import { Card, Grid, TextField, ThemeProvider } from '@mui/material';
import { Box, styled } from '@mui/system';
import BgImg from 'app/components/AppLandingPage/assets/images/7.jpg';
import { lightModeTheme } from 'app/components/MatxTheme/themeColors';
import { Paragraph } from 'app/components/Typography';
import commonRoutes from 'app/components/commonRoutes';
import KairosLogo from 'app/components/icons/kairos.png';
import useAuth from 'app/hooks/useAuth';
import { Formik } from 'formik';
import { useRef, useState } from 'react';
import ReCAPTCHA from 'react-google-recaptcha';
import { NavLink } from 'react-router-dom';
import * as Yup from 'yup';

const FlexBox = styled(Box)(() => ({ display: 'flex', alignItems: 'center' }));

const JustifyBox = styled(FlexBox)(() => ({ justifyContent: 'center' }));

const ContentBox = styled(JustifyBox)(() => ({
  height: '100%',
  padding: '32px',
  background: 'rgba(0, 0, 0, 0.01)',
}));

const JWTRegister = styled(JustifyBox)(() => ({
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
  firstname: '',
  lastname: '',
  email: '',
  password: '',
  confirm_password: '',
  group_code: '',
  entity_id: process.env.REACT_APP_env_entity_id,
};

// form field validation schema
const validationSchema = Yup.object().shape({
  firstname: Yup.string().required('Required'),
  lastname: Yup.string().required('Required'),
  email: Yup.string().email('Email is invalid').required('Email is required'),
  password: Yup.string()
    .matches(
      /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})/,
      'The password must be at least 8 characters.Password must contain at least one number and both uppercase and lowercase letters and one special character.'
    )
    .required('Password is required'),
  confirm_password: Yup.string()
    .oneOf([Yup.ref('password'), null], 'The confirm password and password must match')
    .required('Confirm password is required'),
  group_code: Yup.string().required('Group code is required'),
});
const JwtRegister = () => {
  const theme = useTheme();
  const { register } = useAuth();
  const [loading, setLoading] = useState(false);
  const [disableSubmit, setDisableSubmit] = useState(true);

  const captchaRef = useRef(null);

  const handleFormSubmit = (values) => {
    setLoading(true);
    try {
      register(
        values.firstname,
        values.lastname,
        values.email,
        values.password,
        values.confirm_password,
        values.group_code
      );
      setLoading(false);
    } catch (e) {
      //console.log(e);
      setLoading(false);
    }
  };

  return (
    <ThemeProvider theme={lightModeTheme}>
      <JWTRegister>
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
                  {({ values, errors, touched, handleChange, handleBlur, handleSubmit }) => (
                    <form onSubmit={handleSubmit}>
                      <TextField
                        fullWidth
                        size="small"
                        type="text"
                        name="firstname"
                        label="First Name"
                        variant="outlined"
                        onBlur={handleBlur}
                        value={values.firstname}
                        onChange={handleChange}
                        helperText={touched.firstname && errors.firstname}
                        error={Boolean(errors.firstname && touched.firstname)}
                        sx={{ mb: 3 }}
                      />
                      <TextField
                        fullWidth
                        size="small"
                        type="text"
                        name="lastname"
                        label="Last Name"
                        variant="outlined"
                        onBlur={handleBlur}
                        value={values.lastname}
                        onChange={handleChange}
                        helperText={touched.lastname && errors.lastname}
                        error={Boolean(errors.lastname && touched.lastname)}
                        sx={{ mb: 3 }}
                      />
                      <TextField
                        fullWidth
                        size="small"
                        type="email"
                        name="email"
                        label="Email"
                        variant="outlined"
                        onBlur={handleBlur}
                        value={values.email}
                        onChange={handleChange}
                        helperText={touched.email && errors.email}
                        error={Boolean(errors.email && touched.email)}
                        sx={{ mb: 3 }}
                      />
                      <TextField
                        fullWidth
                        size="small"
                        type="password"
                        name="password"
                        label="Password"
                        variant="outlined"
                        onBlur={handleBlur}
                        value={values.password}
                        onChange={handleChange}
                        helperText={touched.password && errors.password}
                        error={Boolean(errors.password && touched.password)}
                        sx={{ mb: 3 }}
                      />
                      <TextField
                        fullWidth
                        size="small"
                        type="password"
                        name="confirm_password"
                        label="ConfirmPassword"
                        variant="outlined"
                        onBlur={handleBlur}
                        value={values.confirm_password}
                        onChange={handleChange}
                        helperText={touched.confirm_password && errors.confirm_password}
                        error={Boolean(errors.confirm_password && touched.confirm_password)}
                        sx={{ mb: 3 }}
                      />
                      <TextField
                        fullWidth
                        size="small"
                        name="group_code"
                        type="text"
                        label="Group Code"
                        variant="outlined"
                        onBlur={handleBlur}
                        values={values.group_code}
                        onChange={handleChange}
                        helperText={touched.group_code && errors.group_code}
                        error={Boolean(errors.group_code && touched.group_code)}
                        sx={{ mb: 2 }}
                      />
                      <FlexBox gap={1} alignItems="center"></FlexBox>

                      <ReCAPTCHA
                        sitekey={process.env.REACT_APP_SITE_KEY}
                        ref={captchaRef}
                        onChange={(value) => {
                          if (value) setDisableSubmit(false);
                        }}
                        onExpired={() => setDisableSubmit(true)}
                      />
                      <LoadingButton
                        disabled={
                          !(
                            Object.keys(errors).length === 0 &&
                            Boolean(touched.firstname) &&
                            Boolean(touched.lastname) &&
                            Boolean(touched.email) &&
                            Boolean(touched.password) &&
                            Boolean(touched.confirm_password) &&
                            Boolean(touched.group_code) &&
                            !disableSubmit
                          )
                        }
                        type="submit"
                        color="primary"
                        loading={loading}
                        variant="contained"
                        /*                       onClick={handleSubmit} */ sx={{ mb: 2, mt: 3 }}
                      >
                        Register
                      </LoadingButton>

                      <Paragraph>
                        Already have an account?
                        <NavLink
                          to={commonRoutes.session.signin}
                          style={{ color: theme.palette.primary.main, marginLeft: 5 }}
                        >
                          Login
                        </NavLink>
                      </Paragraph>
                    </form>
                  )}
                </Formik>
              </Box>
            </Grid>
          </Grid>
        </Card>
      </JWTRegister>
    </ThemeProvider>
  );
};

export default JwtRegister;
