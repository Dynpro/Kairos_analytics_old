import axios from 'axios';
import { Formik } from 'formik';
import React, { useState } from 'react';
import * as Yup from 'yup';

import { Box, Card, MenuItem, styled } from '@mui/material';
import { useNavigate } from 'react-router-dom';

import commonConfig from 'app/components/commonConfig';
import commonRoutes from 'app/components/commonRoutes';

import SnackbarUtils from 'SnackbarUtils';
import { Breadcrumb } from 'app/components';
import AppGoBackBtn from 'app/components/ReusableComponents/AppGoBackBtn';
import AppthemeLoadingBtn from 'app/components/ReusableComponents/AppThemeLoadingBtn';
import AppThemeTextField from 'app/components/ReusableComponents/AppThemeTextField';
import useApiOnce from 'app/hooks/useApiOnce';
import { getAccessToken } from 'app/utils/utils';
import AppAutocompleteSReports from '../AppAutocompleteSReports';

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
}));

const initialValues = {
  client_id: '',
  client_name: '',
  folder_name: '',
  schema_name: '',
  frequency: null,
  patients: [],
};

const validationSchema = Yup.object().shape({
  folder_name: Yup.string().required('Kindly select a Client').label('Client Name'),
  frequency: Yup.number().required('Kindly select frequency').label('Frequency').nullable(),
  patients: Yup.array().min(1, 'Kindly select atleast one Patient'),
});

const reducer = (state, action) => {
  switch (action.type) {
    case 'CHANGE_ITEMS':
      return { items: [action.newData] };
    default:
      return state;
  }
};

export default function CreateSUMGenerateReports() {
  const [loading, setLoading] = useState(false);
  const [open, setOpen] = React.useState(false);
  const navigate = useNavigate();

  const { data: psClientList } = useApiOnce(commonConfig.urls.psAutomationClientList);
  const FrequencyList = [
    { val: 1, type: 'Once' },
    /*    { val: 2, type: 'Weekly' }, */
    { val: 3, type: 'Monthly' },
    { val: 4, type: 'Quaterly' },
  ];

  const verifyErrors = (errors, touched, fieldName) => {
    if (Boolean(touched[fieldName] && errors[fieldName]))
      return <div style={{ color: 'red' }}>* {errors[fieldName]}</div>;
    return null;
  };

  async function sendDataToServer(data) {
    const authToken = getAccessToken();
    try {
      setLoading(true);
      const response = await axios.post(commonConfig.urls.psAutomationStoreReportRequest, data, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      setLoading(false);
      if (response && response.data.Status === 'Failed') {
        SnackbarUtils.error(Object.values(response.data.Errors).map((item) => item.toString()));
      }
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);
        setOpen(true);
        navigate(commonRoutes.generate_reports.generate_reportsTabList);
      }
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  }

  return (
    <>
      <Box className="breadcrumb" sx={{ m: 1 }}>
        <Breadcrumb
          routeSegments={[
            { name: 'Reports List', path: commonRoutes.generate_reports.generate_reportsTabList },
            { name: 'Generate Patient Summary Reports' },
          ]}
        />
      </Box>

      <Container sx={{ display: 'flex', justifyContent: 'center' }}>
        <Formik
          enableReinitialize={true}
          initialValues={initialValues}
          validationSchema={validationSchema}
          onSubmit={(values) => {
            const splitFolderName = values.folder_name;
            const [client_name, client_id, schema_name] = splitFolderName.split('/');
            sendDataToServer({
              client_id,
              client_name,
              schema_name: schema_name,
              frequency: values.frequency,
              patientlist: values.patients.map((item) => item.name).filter((item) => item),
            });
          }}
        >
          {({ errors, touched, values, handleBlur, handleChange, handleSubmit, setFieldValue }) => {
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
                      id="folder_name"
                      name="folder_name"
                      value={values.folder_name || ''}
                      style={{ width: '100%' }}
                      select
                      label="Clients"
                      placeholder="Select Clients"
                      error={Boolean(errors.folder_name && touched.folder_name)}
                      onChange={(e) => {
                        setFieldValue('schema_name', e.target.value.split('/')[2]);
                        setFieldValue('folder_name', e.target.value);
                      }}
                      onBlur={handleBlur}
                    >
                      {psClientList.map((option, index) => (
                        <MenuItem
                          key={index}
                          value={
                            option.folder_name + '/' + option.folder_id + '/' + option.schema_name
                          }
                        >
                          {option.folder_name}
                        </MenuItem>
                      ))}
                    </AppThemeTextField>
                    {verifyErrors(errors, touched, 'folder_name')}
                  </div>
                  <div>
                    <AppThemeTextField
                      defaultValue={''}
                      id="frequency"
                      name="frequency"
                      value={values.frequency || ''}
                      style={{ width: '100%' }}
                      select
                      label="Frequency"
                      placeholder="Select Frequency"
                      onChange={handleChange('frequency')}
                      onBlur={handleBlur}
                      error={Boolean(errors.frequency && touched.frequency)}
                    >
                      {FrequencyList.map((option, index) => (
                        <MenuItem key={index} value={option.val}>
                          {option.type}
                        </MenuItem>
                      ))}
                    </AppThemeTextField>
                    {verifyErrors(errors, touched, 'frequency')}
                  </div>
                  <AppAutocompleteSReports />
                  {verifyErrors(errors, touched, 'patients')}
                  <AppthemeLoadingBtn
                    type="submit"
                    loading={loading}
                    variant="contained"
                    sx={{ my: 2 }}
                    onClick={handleSubmit}
                  >
                    Submit
                  </AppthemeLoadingBtn>
                  <AppGoBackBtn />
                </Box>
              </Card>
            );
          }}
        </Formik>
      </Container>
    </>
  );
}
