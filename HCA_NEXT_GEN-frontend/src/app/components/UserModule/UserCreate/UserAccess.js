import { Divider, FormControlLabel, Grid } from '@mui/material';
import AppThemeCheckbox from 'app/components/ReusableComponents/AppThemeCheckbox';
import { useFormikContext } from 'formik';

const styles = `
@media (min-width: 960px) {
  .form-group-divider {
    display: none;
  }
}
`;

export default function UserAccess() {
  const { handleChange, values, setFieldValue } = useFormikContext();

  const renderLabel = (labelName) => {
    if (typeof labelName !== 'string') return '';
    const actions = ['add', 'edit', 'delete', 'view'];
    const matchedAction = actions.find((action) => labelName.includes(action));
    return matchedAction ? matchedAction.charAt(0).toUpperCase() + matchedAction.slice(1) : '';
  };

  const handleToggleAll = (moduleName, fields) => {
    const allChecked = fields.every((field) => values.userAccess[field]);
    fields.forEach((field) => setFieldValue(`userAccess.${field}`, !allChecked));
    setFieldValue(`userAccess.${moduleName}`, !allChecked);
  };

  const renderFormGroup = (label, moduleName, fields) => (
    <>
      <Grid container direction="row" alignItems="center" spacing={2}>
        <Grid item xs={12} sm={6} md={4} lg={3}>
          <FormControlLabel
            control={
              <AppThemeCheckbox
                onChange={() => handleToggleAll(moduleName, fields)}
                checked={values.userAccess[moduleName]}
              />
            }
            label={label}
            sx={{ mr: 8 }}
          />
        </Grid>
        <Grid item xs={12} sm={6} md={8} lg={9}>
          {fields.map((field) => (
            <FormControlLabel
              key={field}
              control={
                <AppThemeCheckbox
                  onChange={handleChange(`userAccess.${field}`)}
                  checked={values.userAccess[field]}
                />
              }
              label={renderLabel(field)}
            />
          ))}
        </Grid>
      </Grid>
      <Divider className="form-group-divider" />
    </>
  );

  return (
    <div style={{ width: '100%' }}>
      <style>{styles}</style>
      <p>User Access</p>
      {/* Users */}
      {renderFormGroup('Users', 'users', ['user_add', 'user_edit', 'user_delete', 'user_view'])}

      {/* Approve Button */}
      <Grid item xs={12} sm={6} md={4} lg={3}>
        <FormControlLabel
          control={
            <AppThemeCheckbox
              onChange={() =>
                setFieldValue(
                  'userAccess.approval_pending_user',
                  !values.userAccess.approval_pending_user
                )
              }
              checked={values.userAccess.approval_pending_user}
            />
          }
          label="Approve User"
        />
      </Grid>
      <Divider className="form-group-divider" />

      {/* Group */}
      {renderFormGroup('Group', 'group_module', [
        'group_add',
        'group_edit',
        'group_delete',
        'group_view',
      ])}

      {/* Roles */}
      {renderFormGroup('Roles', 'roles', ['role_add', 'role_edit', 'role_delete', 'role_view'])}

      {/* Clients */}
      {renderFormGroup('Clients', 'clients', [
        'client_add',
        'client_edit',
        'client_delete',
        'client_view',
      ])}

      {/* Reports */}
      {renderFormGroup('Reports', 'reports', [
        'report_add',
        'report_edit',
        'report_delete',
        'report_view',
      ])}

      {/* Generate Reports */}
      {renderFormGroup('Generate Reports', 'generate_report', [
        'generate_report_add',
        'generate_report_edit',
        'generate_report_delete',
        'generate_report_view',
      ])}

      {/* Invite User */}
      <Grid item xs={12} sm={6} md={4} lg={3}>
        <FormControlLabel
          control={
            <AppThemeCheckbox
              onChange={() =>
                setFieldValue('userAccess.invite_user', !values.userAccess.invite_user)
              }
              checked={values.userAccess.invite_user}
            />
          }
          label="Invite User"
        />
      </Grid>
      <Divider className="form-group-divider" />

      {/* Permission Button */}
      <Grid item xs={12} sm={6} md={4} lg={3}>
        <FormControlLabel
          control={
            <AppThemeCheckbox
              onChange={() =>
                setFieldValue('userAccess.permission_btn', !values.userAccess.permission_btn)
              }
              checked={values.userAccess.permission_btn}
            />
          }
          label="Switch Button Access"
        />
      </Grid>
      <Divider className="form-group-divider" />

      {/* Looker */}
      <Grid item xs={12} sm={6} md={4} lg={3}>
        <FormControlLabel
          control={
            <AppThemeCheckbox
              onChange={() => setFieldValue('userAccess.looker', !values.userAccess.looker)}
              checked={values.userAccess.looker}
            />
          }
          label="Looker"
        />
      </Grid>
      <Divider className="form-group-divider" />

      {/* Snowflake */}
      <Grid item xs={12} sm={6} md={4} lg={3}>
        <FormControlLabel
          control={
            <AppThemeCheckbox
              onChange={() => setFieldValue('userAccess.snowflake', !values.userAccess.snowflake)}
              checked={values.userAccess.snowflake}
            />
          }
          label="Snowflake"
        />
      </Grid>

      {/* Referral */}
      <Grid item xs={12} sm={6} md={4} lg={3}>
        <FormControlLabel
          control={
            <AppThemeCheckbox
              onChange={() => setFieldValue('userAccess.referral', !values.userAccess.referral)}
              checked={values.userAccess.referral}
            />
          }
          label="Referral"
        />
      </Grid>
    </div>
  );
}
