import { Autocomplete, styled } from '@mui/material';

const AppThemeAutocomplete = styled(Autocomplete)(({ theme }) => {
  return `
      & .css-1mzj5yt-MuiFormLabel-root-MuiInputLabel-root.Mui-focused 
      {
        color: ${theme.palette.text.primary};
      }
      & .MuiSvgIcon-root {
        color: ${theme.palette.text.primary};
      }
      `;
});

export default AppThemeAutocomplete;
