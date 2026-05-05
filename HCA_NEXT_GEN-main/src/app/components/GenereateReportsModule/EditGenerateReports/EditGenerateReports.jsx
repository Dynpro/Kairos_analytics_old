import {
  Accordion,
  AccordionDetails,
  AccordionSummary,
  Box,
  Button,
  Card,
  Grid,
  Icon,
  IconButton,
  MenuItem,
  styled,
  TextField,
  Typography
} from '@mui/material';
import axios from 'axios';
import { FieldArray, Formik } from 'formik';
import { useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import * as Yup from 'yup';

import SnackbarUtils from 'SnackbarUtils';
import { Breadcrumb } from 'app/components';
import commonConfig from 'app/components/commonConfig';
import commonRoutes from 'app/components/commonRoutes';
import AppGoBackBtn from 'app/components/ReusableComponents/AppGoBackBtn';
import AppthemeLoadingBtn from 'app/components/ReusableComponents/AppThemeLoadingBtn';
import AppThemeTextField from 'app/components/ReusableComponents/AppThemeTextField';
import useApiOnce from 'app/hooks/useApiOnce';
import { getAccessToken } from 'app/utils/utils';

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
}));

const StyledCard = styled(Card)(({ theme }) => ({
  padding: '24px',
  width: '100%',
}));

const SectionHeader = styled(Box)(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  width: '100%',
}));

const validationSchema = Yup.object().shape({
  report_name: Yup.string().required('Report Name is required'),
  client_data: Yup.string().required('Client is required'),
});

export default function EditGenerateReports() {
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const location = useLocation();
  const reportData = location.state?.reportData;
  const authToken = getAccessToken();

  const { data: phmClientList } = useApiOnce(commonConfig.urls.phmAutomationClientList);

  if (!reportData) {
    return (
      <Container>
        <Typography variant="h6">No report data found to edit.</Typography>
        <AppGoBackBtn />
      </Container>
    );
  }

  // Parse sections if it's a string
  let initialSections = [{ title: 'Section 1', content: '' }];
  try {
    if (reportData.sections) {
      initialSections = typeof reportData.sections === 'string' 
        ? JSON.parse(reportData.sections) 
        : reportData.sections;
    }
  } catch (e) {
    console.error("Error parsing sections", e);
  }

  const initialValues = {
    report_name: reportData.report_name || reportData.name || '',
    client_data: `${reportData.folder_name}/${reportData.phm_folder_id}/${reportData.schema_name}`,
    report_type: 'PHM',
    medical_start_date: reportData.medical_start_date ? reportData.medical_start_date.substring(0, 10) : '',
    medical_end_date: reportData.medical_end_date ? reportData.medical_end_date.substring(0, 10) : '',
    pharmacy_start_date: reportData.pharmacy_start_date ? reportData.pharmacy_start_date.substring(0, 10) : '',
    pharmacy_end_date: reportData.pharmacy_end_date ? reportData.pharmacy_end_date.substring(0, 10) : '',
    frequency: reportData.frequency_val || reportData.frequency || 1,
    reporting_year: reportData.reporting_year || 'Service',
    sections: initialSections,
  };

  async function handleSubmit(values) {
    const [client_name, client_id, schema_name] = values.client_data.split('/');
    
    const payload = {
      report_id: reportData.report_id,
      report_name: values.report_name,
      client_id,
      client_name,
      schema_name,
      medical_start_date: values.medical_start_date,
      medical_end_date: values.medical_end_date,
      pharmacy_start_date: values.pharmacy_start_date,
      pharmacy_end_date: values.pharmacy_end_date,
      frequency: values.frequency,
      reporting_year: values.reporting_year,
      sections: values.sections,
      years: [new Date(values.medical_start_date || new Date()).getFullYear()],
    };

    try {
      setLoading(true);
      // Using post for update as typical in this project's PHM module
      const response = await axios.post(commonConfig.urls.phmAutomationStoreReportRequest, payload, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      setLoading(false);
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success("Report updated successfully");
        navigate(commonRoutes.generate_reports.generate_reportsTabList);
      } else {
        SnackbarUtils.error(response.data.Message || 'Failed to update report');
      }
    } catch (error) {
      setLoading(false);
      SnackbarUtils.error(error?.message || 'Something went wrong!!');
    }
  }

  return (
    <Container>
      <Box className="breadcrumb" sx={{ mb: 3 }}>
        <Breadcrumb
          routeSegments={[
            { name: 'Reports List', path: commonRoutes.generate_reports.generate_reportsTabList },
            { name: 'Edit PHM Report' },
          ]}
        />
      </Box>

      <Formik
        initialValues={initialValues}
        validationSchema={validationSchema}
        onSubmit={handleSubmit}
      >
        {({ values, errors, touched, handleChange, handleBlur, setFieldValue, handleSubmit }) => (
          <form onSubmit={handleSubmit}>
            <StyledCard>
              <Grid container spacing={3}>
                <Grid item xs={12} md={4}>
                  <AppThemeTextField
                    fullWidth
                    name="report_name"
                    label="Report Name"
                    value={values.report_name}
                    onChange={handleChange}
                    onBlur={handleBlur}
                    error={Boolean(touched.report_name && errors.report_name)}
                  />
                </Grid>
                <Grid item xs={12} md={4}>
                  <AppThemeTextField
                    fullWidth
                    select
                    name="client_data"
                    label="Client Name"
                    value={values.client_data}
                    onChange={handleChange}
                  >
                    {phmClientList.map((option, index) => (
                      <MenuItem
                        key={index}
                        value={`${option.folder_name}/${option.phm_folder_id}/${option.schema_name}`}
                      >
                        {option.folder_name}
                      </MenuItem>
                    ))}
                  </AppThemeTextField>
                </Grid>
                <Grid item xs={12} md={4}>
                  <AppThemeTextField
                    fullWidth
                    select
                    name="report_type"
                    label="Report Type"
                    value={values.report_type}
                    onChange={handleChange}
                  >
                    <MenuItem value="PHM">PHM</MenuItem>
                    <MenuItem value="Patient Summary">Patient Summary</MenuItem>
                  </AppThemeTextField>
                </Grid>

                <Grid item xs={12} md={3}>
                  <TextField
                    fullWidth
                    type="date"
                    name="medical_start_date"
                    label="Med Start Date"
                    InputLabelProps={{ shrink: true }}
                    value={values.medical_start_date}
                    onChange={handleChange}
                  />
                </Grid>
                <Grid item xs={12} md={3}>
                  <TextField
                    fullWidth
                    type="date"
                    name="medical_end_date"
                    label="Med End Date"
                    InputLabelProps={{ shrink: true }}
                    value={values.medical_end_date}
                    onChange={handleChange}
                  />
                </Grid>
                <Grid item xs={12} md={3}>
                  <TextField
                    fullWidth
                    type="date"
                    name="pharmacy_start_date"
                    label="Pharma Start Date"
                    InputLabelProps={{ shrink: true }}
                    value={values.pharmacy_start_date}
                    onChange={handleChange}
                  />
                </Grid>
                <Grid item xs={12} md={3}>
                  <TextField
                    fullWidth
                    type="date"
                    name="pharmacy_end_date"
                    label="Pharma End Date"
                    InputLabelProps={{ shrink: true }}
                    value={values.pharmacy_end_date}
                    onChange={handleChange}
                  />
                </Grid>

                <Grid item xs={12}>
                  <FieldArray name="sections">
                    {({ push, remove }) => (
                      <Box>
                        {values.sections.map((section, index) => (
                          <Accordion key={index} sx={{ mb: 2, border: '1px solid #ddd' }}>
                            <AccordionSummary expandIcon={<Icon>expand_more</Icon>}>
                              <SectionHeader>
                                <Typography sx={{ color: '#1976d2', fontWeight: 600 }}>
                                  {section.title || `Section ${index + 1}`}
                                </Typography>
                                <IconButton size="small" onClick={() => remove(index)}>
                                  <Icon color="error">delete</Icon>
                                </IconButton>
                              </SectionHeader>
                            </AccordionSummary>
                            <AccordionDetails>
                              <AppThemeTextField
                                fullWidth
                                label="Section Title"
                                name={`sections.${index}.title`}
                                value={section.title}
                                onChange={handleChange}
                              />
                            </AccordionDetails>
                          </Accordion>
                        ))}
                        <Button
                          variant="contained"
                          startIcon={<Icon>add_circle</Icon>}
                          onClick={() => push({ title: `Section ${values.sections.length + 1}`, content: '' })}
                        >
                          Add Section
                        </Button>
                      </Box>
                    )}
                  </FieldArray>
                </Grid>

                <Grid item xs={12} sx={{ display: 'flex', justifyContent: 'flex-end', gap: 2, mt: 3 }}>
                  <AppGoBackBtn />
                  <AppthemeLoadingBtn
                    type="submit"
                    loading={loading}
                    variant="contained"
                    sx={{ backgroundColor: '#2e7d32' }}
                  >
                    Update Report
                  </AppthemeLoadingBtn>
                </Grid>
              </Grid>
            </StyledCard>
          </form>
        )}
      </Formik>
    </Container>
  );
}
