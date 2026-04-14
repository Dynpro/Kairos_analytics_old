import { Checkbox, styled } from '@mui/material';

const AppThemeCheckbox = styled(Checkbox)(({ theme }) => {
  return `
     & .MuiSvgIcon-root {
      color: ${theme.palette.text.primary};
    }
    `;
});

export default AppThemeCheckbox;
