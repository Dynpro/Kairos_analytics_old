import { Box, Icon } from '@mui/material';
import { Formik } from 'formik';
import { useReducer, useState } from 'react';
import * as Yup from 'yup';

import commonConfig from '../commonConfig';

import SnackbarUtils from 'SnackbarUtils';
import useApiOnce from 'app/hooks/useApiOnce';
import useRefreshData from 'app/hooks/useRefreshData';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { useSelector } from 'react-redux';
import AppthemeLoadingBtn from '../ReusableComponents/AppThemeLoadingBtn';
import AppThemeTextField from '../ReusableComponents/AppThemeTextField';
import AvatarUpload from './AvatarUpload';

const initialValues = {
  first_name: '',
  last_name: '',
  email: '',
  file: null,
  is_deleted: 0,
};

const validationSchema = Yup.object().shape({
  first_name: Yup.string().required('Kindly enter your First Name').label('First Name'),
  last_name: Yup.string().required('Kindly enter your Last Name').label('Last Name'),
  email: Yup.string()
    .email('Kindly enter a valid email')
    .required('Kindly enter a valid email')
    .label('Email'),
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
  if (responseObj.first_name && responseObj.last_name && responseObj.email) {
    return {
      ...initialValues,
      first_name: responseObj.first_name,
      last_name: responseObj.last_name,
      email: responseObj.email,
      file: responseObj.profile_pic,
    };
  }
  return null;
}

const EditUserProfile = ({ onclose }) => {
  const [state, dispatch] = useReducer(reducer, {
    loading: false,
    open: false,
    responseError: '',
    hasError: false,
  });
  const [loading, setLoading] = useState(false);
  const [open, setOpen] = useState(false);

  const { data: userObj } = useApiOnce(commonConfig.urls.getUserProfile);
  const { handleRefreshData } = useRefreshData(false);

  const user_id = useSelector((state) => state.userDetails?.user?.id);

  async function sendDataToServer(recdData = {}) {
    if (recdData.file === '') {
      delete recdData.file;
    }
    if (recdData.is_deleted === 0) {
      delete recdData.is_deleted;
    }
    const data = new FormData();
    Object.keys(recdData).forEach((item) => {
      if (Array.isArray(recdData[item])) {
        for (const itemArr in recdData[item])
          data.append(`${item}[${itemArr}]`, recdData[item][itemArr]);
      } else data.append(item, recdData[item]);
    });
    setLoading(true);
    const authToken = getAccessToken();
    const response = await axios.post(commonConfig.urls.updateUserProfile, data, {
      headers: { Authorization: `Bearer ${authToken}` },
    });
    setLoading(false);
    if (response && response.data.Status === 'Failed') {
      SnackbarUtils.error(Object.values(response.data.Errors).map((item) => item.toString()));
    }
    if (response && response.data.Status === 'Success') {
      SnackbarUtils.success(response.data.Message);
      handleRefreshData();
      setOpen(true);
      onclose();
    }
  }

  const returnFormField = (
    id,
    label,
    placeholder,
    onChange,
    onBlur,
    error = false,
    touched = false,
    value,
    type = 'text',
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
        type={type}
      />
      {verifyErrors(error, touched)}
    </div>
  );
  const verifyErrors = (errorName, touchedName) => {
    if (Boolean(touchedName && errorName)) return <div style={{ color: 'red' }}>* {errorName}</div>;
    return null;
  };
  return (
    <Formik
      enableReinitialize={true}
      initialValues={createInitalValues(userObj) || initialValues}
      validationSchema={validationSchema}
      onSubmit={(values) => {
        const value = {
          ...values,
          user_id,
          file: values.file === userObj.profile_pic ? '' : values.file,
        };
        sendDataToServer(value);
      }}
    >
      {({ errors, touched, values, handleBlur, handleChange, handleSubmit }) => {
        return (
          <Box
            component="form"
            sx={{
              '& .MuiTextField-root': { m: 1, width: '25ch' },
              width: '100%',
            }}
            noValidate
            autoComplete="off"
          >
            <div style={{ position: 'absolute', right: 20, top: 20 }}>
              <Icon onClick={onclose} style={{ cursor: 'pointer' }}>
                close
              </Icon>
            </div>
            <p
              style={{
                color: 'rgba(52, 49, 76, 0.54)',
                textAlign: 'center',
              }}
            >
              EDIT PROFILE
            </p>
            <div>
              <AvatarUpload />
            </div>
            <div style={{ marginLeft: '45px' }}>
              {returnFormField(
                'first_name',
                'First Name',
                'Enter First Name',
                handleChange,
                handleBlur,
                errors.first_name,
                touched.first_name,
                values['first_name']
              )}

              {returnFormField(
                'last_name',
                'Last Name',
                'Enter Last Name',
                handleChange,
                handleBlur,
                errors.last_name,
                touched.last_name,
                values['last_name']
              )}
              {returnFormField(
                'email',
                'Email Id',
                'Enter Email Id',
                handleChange,
                handleBlur,
                errors.email,
                touched.email,
                values['email']
              )}
            </div>
            <div
              style={{
                flex: 1,
                textAlign: 'center',
              }}
            >
              <AppthemeLoadingBtn
                type="submit"
                loading={state.loading}
                variant="contained"
                sx={{ my: 2 }}
                onClick={handleSubmit}
              >
                Update
              </AppthemeLoadingBtn>
            </div>
          </Box>
        );
      }}
    </Formik>
  );
};

export default EditUserProfile;
