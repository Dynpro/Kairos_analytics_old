import { useTheme } from '@emotion/react';
import { Box, Icon, Modal, Typography } from '@mui/material';
import { useDispatch, useSelector } from 'react-redux';
import KairosLogo from '../icons/kairos.png';
import KairosLogoWhite from '../icons/kairos.png';

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

export default function AppAboutModule() {
  const dispatch = useDispatch();
  const theme = useTheme();
  const currentTheme = useSelector((state) => state.currentTheme?.theme);

  const open = useSelector((state) => state.modal.open);
  const handleClose = () => dispatch({ type: 'SET_OPEN_MODAL', open: false });
  return (
    <Modal open={open} onClose={handleClose}>
      <Box sx={{ ...style, color: theme.palette.text.primary }}>
        <div style={{ position: 'absolute', right: 20, top: 20 }}>
          <Icon onClick={handleClose} style={{ cursor: 'pointer' }}>
            close
          </Icon>
        </div>
        <Typography
          id="modal-header"
          variant="h6"
          component="h2"
          style={{ display: 'flex', justifyContent: 'center' }}
        >
          <Box
            component={() => (
              <img
                alt="Brand Logo"
                src={currentTheme?.activeTheme === 'slateDark1' ? KairosLogoWhite : KairosLogo}
                style={{ width: '100%', objectFit: 'contain' }}
              />
            )}
          />
        </Typography>
        <hr />
        <Typography id="modal-body" sx={{ mt: 2, textAlign: 'center' }}>
          Version: Kairos-NextGen-UI-v1.0.0 (12 Dec 2024)
        </Typography>
        <hr />
        <Typography
          id="modal-footer"
          sx={{ mt: 2, textAlign: 'center', color: theme.palette.primary }}
        >
          Copyright 2023 | All Rights Reserved
        </Typography>
      </Box>
    </Modal>
  );
}
