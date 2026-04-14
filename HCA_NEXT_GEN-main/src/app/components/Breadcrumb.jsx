import { Breadcrumbs, Hidden, Icon, styled, useTheme } from '@mui/material';
import { NavLink } from 'react-router-dom';
import commonRoutes from './commonRoutes';

const BreadcrumbRoot = styled('div')(() => ({
  display: 'flex',
  flexWrap: 'wrap',
  alignItems: 'center',
  backgroundColor: 'none',
}));

const BreadcrumbName = styled('h4')(({ theme }) => ({
  margin: 0,
  fontSize: '16px',
  paddingBottom: '1px',
  verticalAlign: 'middle',
  textTransform: 'capitalize',
  color: theme.palette.text.secondary,
}));

const SubName = styled('span')(({ theme }) => ({
  textTransform: 'capitalize',
  color: theme.palette.text.secondary,
}));

const Separator = styled('h4')(({ theme }) => ({
  margin: 0,
  marginLeft: 8,
  paddingBottom: '3px',
  color: theme.palette.text.hint,
}));

const StyledIcon = styled(Icon)(({ theme }) => ({
  marginLeft: 8,
  marginBottom: '4px',
  verticalAlign: 'middle',
  color: theme.palette.text.primary,
}));

const Breadcrumb = ({ routeSegments }) => {
  const theme = useTheme();
  const hint = theme.palette.text.hint;

  return (
    <BreadcrumbRoot>
      {routeSegments ? (
        <Hidden xsDown>
          <BreadcrumbName>{routeSegments[routeSegments.length - 1]['name']}</BreadcrumbName>
          <Separator>|</Separator>
        </Hidden>
      ) : null}

      <Breadcrumbs
        separator={<Icon sx={{ color: hint }}>navigate_next</Icon>}
        sx={{ display: 'flex', alignItems: 'center', position: 'relative' }}
      >
        <NavLink to={commonRoutes.home}>
          <StyledIcon color="primary">home</StyledIcon>
        </NavLink>

        {routeSegments
          ? routeSegments.map((route, index) => {
              return index !== routeSegments.length - 1 ? (
                <NavLink key={index} to={route.path}>
                  <SubName>{route.name}</SubName>
                </NavLink>
              ) : (
                <SubName key={index}>{route.name}</SubName>
              );
            })
          : null}
      </Breadcrumbs>
    </BreadcrumbRoot>
  );
};

export default Breadcrumb;
