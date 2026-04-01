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
    case 'CHANGE_PATIENTS':
      return { ...state, patients: [...action.newPatients] };
    default:
      return state;
  }
};

export default function AppAutocompleteSReports({ label, placeholder, items: recdItems }) {
  const { values, setFieldValue } = useFormikContext();
  const [state, dispatch] = useReducer(reducer, {
    items: [],
    patients: [],
  });

  function extractName(responseObj) {
    const condn = responseObj[0] && responseObj[0].DEMOGRAPHIC_VALUES?.patient_details;
    if (condn)
      return responseObj[0].DEMOGRAPHIC_VALUES?.patient_details.map((item, id) => ({
        name: item.name,
        id,
      }));
    return [];
  }

  const fetchPosts = async () => {
    if (values.schema_name) {
      const authToken = getAccessToken();
      try {
        const response = await axios(
          `${commonConfig.urls.psAutomationCreatePatientList}?schema_name=${values.schema_name}`,
          {
            headers: {
              Authorization: `Bearer ${authToken}`,
              'Content-Type': 'application/json',
            },
          }
        );
        if (response && response['data'] && response['data']['Response']) {
          dispatch({
            type: 'CHANGE_PATIENTS',
            newPatients: extractName(response['data']['Response']),
          });
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
        return <Typography variant="body2">{`selected ${state.items.length}`}</Typography>;
      }}
      limitTags={2}
      multiple
      id="checkboxes-patients"
      options={state.patients}
      disableCloseOnSelect
      getOptionLabel={(option) => option.name.toString()}
      renderOption={(props, option, { selected }) => (
        <li {...props} key={option.id}>
          <Checkbox
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
        setFieldValue('patients', vals);
      }}
      style={{ width: 500 }}
      renderInput={(params) => (
        <TextField
          {...params}
          variant="standard"
          label={'Patients'}
          placeholder={state.items.length === 0 ? placeholder : ''}
        />
      )}
    />
  );
}
