import { styled } from '@mui/material';
import { LoadingButton } from '@mui/lab';

const AppthemeLoadingBtn = styled(LoadingButton)(({ theme }) => ({
  margin: theme.spacing(1),
  backgroundColor: '#0288d1',
}));

export default AppthemeLoadingBtn;
