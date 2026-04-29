import { useFormikContext } from 'formik';

import { Box, Card, Grid, MenuItem, TextField } from '@mui/material';

import commonConfig from 'app/components/commonConfig';

import AppGoBackBtn from 'app/components/ReusableComponents/AppGoBackBtn';
import AppthemeLoadingBtn from 'app/components/ReusableComponents/AppThemeLoadingBtn';
import AppThemeTextField from 'app/components/ReusableComponents/AppThemeTextField';
import useApiOnce from 'app/hooks/useApiOnce';
import AppAutoCompEditPHMFolderName from '../AppAutoCompEditPHMFolderName';
import clientModuleUtils from '../util';

export default function EditClientContents({ id, loading }) {
  const { errors, touched, values, handleBlur, handleChange, handleSubmit, setFieldValue } =
    useFormikContext();

  const { data: lookerFolderList } = useApiOnce(commonConfig.urls.getlookerfolderStructure);
  const { data: schemaList } = useApiOnce(commonConfig.urls.getschema);
  const { data: lookerGroups } = useApiOnce(commonConfig.urls.getlookerGroups);
  //const { data: clientCategories } = useApiOnce(commonConfig.urls.getClientCategory);
  const { data: clientSubCategories } = useApiOnce(commonConfig.urls.getSubCategory);

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
            name="folder_name"
            required
            label="Client Name"
            placeholder="Enter Client Name"
            style={{ width: '100%' }}
            value={values.folder_name}
            onChange={handleChange}
            onBlur={handleBlur}
            error={Boolean(errors.folder_name && touched.folder_name)}
          />
          {clientModuleUtils.verifyErrors(errors, touched, 'folder_name')}
        </div>
        <Grid container spacing={2}>
          {/*           <Grid item xs={12} md={6}>
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
            {clientModuleUtils.verifyErrors(errors, touched, 'category')}
          </Grid> */}
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
              {values.subcategory ? (
                <MenuItem color="disabled" sx={{ opacity: 0.4 }} value={''}>
                  Unselect
                </MenuItem>
              ) : null}
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
              error={Boolean(errors.folder_id)}
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
            {clientModuleUtils.verifyErrors(errors, touched, 'folder_id')}
          </Grid>
          <Grid item xs={12} md={6}>
            {values.phm_folders_default.length > 0 && (
              <div>
                <AppAutoCompEditPHMFolderName id={id} />
              </div>
            )}
            {values.phm_folders_default.length === 0 && (
              <div>
                <AppAutoCompEditPHMFolderName id={id} />
              </div>
            )}
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
              error={Boolean(errors.schema_name)}
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
            {clientModuleUtils.verifyErrors(errors, touched, 'schema_name')}
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
              error={Boolean(errors.group_id)}
              variant="standard"
            >
              {lookerGroups.map((option, index) => (
                <MenuItem key={index} value={option.id}>
                  {option.name}
                </MenuItem>
              ))}
            </AppThemeTextField>
            {clientModuleUtils.verifyErrors(errors, touched, 'group_id')}
          </Grid>

          {/* //end */}
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
            error={Boolean(errors.contact_email && touched.contact_email)}
          />
          {clientModuleUtils.verifyErrors(errors, touched, 'contact_email')}
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
            error={Boolean(errors.external_group_id && touched.external_group_id)}
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
          />
          {clientModuleUtils.verifyErrors(errors, touched, 'models')}
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
            error={Boolean(errors.access_filters && touched.access_filters)}
          />
        </div>
        <div style={{ display: 'flex' }}>
          <TextField
            id="file"
            label="Client Logo(Image size should be 225px * 50px)"
            name="file"
            type="file"
            style={{ width: '100%', flex: 1 }}
            onChange={(event) => {
              setFieldValue('file', event.currentTarget.files[0]);
              setFieldValue('logo', '');
            }}
            variant="outlined"
            InputLabelProps={{ shrink: true }}
          />
          {values.logo && (
            <div style={{ flex: 1 }}>
              <img
                src={values.logo}
                style={{
                  height: 'auto',
                  width: 'auto',
                }}
                alt="Client Image"
              />
            </div>
          )}
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
}
