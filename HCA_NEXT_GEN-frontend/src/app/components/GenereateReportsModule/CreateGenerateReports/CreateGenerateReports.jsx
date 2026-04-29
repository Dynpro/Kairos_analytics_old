import 'date-fns';
import axios from 'axios';
import { Formik } from 'formik';
import { useState } from 'react';
import * as Yup from 'yup';
import { format } from 'date-fns';

import { DatePicker } from '@mui/lab';
import AdapterDateFns from '@mui/lab/AdapterDateFns';
import LocalizationProvider from '@mui/lab/LocalizationProvider';
import { Box, Card, Grid, MenuItem, styled, TextField } from '@mui/material';
import { useNavigate } from 'react-router-dom';

import commonConfig from 'app/components/commonConfig';
import commonRoutes from 'app/components/commonRoutes';
import AppAutocompleteGReports from '../AppAutocompleteGReports';

import SnackbarUtils from 'SnackbarUtils';
import { Breadcrumb } from 'app/components';
import AppGoBackBtn from 'app/components/ReusableComponents/AppGoBackBtn';
import AppthemeLoadingBtn from 'app/components/ReusableComponents/AppThemeLoadingBtn';
import AppThemeTextField from 'app/components/ReusableComponents/AppThemeTextField';
import useApiOnce from 'app/hooks/useApiOnce';
import { getAccessToken } from 'app/utils/utils';

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
  reporting_year: '',
  years: [],
  report_name: '',
  medical_start_date: null,
  medical_end_date: null,
  pharmacy_start_date: null,
  pharmacy_end_date: null,
};

const validationSchema = Yup.object().shape({
  folder_name: Yup.string().required('Kindly select a Client').label('Client Name'),
  frequency: Yup.number().required('Kindly select frequency').label('Frequency').nullable(),
  years: Yup.array().min(1, 'Kindly select atleast one Year'),
  reporting_year: Yup.string().required('Kindly select a Reporting Year').label('Reporting Year'),
  report_name: Yup.string().required('Kindly enter a Report Name').label('Report Name'),
  medical_start_date: Yup.date().required('Kindly select Medical Start Date').nullable().typeError('Invalid date'),
  medical_end_date: Yup.date()
    .required('Kindly select Medical End Date')
    .nullable()
    .typeError('Invalid date')
    .min(Yup.ref('medical_start_date'), 'End date must be after start date'),
  pharmacy_start_date: Yup.date().required('Kindly select Pharmacy Start Date').nullable().typeError('Invalid date'),
  pharmacy_end_date: Yup.date()
    .required('Kindly select Pharmacy End Date')
    .nullable()
    .typeError('Invalid date')
    .min(Yup.ref('pharmacy_start_date'), 'End date must be after start date'),
});

export default function CreateGenerateReports() {
  const [loading, setLoading] = useState(false);
  const [open, setOpen] = useState(false);
  const navigate = useNavigate();

  const { data: phmClientList } = useApiOnce(commonConfig.urls.phmAutomationClientList);
  const { data: phmYearList } = useApiOnce(
    `${commonConfig.urls.phmAutomationYearList}?schema_name=SCH_ALL_HEALTH_CHOICE`
  );
  const reportYearList = ['Service', 'Paid'];
  const FrequencyList = [
    { val: 1, type: 'Once' },
    { val: 2, type: 'Weekly' },
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
      const response = await axios.post(commonConfig.urls.phmAutomationStoreReportRequest, data, {
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
            { name: 'Generate PHM Reports' },
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
              schema_name,
              frequency: values.frequency,
              reporting_year: values.reporting_year,
              years: values.years,
              report_name: values.report_name,
              medical_start_date: format(new Date(values.medical_start_date), 'yyyy-MM-dd'),
              medical_end_date: format(new Date(values.medical_end_date), 'yyyy-MM-dd'),
              pharmacy_start_date: format(new Date(values.pharmacy_start_date), 'yyyy-MM-dd'),
              pharmacy_end_date: format(new Date(values.pharmacy_end_date), 'yyyy-MM-dd'),
            });
          }}
        >
          {({
            errors,
            touched,
            values,
            handleBlur,
            handleChange,
            handleSubmit,
            setFieldTouched,
            setFieldValue,
          }) => {
            const datePickerError = (field) =>
              touched[field] && errors[field] ? (
                <div style={{ color: 'red', fontSize: '0.75rem', marginTop: '4px' }}>* {errors[field]}</div>
              ) : null;

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
                        setFieldValue('years', []); // reset year selection when client changes
                      }}
                      onBlur={handleBlur}
                    >
                      {phmClientList.map((option, index) => (
                        <MenuItem
                          key={index}
                          value={
                            option.folder_name +
                            '/' +
                            option.phm_folder_id +
                            '/' +
                            option.schema_name
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
                      id="reporting_year"
                      name="reporting_year"
                      value={values.reporting_year || ''}
                      style={{ width: '100%' }}
                      select
                      label="Reporting Year"
                      placeholder="Select Reporting Year"
                      onChange={handleChange('reporting_year')}
                      onBlur={handleBlur}
                      error={Boolean(errors.reporting_year && touched.reporting_year)}
                    >
                      {reportYearList.map((option, index) => (
                        <MenuItem key={index} value={option}>
                          {option}
                        </MenuItem>
                      ))}
                    </AppThemeTextField>
                    {verifyErrors(errors, touched, 'reporting_year')}
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
                  <AppAutocompleteGReports key={values.schema_name || 'default'} items={phmYearList} />
                  {verifyErrors(errors, touched, 'years')}

                  <div style={{ marginTop: '8px' }}>
                    <AppThemeTextField
                      id="report_name"
                      required
                      label="Report Name"
                      placeholder="Enter Report Name"
                      style={{ width: '100%' }}
                      value={values.report_name}
                      onChange={handleChange('report_name')}
                      onBlur={handleBlur}
                      error={Boolean(errors.report_name && touched.report_name)}
                      variant="outlined"
                      InputLabelProps={{ shrink: true }}
                    />
                    {verifyErrors(errors, touched, 'report_name')}
                  </div>

                  <div style={{ marginTop: '12px' }}>
                    <div style={{ fontSize: '0.85rem', fontWeight: 500, color: '#555', marginBottom: '8px' }}>
                      Medical Data Date Range
                    </div>
                    <LocalizationProvider dateAdapter={AdapterDateFns}>
                      <Grid container spacing={2}>
                        <Grid item xs={12} sm={6}>
                          <DatePicker
                            label="Medical Start Date"
                            value={values.medical_start_date}
                            onChange={(val) => setFieldValue('medical_start_date', val)}
                            renderInput={(params) => (
                              <TextField
                                {...params}
                                fullWidth
                                variant="standard"
                                error={Boolean(touched.medical_start_date && errors.medical_start_date)}
                              />
                            )}
                          />
                          {datePickerError('medical_start_date')}
                        </Grid>
                        <Grid item xs={12} sm={6}>
                          <DatePicker
                            label="Medical End Date"
                            value={values.medical_end_date}
                            minDate={values.medical_start_date}
                            onChange={(val) => setFieldValue('medical_end_date', val)}
                            renderInput={(params) => (
                              <TextField
                                {...params}
                                fullWidth
                                variant="standard"
                                error={Boolean(touched.medical_end_date && errors.medical_end_date)}
                              />
                            )}
                          />
                          {datePickerError('medical_end_date')}
                        </Grid>
                      </Grid>
                    </LocalizationProvider>
                  </div>

                  <div style={{ marginTop: '16px' }}>
                    <div style={{ fontSize: '0.85rem', fontWeight: 500, color: '#555', marginBottom: '8px' }}>
                      Pharmacy Data Date Range
                    </div>
                    <LocalizationProvider dateAdapter={AdapterDateFns}>
                      <Grid container spacing={2}>
                        <Grid item xs={12} sm={6}>
                          <DatePicker
                            label="Pharmacy Start Date"
                            value={values.pharmacy_start_date}
                            onChange={(val) => setFieldValue('pharmacy_start_date', val)}
                            renderInput={(params) => (
                              <TextField
                                {...params}
                                fullWidth
                                variant="standard"
                                error={Boolean(touched.pharmacy_start_date && errors.pharmacy_start_date)}
                              />
                            )}
                          />
                          {datePickerError('pharmacy_start_date')}
                        </Grid>
                        <Grid item xs={12} sm={6}>
                          <DatePicker
                            label="Pharmacy End Date"
                            value={values.pharmacy_end_date}
                            minDate={values.pharmacy_start_date}
                            onChange={(val) => setFieldValue('pharmacy_end_date', val)}
                            renderInput={(params) => (
                              <TextField
                                {...params}
                                fullWidth
                                variant="standard"
                                error={Boolean(touched.pharmacy_end_date && errors.pharmacy_end_date)}
                              />
                            )}
                          />
                          {datePickerError('pharmacy_end_date')}
                        </Grid>
                      </Grid>
                    </LocalizationProvider>
                  </div>

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
