// // useAuth.js

// import { useState } from 'react'; // Assuming you're using React hooks
// // useAuth.js

// import axios from 'axios';

// const useAuth = () => {
//   const login = async (email, password) => {
//     try {
//       const response = await axios.post('https://hcaapi.kairosrp.com/api/login', {
//         email,
//         password
//       });
//       // Handle successful login
//       return response.data;
//     } catch (error) {
//       // Handle login error
//       throw error;
//     }
//   };

//   const check2FA = async (email) => {
//     try {
//       const response = await axios.get('https://hcaapi.kairosrp.com/api/twofa_check', {
//         params: { email }
//       });
//       return response.data;
//     } catch (error) {
//       // Handle error
//       throw error;
//     }
//   };

//   const validate2FAOTP = async (email, otp) => {
//     try {
//       const response = await axios.post('https://hcaapi.kairosrp.com/api/validate_2fa_otp', {
//         email,
//         otp
//       });
//       // Handle successful OTP validation
//       return response.data;
//     } catch (error) {
//       // Handle OTP validation error
//       throw error;
//     }
//   };

//   return { login, check2FA, validate2FAOTP };
// };

// export default useAuth;

