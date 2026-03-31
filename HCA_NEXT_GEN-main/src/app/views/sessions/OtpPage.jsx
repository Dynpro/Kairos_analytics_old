// import React, { useState } from 'react';
// import { Grid, Card, TextField, Button, ThemeProvider } from '@mui/material';
// import { Box, styled } from '@mui/system';
// import { useTheme } from '@emotion/react';
// import SnackbarUtils from 'SnackbarUtils';
// import BgImg from 'app/components/AppLandingPage/assets/images/5.jpg';
// import { lightModeTheme } from 'app/components/MatxTheme/themeColors';
// import { Paragraph } from 'app/components/Typography';
// import { Formik } from 'formik';
// import { useLocation, useNavigate } from 'react-router-dom';
// import axios from 'axios';
// import * as Yup from 'yup';
// import commonConfig from 'app/components/commonConfig';
// import commonRoutes from 'app/components/commonRoutes';

// const FlexBox = styled(Box)(() => ({ display: 'flex', alignItems: 'center' }));

// const JustifyBox = styled(FlexBox)(() => ({ justifyContent: 'center' }));

// const ContentBox = styled(JustifyBox)(() => ({
//   height: '100%',
//   padding: '32px',
//   background: 'rgba(0, 0, 0, 0.01)',
// }));

// const OtpPageRoot = styled(JustifyBox)(() => ({
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

// const initialValues = {
//   validOTP: '',
// };

// const validationSchema = Yup.object().shape({
//   validOTP: Yup.string('Enter the 4 digit OTP').required('Please enter the OTP'),
// });

// const OtpPage = () => {
//   const location = useLocation();
//   const theme = useTheme();
//   const [loading, setLoading] = useState(false);
//   const obj = { ...location.state };
//   const navigate = useNavigate();

//   // Replace 'validateOtp' and 'resendOtp' with your actual API endpoints
//   const validateOtpURL = `${commonConfig.urls.validateOtp}`;
//   const resendOtpURL = `${commonConfig.urls.resendOtp}`;

//   const handleFormSubmit = async (values) => {
//     try {
//       const response = await axios.post(validateOtpURL, { ...obj, otp: values.validOTP });
//       if (response && response.data.Status === 401) {
//         SnackbarUtils.error(response.data?.Message || 'Invalid');
//       }
//       setLoading(false);
//       if (response && response.data.Status === 'Success') {
//         SnackbarUtils.success(response.data.Message);
//         navigate(commonRoutes.landingPage);
//       }
//     } catch (e) {
//       // console.log(e);
//     }
//   };

//   const handleResendSubmt = async () => {
//     try {
//       const response = await axios.post(resendOtpURL, obj);
//       if (response.data?.Status === 402) {
//         SnackbarUtils.error('Too many attempts. Register again');
//         navigate(commonRoutes.session.signup);
//       } else if (response.data?.Status === 401) {
//         SnackbarUtils.error('Invalid otp. try again');
//       } else if (response.data?.Code === 200) {
//         SnackbarUtils.success('OTP sent. Kindly check your mailbox.');
//       }
//     } catch (error) {
//       SnackbarUtils.error(error?.message || 'Something went wrong!!');
//     }
//   };

//   return (
//     <ThemeProvider theme={lightModeTheme}>
//       <OtpPageRoot>
//         <Card className="card">
//           <Grid container>
//             <Grid item sm={6} xs={12}>
//               <ContentBox>
//                 <Paragraph>Kindly enter the OTP sent to your Email Id.</Paragraph>
//               </ContentBox>
//             </Grid>
//             <Grid item sm={6} xs={12}>
//               <Box p={4} height="100%">
//                 <Formik
//                   onSubmit={handleFormSubmit}
//                   initialValues={initialValues}
//                   validationSchema={validationSchema}
//                 >
//                   {({ values, errors, touched, handleBlur, handleSubmit, handleChange }) => (
//                     <form onSubmit={handleSubmit}>
//                       <TextField
//                         fullWidth
//                         size="small"
//                         type="number"
//                         name="validOTP"
//                         label="OTP"
//                         variant="outlined"
//                         onBlur={handleBlur}
//                         value={values.validOTP}
//                         onChange={handleChange}
//                         error={Boolean(errors.validOTP && touched.validOTP)}
//                         sx={{ mb: 4 }}
//                       />
//                       {Boolean(errors.validOTP && touched.validOTP) && (
//                         <div style={{ color: 'red' }}>* {errors.validOTP}</div>
//                       )}
//                       <Button
//                         type="submit"
//                         variant="contained"
//                         color="primary"
//                         sx={{ mb: 4, mt: 3 }}
//                         onClick={handleSubmit}
//                       >
//                         Submit
//                       </Button>
//                     </form>
//                   )}
//                 </Formik>
//                 <Button
//                   onClick={handleResendSubmt}
//                   style={{
//                     cursor: 'pointer',
//                     margin: '-60px 0  30px',
//                     float: 'right',
//                     color: theme.palette.primary.main,
//                   }}
//                 >
//                   Resend OTP
//                 </Button>
//               </Box>
//             </Grid>
//           </Grid>
//         </Card>
//       </OtpPageRoot>
//     </ThemeProvider>
//   );
// };

// export default OtpPage;
