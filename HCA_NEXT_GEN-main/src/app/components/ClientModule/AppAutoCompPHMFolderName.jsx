import {
  CheckBox as CheckBoxIcon,
  CheckBoxOutlineBlankOutlined as CheckBoxOutlineBlankIcon,
} from '@mui/icons-material';
import { TextField, Typography } from '@mui/material';
import { useFormikContext } from 'formik';
import { useReducer } from 'react';
import AppThemeAutocomplete from '../ReusableComponents/AppThemeAutoComplete';
import AppThemeCheckbox from '../ReusableComponents/AppThemeCheckbox';

const icon = <CheckBoxOutlineBlankIcon fontSize="small" />;
const checkedIcon = <CheckBoxIcon fontSize="small" />;

const reducer = (state, action) => {
  switch (action.type) {
    case 'CHANGE_ITEMS':
      return { ...state, items: [...action.newData] };
    default:
      return state;
  }
};

export default function AppAutoCompPHMFolderName({ label, placeholder, items: recdItems }) {
  const { setFieldValue } = useFormikContext();
  const [state, dispatch] = useReducer(reducer, {
    items: [],
  });

  return (
    <AppThemeAutocomplete
      renderTags={(value, getTagProps) => (
        <Typography variant="body2">{`selected ${state.items.length}`}</Typography>
      )}
      limitTags={2}
      multiple
      id="checkboxes-years"
      options={recdItems}
      disableCloseOnSelect
      getOptionLabel={(option) => option.name}
      renderOption={(props, option, { selected }) => (
        <li {...props} key={option.tbl_id}>
          <AppThemeCheckbox
            icon={icon}
            checkedIcon={checkedIcon}
            style={{ marginRight: 8 }}
            checked={selected}
          />
          {option.name}
        </li>
      )}
      onChange={(ev, vals) => {
        dispatch({
          type: 'CHANGE_ITEMS',
          newData: vals,
        });
        setFieldValue('phm_folder_id', vals);
      }}
      style={{ width: 500 }}
      renderInput={(params) => (
        <TextField
          {...params}
          variant="standard"
          label={'PHM Folder Name'}
          placeholder={state.items.length === 0 ? placeholder : ''}
        />
      )}
    />
  );
}
