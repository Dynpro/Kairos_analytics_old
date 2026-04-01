// import React, { useState, useEffect } from 'react';
// import axios from 'axios';
// import { Button, TextField } from '@mui/material';
// import { Box, styled, ThemeProvider } from '@mui/system';
// import BgImg from 'app/components/AppLandingPage/assets/images/5.jpg';
// import { lightModeTheme } from 'app/components/MatxTheme/themeColors';
// import SnackbarUtils from 'SnackbarUtils';
// import { getAccessToken } from 'app/utils/utils';
// import { useLocation } from 'react-router-dom';

// const Container = styled(Box)(({ theme }) => ({
//   display: 'flex',
//   flexDirection: 'column',
//   alignItems: 'center',
//   backgroundImage: `url(${BgImg})`,
//   backgroundSize: 'cover',
//   backgroundRepeat: 'no-repeat',
//   minHeight: '100vh',
//   paddingTop: '50px',
// }));

// const Heading = styled('h1')(({ theme }) => ({
//   color: theme.palette.primary.main,
//   backgroundColor: 'rgba(255, 255, 255, 0.8)',
//   padding: '20px',
//   borderRadius: '8px',
// }));

// const QRCodeGenerator = () => {
//   const location = useLocation();
//   const [qrCodeData, setQrCodeData] = useState('');
//   const [qrCodeImage, setQRCodeImage] = useState('');
//   const [otp, setOtp] = useState('');
//   const [secret, setSecret] = useState('');
//   const { state } = location;
//   const [showSecretKeyInput, setShowSecretKeyInput] = useState(false);
//   const authToken = getAccessToken();
//   const [registrationData, setRegistrationData] = useState(null);
//   const email = state?.email || registrationData?.email;

//   useEffect(() => {
//     if (state && state.email) {
//       generateQRCode(state.email);
//     }
//   }, [state]);


//   const generateQRCode = async (email) => {
//     try {
//       const headers = {
//         Authorization: `Bearer ${authToken}`,
//         'Content-Type': 'application/json',
//       };   

//       const requestBody = {
//         email: email,
//       };

//       const qrCodeResponse = await axios.post(
//         'https://hcaapi.kairosrp.com/api/QrCode_NewReg',
//         requestBody,
//         { headers }
//       );

//       const qrCodeData = qrCodeResponse.data.Response.body;
//       if (qrCodeData) {
//         const QRCodesecretKey = qrCodeResponse.data.Response.additionalInfo.secret;
//         setQrCodeData(qrCodeData);
//         setSecret(QRCodesecretKey);
//         setQRCodeImage(`data:image/png;base64,${qrCodeData}`);
//       } else {
//         SnackbarUtils.error('Failed to generate QR code');
//       }
//     } catch (error) {
//       console.error('Error generating QR code:', error);
//       SnackbarUtils.error(error?.message || 'Something went wrong!!');
//     }
//   };

//   const handleOtpChange = (event) => {
//     setOtp(event.target.value); // Update the OTP state when input changes
//   }; 

//   const handleVerifyOtp = async () => {
//     try {
//       if (!otp) {
//         SnackbarUtils.error('Please enter OTP!');
//         return;
//       }
  
//       const headers = {
//         'Content-Type': 'application/json',
//       };
      
//       const requestBody = {
//         email: email,
//         otp: otp,
//       };
  
//       const otpVerificationResponse = await axios.post(
//         'https://hcaapi.kairosrp.com/api/ValidateOTP_NewReg',
//         requestBody,
//         { headers }
//       );
  
//       // Check if OTP verification is successful
//       if (otpVerificationResponse.data.Response) {
//         const { Status, Message } = otpVerificationResponse.data.Response;
//         if (Status === 'Success') {
//           SnackbarUtils.success(Message || 'OTP Verified Successfully!');
  
//           // Save registration data to the API
//           const registrationData = {
//             email: state.email,
//             firstname: state.firstname,
//             lastname: state.lastname,
//             password: state.password,
//             confirm_password: state.confirm_password,
//             group_code: state.group_code,
//             entity_id: process.env.REACT_APP_env_entity_id,
//           };
  
//            await axios.post(
//             'https://hcaapi.kairosrp.com/api/update_registration',
//             registrationData,
//             { headers }
//           );
          
//           // Redirect to login or another page
//           window.location.href = '/login';
//         } else {
//           SnackbarUtils.error(Message || 'Invalid OTP!');
//         }
//       } } catch (error) {
//         console.error('Error verifying OTP or updating registration:', error);
//         SnackbarUtils.error('Error verifying OTP or updating registration');
//       }
//   };
  

//   const handleCantScanQRCode = async () => {
//     setShowSecretKeyInput(true);
//   };

//   return (
//     <ThemeProvider theme={lightModeTheme}>
//       <Container>
//         <Heading>QR CODE</Heading>
//         {qrCodeImage && <img src={qrCodeImage} alt="QR Code" />}
//         <Button
//           onClick={handleCantScanQRCode}
//           style={{ marginTop: '20px', textDecoration: 'underline', cursor: 'pointer' }}
//         >
//           CAN'T SCAN THE QR CODE?
//         </Button>
//         {showSecretKeyInput && (
//           <div style={{ marginTop: '20px', textAlign: 'center' }}>
//             <TextField
//               margin="dense"
//               id="secretKey"
//               type="text"
//               fullWidth
//               value={secret}
//               variant="outlined"
//               style={{ marginBottom: '20px', width: '300px' }}
//               InputProps={{
//                 readOnly: true, // Ensure the input is read-only
//               }}
//             />
//           </div>
//         )}

//         {qrCodeImage && (
//           <div style={{ marginTop: '20px' }}>
//             <input
//               type="text"
//               placeholder="Enter OTP"
//               value={otp}
//               onChange={handleOtpChange}
//               variant="outlined"
//               style={{ width: '300px', padding: '10px', borderRadius: '4px', marginBottom: '20px' }}
//             />
//             <Button
//               variant="contained"
//               color="primary"
//               onClick={handleVerifyOtp}
//               sx={{ fontSize: '1.2rem', padding: '15px 30px', borderRadius: '4px' }}
//             >
//               Verify OTP
//             </Button>
//           </div>
//         )}
//       </Container>
//     </ThemeProvider>
//   );
// };

// export default QRCodeGenerator;


