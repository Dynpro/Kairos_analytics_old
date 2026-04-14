import { Icon } from '@mui/material';
import SnackbarUtils from 'SnackbarUtils';
import commonConfig from 'app/components/commonConfig';
import axios from 'axios';
import { Formik } from 'formik';
import { useState } from 'react';
import * as Yup from 'yup';
import AppthemeLoadingBtn from '../ReusableComponents/AppThemeLoadingBtn';
import AppThemeTextField from '../ReusableComponents/AppThemeTextField';

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

const ResetPassword = ({ emailId, onClose }) => {
  const [loading, setLoading] = useState(false);

  const handleFormSubmit = async (values) => {
    setLoading(true);
    try {
      const response = await axios.post(commonConfig.urls.forgetPasswordChange, {
        email: emailId,
        otp: values.otp,
        password: values.password,
        repassword: values.repassword,
        entity_id: process.env.REACT_APP_env_entity_id,
      });
      setLoading(false);
      if (response.data && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data?.Message);
        onClose();
      } else {
        SnackbarUtils.error(' Invalid OTP..!!');
      }
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  };
  return (
    <div>
      <div style={{ position: 'absolute', right: 20, top: 20 }}>
        <Icon onClick={onClose} style={{ cursor: 'pointer' }}>
          close
        </Icon>
      </div>

      <Formik
        onSubmit={handleFormSubmit}
        initialValues={initialValues}
        validationSchema={validationSchema}
      >
        {({ values, errors, touched, handleBlur, handleSubmit, handleChange }) => (
          <form onSubmit={handleSubmit}>
            <AppThemeTextField
              fullWidth
              size="small"
              type="email"
              name="email"
              label="email"
              onBlur={handleBlur}
              value={emailId}
              disabled={true}
              onChange={handleChange}
              helperText={touched.email && errors.email}
              error={Boolean(errors.email && touched.email)}
              style={{ marginBottom: '20px', marginTop: '30px' }}
            />
            <AppThemeTextField
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
              style={{ marginBottom: '20px' }}
            />
            <AppThemeTextField
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
              style={{ marginBottom: '20px' }}
            />
            <AppThemeTextField
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
            />
            <div style={{ textAlign: 'center' }}>
              <AppthemeLoadingBtn
                type="submit"
                variant="contained"
                onClick={handleSubmit}
                sx={{ mt: 3 }}
                loading={loading}
              >
                Update Password
              </AppthemeLoadingBtn>
            </div>
          </form>
        )}
      </Formik>
    </div>
  );
};

export default ResetPassword;
