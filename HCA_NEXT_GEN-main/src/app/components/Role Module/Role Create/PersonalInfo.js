import Box from '@mui/material/Box';
import AppThemeTextField from 'app/components/ReusableComponents/AppThemeTextField';
import { useFormikContext } from 'formik';

export default function PersonalInfo() {
  const { values, handleChange, errors, touched, handleBlur } = useFormikContext();

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
      <p style={{ color: 'inherit' }}>Create Role</p>
      <div>
        <AppThemeTextField
          label="Role"
          placeholder="Enter role"
          style={{ width: '70%' }}
          required
          value={values.role}
          onChange={handleChange('role')}
          onBlur={handleBlur}
          error={Boolean(errors && touched.role && touched.role && errors.role)}
        />
      </div>
    </Box>
  );
}
