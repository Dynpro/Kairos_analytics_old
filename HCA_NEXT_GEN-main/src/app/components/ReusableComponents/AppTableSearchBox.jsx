import React from 'react';
import { styled, TextField } from '@mui/material';

const AppTextField = styled(TextField)(({ theme }) => {
  return `
    & label.Mui-focused {
      color: ${theme.palette.text.primary};
    }
    & .MuiOutlinedInput-root {
      &.Mui-focused fieldset {
        border-color: ${theme.palette.text.primary};
      }
      & fieldset {
        border-color: ${theme.palette.text.primary};
      }
    }
    }
    `;
});

export default function AppTableSearchBox({ onSearch }) {
  return (
    <AppTextField
      autoComplete="off"
      sx={{ cursor: 'pointer' }}
      id="outlined-search"
      label="Search ..."
      type="search"
      size="small"
      onChange={onSearch}
    />
  );
}
