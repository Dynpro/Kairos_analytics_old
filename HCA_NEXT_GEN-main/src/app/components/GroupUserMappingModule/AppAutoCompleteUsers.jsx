import {
  CheckBox as CheckBoxIcon,
  CheckBoxOutlineBlankOutlined as CheckBoxOutlineBlankIcon,
} from '@mui/icons-material';
import {
  Autocomplete,
  Checkbox,
  Paper,
  TextField,
  Typography,
  styled,
  useTheme,
} from '@mui/material';
import { useFormikContext } from 'formik';
import { useReducer } from 'react';

const icon = <CheckBoxOutlineBlankIcon fontSize="small" />;
const checkedIcon = <CheckBoxIcon fontSize="small" />;

const reducer = (state, action) => {
  switch (action.type) {
    case 'CHANGE_ITEMS':
      return { items: [...action.newData] };
    default:
      return state;
  }
};

const StyledAutocomplete = styled(Autocomplete)(({ theme }) => {
  return `
     & .css-19bumdr-MuiButtonBase-root-MuiCheckbox-root.Mui-checked {
      color: inherit;
    },
     & .css-19bumdr-MuiButtonBase-root-MuiCheckbox-root.MuiCheckbox-indeterminate {
      color: inherit;
    },
     & .PrivateSwitchBase-root Mui-checked css-19bumdr-MuiButtonBase-root-MuiCheckbox-root {
      color: inherit;
    },
    `;
});

const StyledCheckbox = styled(Checkbox)(({ theme }) => {
  return `
     & .css-19bumdr-MuiButtonBase-root-MuiCheckbox-root.Mui-checked {
      color: inherit;
    },
     & .css-19bumdr-MuiButtonBase-root-MuiCheckbox-root.MuiCheckbox-indeterminate {
      color: inherit;
    },
     & .PrivateSwitchBase-root Mui-checked css-19bumdr-MuiButtonBase-root-MuiCheckbox-root {
      color: inherit;
    },
    `;
});

export default function AppAutoCompleteUsers({ label, placeholder, items: recdItems }) {
  const { values, setFieldValue, errors, touched } = useFormikContext();
  const defaultValue = values.Userslist.filter((dept) => dept.checked === true);
  const theme = useTheme();
  const [state, dispatch] = useReducer(reducer, {
    items: defaultValue,
  });
  const verifyErrors = (errors, touched, fieldName) => {
    if (Boolean(touched[fieldName] && errors[fieldName]))
      return <div style={{ color: 'red' }}>* {errors[fieldName]}</div>;
    return null;
  };

  return (
    <>
      <StyledAutocomplete
        sx={{
          '&$checked': {
            color: 'teal',
          },
        }}
        renderTags={(value, getTagProps) => {
          const numTags = value.length;
          return <Typography variant="body2">{`selected ${state.items.length}`}</Typography>;
        }}
        limitTags={2}
        multiple
        id="tags-standard"
        disableCloseOnSelect
        options={values.Userslist}
        getOptionLabel={(option) => option.name + option.last_name}
        defaultValue={defaultValue}
        renderInput={(params) => (
          <TextField
            {...params}
            variant="standard"
            label={'Users'}
            placeholder={state.items.length === 0 ? placeholder : ''}
          />
        )}
        onChange={(ev, vals) => {
          dispatch({
            type: 'CHANGE_ITEMS',
            newData: vals,
          });
          setFieldValue('finalUserlist', vals);
        }}
        PaperComponent={({ children }) => (
          <Paper
            style={{
              color: theme.palette.text.primary,
              background: theme.palette.background.default,
            }}
          >
            {children}
          </Paper>
        )}
        style={{ width: 300 }}
        renderOption={(props, option, { selected }) => (
          <li {...props}>
            <StyledCheckbox
              icon={icon}
              color={'success'}
              checkedIcon={checkedIcon}
              checked={selected}
            />
            {option.name + ' ' + option.last_name}
          </li>
        )}
      />
      {verifyErrors(errors, touched, 'finalUserlist')}
    </>
  );
}
