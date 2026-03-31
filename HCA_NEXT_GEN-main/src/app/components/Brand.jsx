import { Box, styled } from '@mui/material';
import useSettings from 'app/hooks/useSettings';
import KairosLogo from './icons/kairos.png';
import KairosLogoWhite from './icons/kairos.png';
import { Link } from 'react-router-dom';
import commonRoutes from './commonRoutes';
import { useDispatch, useSelector } from 'react-redux';
import useRefreshData from 'app/hooks/useRefreshData';

const BrandRoot = styled(Box)(({ theme }) => {
  return {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
  };
});
const AppHr = styled('hr')(({ theme }) => {
  return {
    width: '100%',
    padding: 0,
    margin: 0,
    backgroundColor: theme.palette.text.primary,
  };
});

const Brand = ({ children }) => {
  const { settings } = useSettings();
  const leftSidebar = settings.layout1Settings.leftSidebar;
  const { mode } = leftSidebar;
  const currentTheme = useSelector((state) => state.currentTheme?.theme);
  const { handleRefreshData } = useRefreshData();
  const dispatch = useDispatch();
  return (
    <Link
      to={commonRoutes.home}
      onClick={() => {
        handleRefreshData();
        dispatch({
          type: 'SET_CLIENT',
          client: {
            subCategory_name: '',
            client_name: '',
            folder_name: '',
            dashboard_name: '',
            client_id: '',
          },
        });
      }}
    >
      <BrandRoot style={{ backgroundColor: currentTheme === 'Default Theme' ? '#fff' : 'inherit' }}>
        <div
          style={{
            marginTop: '5px',
            height: '59px',
            width: '100%',
            display: 'flex',
            justifyContent: 'center',
          }}
        >
          <Box
            component={() => (
              <img
                alt="Brand Logo"
                src={currentTheme?.activeTheme === 'slateDark1' ? KairosLogoWhite : KairosLogo}
                style={{
                  objectFit: 'contain',
                  width: '90%',
                }}
              />
            )}
          />
        </div>
        <Box className="sidenavHoverShow" sx={{ display: mode === 'compact' ? 'none' : 'block' }}>
          {children || null}
        </Box>
      </BrandRoot>
      {!(currentTheme === 'Default Theme') && <AppHr />}
    </Link>
  );
};

export default Brand;
