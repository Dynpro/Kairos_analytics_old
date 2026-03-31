import { TextField, styled } from '@mui/material';

const AppThemeTextField = styled(TextField)(({ theme }) => {
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
      '.MuiOutlinedInput-notchedOutline': {
        borderColor: 'rgba(228, 219, 233, 0.25)',
      },
      '&.Mui-focused .MuiOutlinedInput-notchedOutline': {
        borderColor: 'rgba(228, 219, 233, 0.25)',
      },
      '&:hover .MuiOutlinedInput-notchedOutline': {
        borderColor: 'rgba(228, 219, 233, 0.25)',
      },
      &.css-i4bv87-MuiSvgIcon-root {
        fill: ${theme.palette.text.primary};
      }
      &.css-qzbt6i-MuiButtonBase-root-MuiIconButton-root-MuiAutocomplete-popupIndicator
      {
        color: ${theme.palette.text.primary};
      }
      &.css-pqjvzy-MuiSvgIcon-root-MuiSelect-icon
      {
        color: ${theme.palette.text.primary};
      }
      &.css-1mzj5yt-MuiFormLabel-root-MuiInputLabel-root.Mui-focused 
      {
        color: ${theme.palette.text.primary};
      }
      & .css-1j8fbm1-MuiInputBase-root-MuiInput-root
      {
        border-bottom: 2px solid ${theme.palette.text.primary};
      }
      & .css-1rezcpo-MuiInputBase-root-MuiInput-root
      {
        border-bottom: 2px solid ${theme.palette.text.primary};
      }
      & .css-1mf6u8l-MuiSvgIcon-root-MuiSelect-icon
      {
        color: ${theme.palette.text.primary};
      }
      `;
});

export default AppThemeTextField;
