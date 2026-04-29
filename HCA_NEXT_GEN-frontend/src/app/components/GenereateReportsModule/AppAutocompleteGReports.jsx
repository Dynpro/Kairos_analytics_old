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

export default function AppAutocompleteGReports({ placeholder, items: recdItems }) {
  const { values, setFieldValue } = useFormikContext();
  const [state, dispatch] = useReducer(reducer, {
    items: [],
    years: [],
  });

  const fallbackYears = () => {
    const currentYear = new Date().getFullYear();
    return Array.from({ length: 8 }, (_, i) => currentYear - i);
  };

  const normalizeYears = (rawYears = []) => {
    const list = Array.isArray(rawYears) ? rawYears : [];

    return [...new Set(
      list
        .map((item) => (item && typeof item === 'object' ? item.name ?? item.year ?? item.value : item))
        .map((item) => Number(item))
        .filter((item) => Number.isFinite(item) && item > 1900)
    )].sort((a, b) => b - a);
  };

  const fetchYears = async () => {
    const initialYears = normalizeYears(recdItems);

    if (!values.schema_name) {
      dispatch({
        type: 'CHANGE_YEARS',
        newYears: initialYears.length > 0 ? initialYears : fallbackYears(),
      });
      return;
    }

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

      const years = normalizeYears(response?.data?.Response);
      dispatch({
        type: 'CHANGE_YEARS',
        newYears: years.length > 0 ? years : initialYears.length > 0 ? initialYears : fallbackYears(),
      });
    } catch (error) {
      dispatch({
        type: 'CHANGE_YEARS',
        newYears: initialYears.length > 0 ? initialYears : fallbackYears(),
      });
    }
  };

  useEffect(() => {
    fetchYears();
  }, [values.schema_name, recdItems]);

  return (
    <Autocomplete
      value={values.years || []}
      renderTags={(value) => <Typography variant="body2">{`selected ${value.length}`}</Typography>}
      limitTags={2}
      multiple
      id="checkboxes-years"
      options={state.years}
      disableCloseOnSelect
      getOptionLabel={(option) => option.toString()}
      isOptionEqualToValue={(option, value) => option === value}
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
          label="Years"
          placeholder={state.items.length === 0 ? placeholder : ''}
        />
      )}
    />
  );
}
