import { Cancel } from '@mui/icons-material';
import { IconButton, InputAdornment, TextField } from '@mui/material';
import { styled } from '@mui/system';
import useSettings from 'app/hooks/useSettings';

const AppCancel = styled(Cancel)(({ theme, mode }) => ({
  color: theme.palette.text.secondary,
}));
const AppTotalCount = styled('p')(({ theme, mode }) => ({
  color: theme.palette.text.secondary,
}));

const SidebarSearch = ({ searchValue, totalCount }) => {
  const { settings } = useSettings();
  const { mode } = settings.layout1Settings.leftSidebar;

  return (
    <TextField
      autoComplete="off"
      fullWidth
      size="small"
      sx={{
        borderBottom: '2px solid rgb(135, 206, 235)',
        width: '85%',
        marginLeft: '8%',
      }}
      variant="standard"
      style={{ overflow: 'hidden' }}
      mode={mode}
      value={searchValue?.current}
      id="search_field"
      name="search_field"
      placeholder={'Search Dashboards...'}
      onChange={(event) => {
        searchValue.current = event.currentTarget.value;
      }}
      InputProps={{
        endAdornment: (
          <InputAdornment position="end" sx={{ marginRight: '5px' }}>
            <AppTotalCount> {totalCount || 0}</AppTotalCount>
            {searchValue.current.length > 0 && (
              <IconButton
                edge="end"
                onClick={() => {
                  searchValue.current = '';
                }}
              >
                <AppCancel />
              </IconButton>
            )}
          </InputAdornment>
        ),
      }}
    />
  );
};

export default SidebarSearch;
