import { Box, Card, MenuItem, Grid, styled } from '@mui/material';
import axios from 'axios';
import { Formik } from 'formik';
import { useState } from 'react';
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
import AppAutoCompPHMFolderName from '../AppAutoCompPHMFolderName';
import clientModuleUtils from '../util';

const Container = styled('div')(({ theme }) => ({
  margin: '30px',
  [theme.breakpoints.down('sm')]: { margin: '16px' },
}));

export default function CreateClient() {
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const { data: lookerFolderList } = useApiOnce(commonConfig.urls.getlookerfolderStructure);
  const { data: phmFolderList } = useApiOnce(commonConfig.urls.getparentphm);
  const { data: schemaList } = useApiOnce(commonConfig.urls.getschema);
  const { data: lookerGroups } = useApiOnce(commonConfig.urls.getlookerGroups);
  // const { data: clientCategories } = useApiOnce(commonConfig.urls.getClientCategory);
  const { data: clientSubCategories } = useApiOnce(commonConfig.urls.getSubCategory);

  const verifyErrors = (errors, touched, fieldName) => {
    if (Boolean(touched[fieldName] && errors[fieldName]))
      return <div style={{ color: 'red' }}>* {errors[fieldName]}</div>;
    return null;
  };

  async function sendDataToServer(recdData = {}) {
    const data = new FormData();

    Object.keys(recdData).forEach((item) => {
      if (Array.isArray(recdData[item])) {
        for (const itemArr in recdData[item])
          data.append(`${item}[${itemArr}]`, recdData[item][itemArr]);
      } else data.append(item, recdData[item]);
    });
    const authToken = getAccessToken();
    try {
      setLoading(true);
      const response = await axios.post(commonConfig.urls.client, data, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      setLoading(false);
      if (response && response.data.Status === 'Failed') {
        SnackbarUtils.error(Object.values(response.data.Errors).map((item) => item.toString()));
      }
      if (response && response.data.Status === 'Success') {
        SnackbarUtils.success(response.data.Message);
        navigate(commonRoutes.clients.clientList);
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
            {
              name: 'Client List',
              path: commonRoutes.clients.clientList,
            },
            { name: 'Create Client' },
          ]}
        />
      </Box>
      <Container sx={{ display: 'flex', justifyContent: 'center' }}>
        <Formik
          initialValues={clientModuleUtils.initialValues}
          validationSchema={clientModuleUtils.validationSchema}
          onSubmit={(values) => {
            sendDataToServer({
              ...values,
              phm_folder_id: values.phm_folder_id.map((item) => item.id).filter((item) => item),
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
                      id="folder_name"
                      required
                      label="Client Name"
                      placeholder="Enter Client Name"
                      style={{ width: '100%' }}
                      value={values.folder_name}
                      onChange={handleChange('folder_name')}
                      onBlur={handleBlur}
                      error={Boolean(errors.folder_name && touched.folder_name)}
                      variant="outlined"
                      InputLabelProps={{ shrink: true }}
                    />
                    {verifyErrors(errors, touched, 'folder_name')}
                  </div>
                  <Grid container spacing={2}>
                    {/*                     <Grid item xs={12} md={6}>
                      <AppThemeTextField
                        defaultValue={''}
                        id="category"
                        name="category"
                        value={values.category || ''}
                        select
                        label="Category"
                        placeholder="Select Category"
                        error={Boolean(errors.category && touched.category)}
                        onChange={handleChange('category')}
                        onBlur={handleBlur}
                        variant="standard"
                      >
                        {clientCategories.map((option, index) => (
                          <MenuItem key={index} value={option?.category_id}>
                            {option?.category}
                          </MenuItem>
                        ))}
                      </AppThemeTextField>
                      {verifyErrors(errors, touched, 'category')}
                    </Grid>
 */}
                    <Grid item xs={12} md={6}>
                      <AppThemeTextField
                        defaultValue={''}
                        id="subcategory"
                        name="subcategory"
                        value={values.subcategory || ''}
                        select
                        label="Sub Category"
                        placeholder="Select Sub-Category"
                        error={Boolean(errors.subcategory && touched.subcategory)}
                        onChange={handleChange('subcategory')}
                        onBlur={handleBlur}
                        variant="standard"
                      >
                        {clientSubCategories.map((option, index) => (
                          <MenuItem key={index} value={option?.subcategory_id}>
                            {option?.subcategory}
                          </MenuItem>
                        ))}
                      </AppThemeTextField>
                    </Grid>
                    <Grid item xs={12} md={6}>
                      <AppThemeTextField
                        defaultValue={''}
                        id="folder_id"
                        name="folder_id"
                        value={values.folder_id || ''}
                        select
                        label="Folder Name"
                        placeholder="Select Folder Name"
                        error={Boolean(errors.folder_id && touched.folder_id)}
                        onChange={handleChange('folder_id')}
                        onBlur={handleBlur}
                        variant="standard"
                      >
                        {lookerFolderList.map((option, index) => (
                          <MenuItem key={index} value={option.id}>
                            {option.name}
                          </MenuItem>
                        ))}
                      </AppThemeTextField>
                      {verifyErrors(errors, touched, 'folder_id')}
                    </Grid>
                    <Grid item xs={12} md={6}>
                      <AppAutoCompPHMFolderName items={phmFolderList} />
                    </Grid>
                    <Grid item xs={12} md={6}>
                      <AppThemeTextField
                        defaultValue={''}
                        id="schema_name"
                        name="schema_name"
                        value={values.schema_name || ''}
                        select
                        label="Schema Name"
                        placeholder="Select Schema"
                        error={Boolean(errors.schema_name && touched.schema_name)}
                        onChange={handleChange('schema_name')}
                        onBlur={handleBlur}
                        variant="standard"
                      >
                        {schemaList.map((option, index) => (
                          <MenuItem key={index} value={option.schema_name}>
                            {option.schema_name}
                          </MenuItem>
                        ))}
                      </AppThemeTextField>
                      {verifyErrors(errors, touched, 'schema_name')}
                    </Grid>
                    <Grid item xs={12} md={6}>
                      <AppThemeTextField
                        defaultValue={''}
                        id="group_id"
                        name="group_id"
                        value={values.group_id || ''}
                        select
                        label="Group Id"
                        placeholder="Select Group Id"
                        onChange={handleChange('group_id')}
                        onBlur={handleBlur}
                        error={Boolean(errors.group_id && touched.group_id)}
                        variant="standard"
                      >
                        {lookerGroups.map((option, index) => (
                          <MenuItem key={index} value={option.id}>
                            {option.name}
                          </MenuItem>
                        ))}
                      </AppThemeTextField>
                      {verifyErrors(errors, touched, 'group_id')}
                    </Grid>

                    {/* end */}
                  </Grid>

                  <div>
                    <AppThemeTextField
                      id="contact_email"
                      label="Email"
                      placeholder="Enter Email Id"
                      style={{ width: '100%' }}
                      value={values.contact_email}
                      onChange={handleChange('contact_email')}
                      onBlur={handleBlur}
                      variant="outlined"
                      InputLabelProps={{ shrink: true }}
                      error={Boolean(errors.contact_email && touched.contact_email)}
                    />
                    {verifyErrors(errors, touched, 'contact_email')}
                  </div>
                  <div>
                    <AppThemeTextField
                      id="external_group_id"
                      label="External Group Id"
                      placeholder="Enter External Group Id"
                      style={{ width: '100%' }}
                      value={values.external_group_id}
                      onChange={handleChange('external_group_id')}
                      onBlur={handleBlur}
                      variant="outlined"
                      InputLabelProps={{ shrink: true }}
                    />
                  </div>
                  <div>
                    <AppThemeTextField
                      required
                      id="models"
                      label="Models"
                      placeholder="Enter Model Name"
                      style={{ width: '100%' }}
                      value={values.models}
                      onChange={handleChange('models')}
                      onBlur={handleBlur}
                      error={Boolean(errors.models && touched.models)}
                      variant="outlined"
                      InputLabelProps={{ shrink: true }}
                    />
                    {verifyErrors(errors, touched, 'models')}
                  </div>
                  <div>
                    <AppThemeTextField
                      id="access_filters"
                      label="Access Filters"
                      placeholder="Enter Access Filters"
                      style={{ width: '100%' }}
                      value={values.access_filters}
                      onChange={handleChange('access_filters')}
                      onBlur={handleBlur}
                      variant="outlined"
                      InputLabelProps={{ shrink: true }}
                    />
                  </div>
                  <div>
                    <AppThemeTextField
                      id="file"
                      label="Client Logo(Image size should be 225px * 50px)"
                      name="file"
                      type="file"
                      style={{ width: '100%' }}
                      onChange={(event) => {
                        setFieldValue('file', event.currentTarget.files[0]);
                      }}
                      variant="outlined"
                      InputLabelProps={{ shrink: true }}
                    />
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
