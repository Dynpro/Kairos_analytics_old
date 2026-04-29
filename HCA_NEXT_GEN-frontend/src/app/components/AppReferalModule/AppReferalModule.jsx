import 'date-fns';
import { useState } from 'react';
import SnackbarUtils from 'SnackbarUtils';

import commonConfig from '../commonConfig';

import { DatePicker } from '@mui/lab';
import AdapterDateFns from '@mui/lab/AdapterDateFns';
import LocalizationProvider from '@mui/lab/LocalizationProvider';
import { Box, Card, Grid, MenuItem, styled, TextField } from '@mui/material';
import { Breadcrumb } from 'app/components';
import useApiOnce from 'app/hooks/useApiOnce';
import { getAccessToken } from 'app/utils/utils';
import axios from 'axios';
import { Formik } from 'formik';
import { useSelector } from 'react-redux';
import AppGoBackBtn from '../ReusableComponents/AppGoBackBtn';
import AppthemeLoadingBtn from '../ReusableComponents/AppThemeLoadingBtn';
import AppThemeTextField from '../ReusableComponents/AppThemeTextField';
import referralUtil from './util';

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
}));

export default function AppReferralModule() {
  const [loading, setLoading] = useState(false);
  const loggedInUserEmailId = useSelector((state) => state.userDetails?.user?.email);
  const referralPermission = useSelector(
    (state) => state.userAccessPermissions?.userPermissions?.referral
  );
  const { data: psClientList } = useApiOnce(commonConfig.urls.getclientList);

  async function sendDataToServer(data, { resetForm }) {
    const authToken = getAccessToken();

    const formattedData = {
      ...data,
      dob: referralUtil.formatDateString(data.dob),
      primary_insured_dob: referralUtil.formatDateString(data.primary_insured_dob),
      eligibility_effective_date: referralUtil.formatDateString(data.eligibility_effective_date),
      eligibility_termination_date: referralUtil.formatDateString(
        data.eligibility_termination_date
      ),
      loadby: loggedInUserEmailId,
    };
    try {
      setLoading(true);
      const response = await axios.post(commonConfig.urls.referral_uuid, formattedData, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      setLoading(false);
      if (response && response.data.Status === 'Failed') {
        //   SnackbarUtils.error(Object.values(response.data.Errors).map((item) => item.toString()));
        SnackbarUtils.error('Something went wrong : Server Side');
      }
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data?.Response?.message || 'Successfully created');
        resetForm();
      }
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.message || 'Something went wrong :  : Client Side!!');
    }
  }

  return (
    <>
      {referralPermission === 1 && (
        <>
          <Box className="breadcrumb" sx={{ m: 1 }}>
            <Breadcrumb routeSegments={[{ name: 'Referral' }]} />
          </Box>
          <Container sx={{ display: 'flex', justifyContent: 'center', minWidth: '470px' }}>
            <Formik
              initialValues={referralUtil.initialValues}
              validationSchema={referralUtil.validationSchema}
              onSubmit={sendDataToServer}
            >
              {({
                errors,
                touched,
                handleSubmit,
                handleChange,
                values,
                handleBlur,
                setFieldValue,
              }) => {
                return (
                  <Card sx={{ px: 3, pt: 1, pb: 2, width: '100%', maxWidth: '700px' }}>
                    <Box
                      component="form"
                      sx={{
                        '& .MuiTextField-root': { my: 1, width: '25ch' },
                        width: '100%',
                      }}
                      noValidate
                      autoComplete="off"
                    >
                      <div>
                        <AppThemeTextField
                          defaultValue={''}
                          id="clientname"
                          name="clientname"
                          value={values.clientname}
                          style={{ width: '100%' }}
                          select
                          label="Client"
                          placeholder="Select Client"
                          onChange={handleChange}
                        >
                          {psClientList.map((option, index) => (
                            <MenuItem key={index} value={option.folder_name}>
                              {option.folder_name}
                            </MenuItem>
                          ))}
                        </AppThemeTextField>
                        {referralUtil.verifyErrors(errors.clientname, touched.clientname)}
                      </div>
                      <div>
                        <AppThemeTextField
                          id="firstname"
                          required={true}
                          label="First Name"
                          placeholder="Enter First Name"
                          style={{ width: '100%' }}
                          value={values.firstname}
                          onChange={handleChange('firstname')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors && touched && touched.firstname && errors.firstname
                          )}
                        />
                        {referralUtil.verifyErrors(errors.firstname, touched.firstname)}
                      </div>
                      <div>
                        <AppThemeTextField
                          id="lastname"
                          required={true}
                          label="Last Name"
                          placeholder="Enter Last Name"
                          style={{ width: '100%' }}
                          value={values.lastname}
                          onChange={handleChange('lastname')}
                          onBlur={handleBlur}
                          error={Boolean(errors && touched && touched.lastname && errors.lastname)}
                        />
                        {referralUtil.verifyErrors(errors.lastname, touched.lastname)}
                      </div>
                      <div>
                        <LocalizationProvider dateAdapter={AdapterDateFns}>
                          <Grid container sx={{ width: '60%' }} justify="space-around">
                            <DatePicker
                              value={values.dob}
                              onChange={(value) => setFieldValue('dob', value, true)}
                              renderInput={(props) => (
                                <TextField
                                  {...props}
                                  variant="standard"
                                  id="dob"
                                  label="Date of Birth"
                                  error={Boolean(touched.dob && errors.dob)}
                                />
                              )}
                            />
                          </Grid>
                        </LocalizationProvider>
                        {referralUtil.verifyErrors(errors.dob, touched.dob)}
                      </div>
                      <div>
                        <AppThemeTextField
                          defaultValue={''}
                          id="gender"
                          required={true}
                          name="gender"
                          value={values.gender}
                          style={{ width: '100%' }}
                          select
                          label="Gender"
                          placeholder="Select Gender"
                          onChange={handleChange}
                        >
                          {referralUtil.genders.map((option, index) => (
                            <MenuItem key={index} value={option.value}>
                              {option.name}
                            </MenuItem>
                          ))}
                        </AppThemeTextField>
                        {referralUtil.verifyErrors(errors.gender, touched.gender)}
                      </div>

                      <div>
                        <AppThemeTextField
                          id="member_phone_number"
                          required={false}
                          label="Member Phone Number"
                          placeholder="Enter Phone Number"
                          style={{ width: '100%' }}
                          value={values.member_phone_number}
                          onChange={handleChange('member_phone_number')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors &&
                              touched &&
                              touched.member_phone_number &&
                              errors.member_phone_number
                          )}
                        />
                        {referralUtil.verifyErrors(
                          errors.member_phone_number,
                          touched.member_phone_number
                        )}
                      </div>
                      <div>
                        <AppThemeTextField
                          id="member_address"
                          required={false}
                          label="Member Address"
                          placeholder="Enter Address"
                          style={{ width: '100%' }}
                          value={values.member_address}
                          onChange={handleChange('member_address')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors && touched && touched.member_address && errors.member_address
                          )}
                        />
                        {referralUtil.verifyErrors(errors.member_address, touched.member_address)}
                      </div>

                      <div>
                        <AppThemeTextField
                          id="member_city"
                          required={false}
                          label="Member City"
                          placeholder="Enter City"
                          style={{ width: '100%' }}
                          value={values.member_city}
                          onChange={handleChange('member_city')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors && touched && touched.member_city && errors.member_city
                          )}
                        />
                        {referralUtil.verifyErrors(errors.member_city, touched.member_city)}
                      </div>
                      <div>
                        <AppThemeTextField
                          id="member_state"
                          required={false}
                          label="Member State"
                          placeholder="Enter State"
                          style={{ width: '100%' }}
                          value={values.member_state}
                          onChange={handleChange('member_state')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors && touched && touched.member_state && errors.member_state
                          )}
                        />
                        {referralUtil.verifyErrors(errors.member_state, touched.member_state)}
                      </div>

                      <div>
                        <AppThemeTextField
                          required={false}
                          id="member_zip_code"
                          label="Member ZIP Code"
                          placeholder="Enter ZIP code"
                          style={{ width: '100%' }}
                          value={values.member_zip_code}
                          onChange={handleChange('member_zip_code')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors && touched && touched.member_zip_code && errors.member_zip_code
                          )}
                        />
                        {referralUtil.verifyErrors(errors.member_zip_code, touched.member_zip_code)}
                      </div>

                      <div>
                        <AppThemeTextField
                          required={false}
                          id="primary_insured_first_name"
                          label="Primary Insured First Name"
                          placeholder="Enter Primary Insured First Name "
                          style={{ width: '100%' }}
                          value={values.primary_insured_first_name}
                          onChange={handleChange('primary_insured_first_name')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors &&
                              touched &&
                              touched.primary_insured_first_name &&
                              errors.primary_insured_first_name
                          )}
                        />
                        {referralUtil.verifyErrors(
                          errors.primary_insured_first_name,
                          touched.primary_insured_first_name
                        )}
                      </div>

                      <div>
                        <AppThemeTextField
                          required={false}
                          id="primary_insured_last_name"
                          label="Primary Insured Last Name"
                          placeholder="Enter Insured Last Name"
                          style={{ width: '100%' }}
                          value={values.primary_insured_last_name}
                          onChange={handleChange('primary_insured_last_name')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors &&
                              touched &&
                              touched.primary_insured_last_name &&
                              errors.primary_insured_last_name
                          )}
                        />
                        {referralUtil.verifyErrors(
                          errors.primary_insured_last_name,
                          touched.primary_insured_last_name
                        )}
                      </div>

                      <div>
                        <AppThemeTextField
                          id="member_diagnoses"
                          required={false}
                          label="Member Diagnoses"
                          placeholder="Enter Member Diagnoses"
                          style={{ width: '100%' }}
                          value={values.member_diagnoses}
                          onChange={handleChange('member_diagnoses')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors && touched && touched.member_diagnoses && errors.member_diagnoses
                          )}
                        />
                        {referralUtil.verifyErrors(
                          errors.member_diagnoses,
                          touched.member_diagnoses
                        )}
                      </div>
                      <div>
                        <LocalizationProvider dateAdapter={AdapterDateFns}>
                          <Grid container sx={{ width: '60%' }} justify="space-around">
                            <DatePicker
                              value={values.primary_insured_dob}
                              onChange={(value) =>
                                setFieldValue('primary_insured_dob', value, true)
                              }
                              renderInput={(props) => (
                                <TextField
                                  {...props}
                                  variant="standard"
                                  id="primary_insured_dob"
                                  label="Primary Insured Date of Birth"
                                  error={Boolean(
                                    touched.primary_insured_dob && errors.primary_insured_dob
                                  )}
                                />
                              )}
                            />
                          </Grid>
                        </LocalizationProvider>
                        {referralUtil.verifyErrors(
                          errors.primary_insured_dob,
                          touched.primary_insured_dob
                        )}
                      </div>

                      <div>
                        <LocalizationProvider dateAdapter={AdapterDateFns}>
                          <Grid container sx={{ width: '60%' }} justify="space-around">
                            <DatePicker
                              value={values.eligibility_effective_date}
                              onChange={(value) =>
                                setFieldValue('eligibility_effective_date', value, true)
                              }
                              renderInput={(props) => (
                                <TextField
                                  {...props}
                                  variant="standard"
                                  id="eligibility_effective_date"
                                  label="Eligibility Effective Date"
                                  error={Boolean(
                                    touched.eligibility_effective_date &&
                                      errors.eligibility_effective_date
                                  )}
                                />
                              )}
                            />
                          </Grid>
                        </LocalizationProvider>
                        {referralUtil.verifyErrors(
                          errors.eligibility_effective_date,
                          touched.eligibility_effective_date
                        )}
                      </div>

                      <div>
                        <LocalizationProvider dateAdapter={AdapterDateFns}>
                          <Grid container sx={{ width: '60%' }} justify="space-around">
                            <DatePicker
                              value={values.eligibility_termination_date}
                              onChange={(value) =>
                                setFieldValue('eligibility_termination_date', value, true)
                              }
                              renderInput={(props) => (
                                <TextField
                                  {...props}
                                  variant="standard"
                                  id="eligibility_termination_date"
                                  label="Eligibility Termination Date"
                                  error={Boolean(
                                    touched.eligibility_termination_date &&
                                      errors.eligibility_termination_date
                                  )}
                                />
                              )}
                            />
                          </Grid>
                        </LocalizationProvider>
                        {referralUtil.verifyErrors(
                          errors.eligibility_termination_date,
                          touched.eligibility_termination_date
                        )}
                      </div>
                      <div>
                        <AppThemeTextField
                          id="relationship_to_employee"
                          required={false}
                          label="Relationship to Employee"
                          placeholder="Enter Relationship to Employee"
                          style={{ width: '100%' }}
                          value={values.relationship_to_employee}
                          onChange={handleChange('relationship_to_employee')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors &&
                              touched &&
                              touched.relationship_to_employee &&
                              errors.relationship_to_employee
                          )}
                        />
                        {referralUtil.verifyErrors(
                          errors.relationship_to_employee,
                          touched.relationship_to_employee
                        )}
                      </div>
                      <div>
                        <AppThemeTextField
                          id="recommended_program"
                          required={false}
                          label=" Recommended Program"
                          placeholder="Enter  Recommended Program"
                          style={{ width: '100%' }}
                          value={values.recommended_program}
                          onChange={handleChange('recommended_program')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors &&
                              touched &&
                              touched.recommended_program &&
                              errors.recommended_program
                          )}
                        />
                        {referralUtil.verifyErrors(
                          errors.recommended_program,
                          touched.recommended_program
                        )}
                      </div>
                      <div>
                        <AppThemeTextField
                          id="member_id"
                          required={false}
                          label=" Member ID"
                          placeholder="Enter Member ID"
                          style={{ width: '100%' }}
                          value={values.member_id}
                          onChange={handleChange('member_id')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors && touched && touched.member_id && errors.member_id
                          )}
                        />
                        {referralUtil.verifyErrors(errors.member_id, touched.member_id)}
                      </div>
                      <div>
                        <AppThemeTextField
                          required={false}
                          id="member_ssn"
                          label="Member SSN"
                          placeholder="Enter Member SSN"
                          style={{ width: '100%' }}
                          value={values.member_ssn}
                          onChange={handleChange('member_ssn')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors && touched && touched.member_ssn && errors.member_ssn
                          )}
                        />
                        {referralUtil.verifyErrors(errors.member_ssn, touched.member_ssn)}
                      </div>
                      <div>
                        <AppThemeTextField
                          required={false}
                          id="class_code"
                          label="Class Code"
                          placeholder="Enter Class Code"
                          style={{ width: '100%' }}
                          value={values.class_code}
                          onChange={handleChange('class_code')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors && touched && touched.class_code && errors.class_code
                          )}
                        />
                        {referralUtil.verifyErrors(errors.class_code, touched.class_code)}
                      </div>
                      <div>
                        <AppThemeTextField
                          required={false}
                          id="group_id"
                          label="Group Id"
                          placeholder="Enter the Group Name"
                          style={{ width: '100%' }}
                          value={values.group_id}
                          onChange={handleChange('group_id')}
                          onBlur={handleBlur}
                          error={Boolean(errors && touched && touched.group_id && errors.group_id)}
                        />
                        {referralUtil.verifyErrors(errors.group_id, touched.group_id)}
                      </div>
                      <div>
                        <AppThemeTextField
                          required={false}
                          id="group_name"
                          label="Group Name"
                          placeholder="Enter the Group Name"
                          style={{ width: '100%' }}
                          value={values.group_name}
                          onChange={handleChange('group_name')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors && touched && touched.group_name && errors.group_name
                          )}
                        />
                        {referralUtil.verifyErrors(errors.group_name, touched.group_name)}
                      </div>
                      <div>
                        <AppThemeTextField
                          required={false}
                          id="icd10_code"
                          label="ICD10 CODE"
                          placeholder="Enter the ICD10_CODE"
                          style={{ width: '100%' }}
                          value={values.icd10_code}
                          onChange={handleChange('icd10_code')}
                          onBlur={handleBlur}
                          error={Boolean(
                            errors && touched && touched.icd10_code && errors.icd10_code
                          )}
                        />
                        {referralUtil.verifyErrors(errors.icd10_code, touched.icd10_code)}
                      </div>
                      <AppthemeLoadingBtn
                        type="submit"
                        loading={loading}
                        variant="contained"
                        sx={{ my: 2 }}
                        onClick={handleSubmit}
                      >
                        Generate Unique ID
                      </AppthemeLoadingBtn>
                      <AppGoBackBtn />
                    </Box>
                  </Card>
                );
              }}
            </Formik>
          </Container>
        </>
      )}
      {!(referralPermission === 1) && <h1>You dont have access to view this page</h1>}
    </>
  );
}
