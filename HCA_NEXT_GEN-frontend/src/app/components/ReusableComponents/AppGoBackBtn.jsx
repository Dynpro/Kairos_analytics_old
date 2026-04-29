import styled from '@emotion/styled';
import { Button } from '@mui/material';
import { useNavigate } from 'react-router-dom';

const StyledButton = styled(Button)(({ theme }) => ({
  margin: theme.spacing(1),
  backgroundColor: theme.palette.info.main,
}));

const AppGoBackBtn = () => {
  let navigate = useNavigate();
  return (
    <StyledButton variant="contained" onClick={() => navigate(-1)}>
      Back
    </StyledButton>
  );
};

export default AppGoBackBtn;
