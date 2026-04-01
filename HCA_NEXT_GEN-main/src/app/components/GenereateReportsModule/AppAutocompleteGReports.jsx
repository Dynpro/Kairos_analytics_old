import React, { useEffect, useReducer } from 'react';
import { Autocomplete, Checkbox, TextField, Typography } from '@mui/material';
import {
  CheckBox as CheckBoxIcon,
  CheckBoxOutlineBlankOutlined as CheckBoxOutlineBlankIcon,
} from '@mui/icons-material';
import { useFormikContext } from 'formik';
import commonConfig from '../commonConfig';
import axios from 'axios';
import { getAccessToken } from 'app/utils/utils';
import SnackbarUtils from 'SnackbarUtils';

const icon = <CheckBoxOutlineBlankIcon fontSize="small" />;
const checkedIcon = <CheckBoxIcon fontSize="small" />;

const reducer = (state, action) => {
  switch (action.type) {
    case 'CHANGE_ITEMS':
      return { ...state, items: [...action.newData] };
    case 'CHANGE_YEARS':
      return { ...state, years: [...action.newYears] };
    default:
      return state;
  }
};

export default function AppAutocompleteGReports({ label, placeholder, items: recdItems }) {
  const { values, setFieldValue } = useFormikContext();
  const [state, dispatch] = useReducer(reducer, {
    items: [],
    years: [],
  });

  const fetchPosts = async () => {
    if (values.schema_name) {
      const authToken = getAccessToken();
      try {
        const response = await axios(
          `${commonConfig.urls.phmAutomationYearList}?schema_name=${values.schema_name}`,
          {
            headers: {
              Authorization: `Bearer ${authToken}`,
              'Content-Type': 'application/json',
            },
          }
        );
        if (response && response['data'] && response['data']['Response']) {
          dispatch({ type: 'CHANGE_YEARS', newYears: response['data']['Response'] });
        }
      } catch (error) {
        SnackbarUtils.error(error?.message || 'Something went wrong!!');
      }
    }
  };

  useEffect(() => {
    fetchPosts();
  }, [values.schema_name]);

  return (
    <Autocomplete
      renderTags={(value, getTagProps) => {
        const numTags = value.length;
        return <Typography variant="body2">{`selected ${state.items.length}`}</Typography>;
      }}
      limitTags={2}
      multiple
      id="checkboxes-years"
      options={state.years.slice(0, 3)}
      disableCloseOnSelect
      getOptionLabel={(option) => option.toString()}
      renderOption={(props, option, { selected }) => (
        <li {...props}>
          <Checkbox
            icon={icon}
            checkedIcon={checkedIcon}
            style={{ marginRight: 8 }}
            checked={selected}
          />
          {option}
        </li>
      )}
      onChange={(ev, vals) => {
        dispatch({
          type: 'CHANGE_ITEMS',
          newData: vals,
        });
        setFieldValue('years', vals);
      }}
      style={{ width: 500 }}
      renderInput={(params) => (
        <TextField
          {...params}
          variant="standard"
          label={'Years'}
          placeholder={state.items.length === 0 ? placeholder : ''}
        />
      )}
    />
  );
}
