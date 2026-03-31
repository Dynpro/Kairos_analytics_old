import {
  CheckBox as CheckBoxIcon,
  CheckBoxOutlineBlankOutlined as CheckBoxOutlineBlankIcon,
} from '@mui/icons-material';
import { Autocomplete, Checkbox, TextField, Typography } from '@mui/material';
import { useFormikContext } from 'formik';
import { useReducer } from 'react';

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

export default function AppAutoCompEditPHMFolderName({ placeholder }) {
  const { setFieldValue, values } = useFormikContext();
  const defaultValue = values.phm_folders.filter((item) => item.checked);
  const [state, dispatch] = useReducer(reducer, {
    items: defaultValue,
  });

  return (
    <Autocomplete
      renderTags={(value, getTagProps) => {
        const numTags = value.length;
        return <Typography variant="body2">{`selected ${state.items.length}`}</Typography>;
      }}
      limitTags={2}
      multiple
      id="checkboxes-years"
      disableCloseOnSelect
      options={values.phm_folders}
      getOptionLabel={(option) => option.name}
      value={state.items}
      defaultValue={defaultValue}
      renderInput={(params) => (
        <TextField
          {...params}
          variant="standard"
          label={'PHM Folder Name'}
          placeholder={state.items.length === 0 ? placeholder : ''}
        />
      )}
      onChange={(_ev, vals) => {
        dispatch({
          type: 'CHANGE_ITEMS',
          newData: vals,
        });
        setFieldValue('phm_folder_id', vals);
      }}
      style={{ width: 500 }}
      renderOption={(props, option, { selected }) => {
        return (
          <li {...props}>
            <Checkbox
              icon={icon}
              checkedIcon={checkedIcon}
              style={{ marginRight: 8 }}
              checked={selected}
            />
            {option.name}
          </li>
        );
      }}
    />
  );
}
