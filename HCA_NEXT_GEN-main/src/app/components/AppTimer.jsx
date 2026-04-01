import React from 'react';
import { useTheme } from '@emotion/react';
import { Box, Button, Typography, Modal, styled } from '@mui/material';
import { H1 } from 'app/components/Typography';
import { LoadingButton } from '@mui/lab';
const style = {
  position: 'absolute',
  top: '35%',
  left: '50%',
  transform: 'translate(-50%, -50%)',
  width: 500,
  bgcolor: 'background.paper',
  boxShadow: 24,
  p: 4,
};

const StyledButton = styled(Button)(({ theme }) => ({
  margin: theme.spacing(1),
}));

export default function AppTimer({ open, handleExtend, handleLogout }) {
  const theme = useTheme();
  return (
    <Modal open={open}>
      <Box sx={{ ...style, color: theme.palette.text.primary }}>
        <Typography id="modal-header" sx={{ textAlign: 'center' }}>
          <H1>Session Expire Warning</H1>
        </Typography>
        <hr />
        <Typography id="modal-body1" sx={{ mt: 2, textAlign: 'center' }}>
          Your session will expire in <strong>05:00 Minutes</strong>
        </Typography>
        <Typography id="modal-body1" sx={{ mt: 2, textAlign: 'center' }}>
          Do you want to extend/logoff session?
        </Typography>
        <hr />
        <div style={{ display: 'flex', justifyContent: 'center' }}>
          <StyledButton onClick={handleExtend} variant="contained" color="primary">
            Extend
          </StyledButton>
          <LoadingButton
            type="submit"
            color="error"
            variant="contained"
            sx={{ my: 2 }}
            onClick={handleLogout}
          >
            Logout
          </LoadingButton>
        </div>
      </Box>
    </Modal>
  );
}
