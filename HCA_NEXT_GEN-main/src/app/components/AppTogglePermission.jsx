import { Button, FormControlLabel, Switch, Typography } from '@mui/material';
import { useDispatch, useSelector } from 'react-redux';

export default function AppTogglePermission() {
  const currentUserPermission = useSelector((state) => state.userType.userIsA);
  const dispatch = useDispatch();

  const handlePermissionChange = (event, newPermission) => {
    if (newPermission === null) return dispatch({ type: 'SET_USER_TYPE', userIsA: 'viewer' });
    else if (newPermission === true)
      return dispatch({ type: 'SET_USER_TYPE', userIsA: 'explorer' });
    else if (newPermission === false) return dispatch({ type: 'SET_USER_TYPE', userIsA: 'viewer' });
  };
  return (
    <div>
      <FormControlLabel
        control={
          <Switch
            value={currentUserPermission === 'explorer' ? true : false}
            checked={currentUserPermission === 'explorer' ? true : false}
            onChange={handlePermissionChange}
            color="primary"
          />
        }
        label={
          <Button variant="contained" sx={{ backgroundColor: '#0070c0' }}>
            <Typography style={{ textTransform: 'uppercase' }} variant="body1" color="white">
              {currentUserPermission}
            </Typography>
          </Button>
        }
      />
    </div>
  );
}
