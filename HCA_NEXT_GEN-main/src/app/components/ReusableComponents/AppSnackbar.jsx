import React from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Snackbar, Alert } from '@mui/material';
import { setSnackbar } from '../../redux/ducks/snackbar';

const AppSnackbar = () => {
  const dispatch = useDispatch();
  const snackbarOpen = useSelector((state) => state.snackbar.snackbarOpen);
  const snackbarType = useSelector((state) => state.snackbar.snackbarType);
  const snackbarMessage = useSelector((state) => state.snackbar.snackbarMessage);
  const handleClose = (event, reason) => {
    if (reason === 'clickaway') {
      return;
    }
    dispatch(setSnackbar(false, snackbarType, snackbarMessage));
  };

  return (
    <div>
      <Snackbar
        open={snackbarOpen}
        autoHideDuration={4000}
        onClose={handleClose}
        anchorOrigin={{ vertical: 'top', horizontal: 'right' }}
        sx={{ height: '15%' }}
      >
        <Alert elevation={6} variant="filled" onClose={handleClose} color={snackbarType}>
          {snackbarMessage}
        </Alert>
      </Snackbar>
    </div>
  );
};

export default AppSnackbar;
