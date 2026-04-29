import 'date-fns';
import axios from 'axios';
import { Formik } from 'formik';
import { useEffect, useState } from 'react';
import * as Yup from 'yup';
import { format } from 'date-fns';

import { DatePicker } from '@mui/lab';
import AdapterDateFns from '@mui/lab/AdapterDateFns';
import LocalizationProvider from '@mui/lab/LocalizationProvider';
import { Box, Card, CircularProgress, Grid, MenuItem, styled, TextField } from '@mui/material';
import { useNavigate, useLocation } from 'react-router-dom';

import commonConfig from 'app/components/commonConfig';
import commonRoutes from 'app/components/commonRoutes';

import SnackbarUtils from 'SnackbarUtils';
import { Breadcrumb } from 'app/components';
import AppGoBackBtn from 'app/components/ReusableComponents/AppGoBackBtn';
import AppthemeLoadingBtn from 'app/components/ReusableComponents/AppThemeLoadingBtn';
import AppThemeTextField from 'app/components/ReusableComponents/AppThemeTextField';
import { getAccessToken } from 'app/utils/utils';

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
}));

const FrequencyList = [
  { val: 1, type: 'Once' },
  { val: 2, type: 'Weekly' },
  { val: 3, type: 'Monthly' },
  { val: 4, type: 'Quarterly' },
];

const reportYearList = ['Service', 'Paid'];

const validationSchema = Yup.object().shape({
  report_name: Yup.string().required('Kindly enter a Report Name'),
  frequency: Yup.number().required('Kindly select frequency').nullable(),
  reporting_year: Yup.string().required('Kindly select a Reporting Year'),
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

export default function EditGenerateReports() {
  const navigate = useNavigate();
  const location = useLocation();
  const authToken = getAccessToken();

  // Report ID passed via route state or query param
  const reportId = location.state?.report_id || new URLSearchParams(location.search).get('id');

  const [loading, setLoading] = useState(false);
  const [fetching, setFetching] = useState(true);
  const [initialValues, setInitialValues] = useState(null);

  // ------------------------------------------------------------------
  // Load existing report data
  // ------------------------------------------------------------------
  useEffect(() => {
    const load = async () => {
      if (!reportId) {
        SnackbarUtils.error('No report ID provided');
        navigate(commonRoutes.generate_reports.generate_reportsTabList);
        return;
      }
      try {
        setFetching(true);
        const resp = await axios.get(
          `${commonConfig.urls.phmAutomationReportList}/${reportId}`,
          { headers: { Authorization: `Bearer ${authToken}` } }
        );
        const r = resp?.data?.Response;
        if (r) {
          setInitialValues({
            report_name: r.report_name || r.name || '',
            reporting_year: r.reporting_year || '',
            frequency: r.frequency ?? null,
            medical_start_date: r.medical_start_date ? new Date(r.medical_start_date) : null,
            medical_end_date: r.medical_end_date ? new Date(r.medical_end_date) : null,
            pharmacy_start_date: r.pharmacy_start_date ? new Date(r.pharmacy_start_date) : null,
            pharmacy_end_date: r.pharmacy_end_date ? new Date(r.pharmacy_end_date) : null,
            folder_name: r.folder_name || '',
            schema_name: r.schema_name || '',
          });
        } else {
          SnackbarUtils.error('Report not found');
          navigate(commonRoutes.generate_reports.generate_reportsTabList);
        }
      } catch (e) {
        SnackbarUtils.error(e?.message || 'Failed to load report');
        navigate(commonRoutes.generate_reports.generate_reportsTabList);
      } finally {
        setFetching(false);
      }
    };
    load();
  }, [reportId]);

  // ------------------------------------------------------------------
  // Submit updated values
  // ------------------------------------------------------------------
  const handleUpdate = async (values) => {
    try {
      setLoading(true);
      const resp = await axios.put(
        `${commonConfig.urls.phmAutomationReportList}/${reportId}`,
        {
          report_name: values.report_name,
          reporting_year: values.reporting_year,
          frequency: values.frequency,
          medical_start_date: format(new Date(values.medical_start_date), 'yyyy-MM-dd'),
          medical_end_date: format(new Date(values.medical_end_date), 'yyyy-MM-dd'),
          pharmacy_start_date: format(new Date(values.pharmacy_start_date), 'yyyy-MM-dd'),
          pharmacy_end_date: format(new Date(values.pharmacy_end_date), 'yyyy-MM-dd'),
        },
        { headers: { Authorization: `Bearer ${authToken}` } }
      );
      setLoading(false);
      if (resp?.data?.Status === 'Success') {
        SnackbarUtils.success(resp.data.Message || 'Report updated');
        navigate(commonRoutes.generate_reports.generate_reportsTabList);
      } else {
        SnackbarUtils.error(
          resp?.data?.Errors
            ? Object.values(resp.data.Errors).join(', ')
            : resp?.data?.Message || 'Update failed'
        );
      }
    } catch (e) {
      setLoading(false);
      SnackbarUtils.error(e?.message || 'Something went wrong');
    }
  };

  if (fetching) {
    return (
      <Box sx={{ display: 'flex', justifyContent: 'center', mt: 8 }}>
        <CircularProgress />
      </Box>
    );
  }

  if (!initialValues) return null;

  const verifyErrors = (errors, touched, field) =>
    Boolean(touched[field] && errors[field]) ? (
      <div style={{ color: 'red' }}>* {errors[field]}</div>
    ) : null;

  return (
    <>
      <Box className="breadcrumb" sx={{ m: 1 }}>
        <Breadcrumb
          routeSegments={[
            { name: 'Reports List', path: commonRoutes.generate_reports.generate_reportsTabList },
            { name: 'Edit PHM Report' },
          ]}
        />
      </Box>

      <Container sx={{ display: 'flex', justifyContent: 'center' }}>
        <Formik
          enableReinitialize
          initialValues={initialValues}
          validationSchema={validationSchema}
          onSubmit={handleUpdate}
        >
          {({ errors, touched, values, handleBlur, handleChange, handleSubmit, setFieldValue }) => {
            const datePickerError = (field) =>
              touched[field] && errors[field] ? (
                <div style={{ color: 'red', fontSize: '0.75rem', marginTop: '4px' }}>
                  * {errors[field]}
                </div>
              ) : null;

            return (
              <Card sx={{ px: 3, pt: 1, pb: 2, width: '100%', maxWidth: '700px' }}>
                <Box
                  component="form"
                  sx={{ '& .MuiTextField-root': { my: 1, width: '25ch' }, width: '100%' }}
                  noValidate
                  autoComplete="off"
                >
                  {/* Read-only info */}
                  <Box sx={{ mb: 2, p: 1.5, bgcolor: 'grey.100', borderRadius: 1 }}>
                    <Box sx={{ fontSize: '0.8rem', color: 'text.secondary' }}>
                      Client: <strong>{values.folder_name}</strong>
                      &nbsp;&nbsp;|&nbsp;&nbsp;Schema: <strong>{values.schema_name}</strong>
                    </Box>
                  </Box>

                  <div>
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

                  <div>
                    <AppThemeTextField
                      defaultValue=""
                      id="reporting_year"
                      name="reporting_year"
                      value={values.reporting_year || ''}
                      style={{ width: '100%' }}
                      select
                      label="Reporting Year"
                      onChange={handleChange('reporting_year')}
                      onBlur={handleBlur}
                      error={Boolean(errors.reporting_year && touched.reporting_year)}
                    >
                      {reportYearList.map((opt, i) => (
                        <MenuItem key={i} value={opt}>{opt}</MenuItem>
                      ))}
                    </AppThemeTextField>
                    {verifyErrors(errors, touched, 'reporting_year')}
                  </div>

                  <div>
                    <AppThemeTextField
                      defaultValue=""
                      id="frequency"
                      name="frequency"
                      value={values.frequency || ''}
                      style={{ width: '100%' }}
                      select
                      label="Frequency"
                      onChange={handleChange('frequency')}
                      onBlur={handleBlur}
                      error={Boolean(errors.frequency && touched.frequency)}
                    >
                      {FrequencyList.map((opt, i) => (
                        <MenuItem key={i} value={opt.val}>{opt.type}</MenuItem>
                      ))}
                    </AppThemeTextField>
                    {verifyErrors(errors, touched, 'frequency')}
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
                              <TextField {...params} fullWidth variant="standard"
                                error={Boolean(touched.medical_start_date && errors.medical_start_date)} />
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
                              <TextField {...params} fullWidth variant="standard"
                                error={Boolean(touched.medical_end_date && errors.medical_end_date)} />
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
                              <TextField {...params} fullWidth variant="standard"
                                error={Boolean(touched.pharmacy_start_date && errors.pharmacy_start_date)} />
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
                              <TextField {...params} fullWidth variant="standard"
                                error={Boolean(touched.pharmacy_end_date && errors.pharmacy_end_date)} />
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
                    Update Report
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
