import {
  AppBar,
  Box,
  Container,
  Divider,
  Hidden,
  IconButton,
  List,
  ListItem,
  SwipeableDrawer,
  Toolbar,
} from '@mui/material';
import { useEffect, useState } from 'react';
import Logo from './assets/images/ahc_logo.png';

import { ChevronRight as ChevronRightIcon, Menu as MenuIcon } from '@mui/icons-material';
import { NavLink } from 'react-router-dom';

const externalLinks = [{ name: 'User Guides', href: 'https://education.ahcanalytics.com/' }];

const internalLinks = [
  { name: 'Login', href: '/signin' },
  { name: 'Register', href: '/signup' },
];

const NavBarItem = ({ itemName }) => (
  <span
    style={{
      marginRight: 20,
      color: 'rgb(20 118 189)',
      textDecoration: 'none',
      '&:hover': {
        color: 'rgb(20 118 189)',
        fontWeight: 'bold',
        textDecoration: 'underline',
      },
      fontSize: '16px',
      lineHeight: '24px',
      fontWeight: 700,
      padding: '26px 0',
      textTransform: 'uppercase',
      position: 'relative',
      transition: 'all .3s ease-out 0s',
    }}
  >
    {itemName}
  </span>
);

const CustomListItem = ({ item, external = false }) => {
  const linkStyle = {
    marginRight: 20,
    color: 'black',
    textDecoration: 'none',
  };

  const hoverStyle = {
    '&:hover': {
      color: 'blue',
    },
  };

  const combinedStyle = { ...linkStyle, ...hoverStyle };

  const Component = external ? 'a' : NavLink;

  return (
    <ListItem key={item.name}>
      <Component
        style={combinedStyle}
        variant={external ? undefined : 'button'}
        underline={external ? 'none' : 'hover'}
        target={external ? '_blank' : undefined}
        rel={external ? 'noopener noreferrer' : undefined}
        href={external ? item.href : undefined}
        to={external ? undefined : item.href}
      >
        {item.name}
      </Component>
    </ListItem>
  );
};

export default function Header() {
  const [open, setOpen] = useState(false);
  const [scrollPosition, setScrollPosition] = useState(0);
  const handleScroll = () => {
    const position = window.pageYOffset;
    setScrollPosition(position);
  };
  useEffect(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });

    return () => {
      window.removeEventListener('scroll', handleScroll);
    };
  }, []);
  const determineNavBarBgColor = (scrollCount) => (scrollCount > 100 ? 'white' : 'transparent');
  return (
    <AppBar
      position="sticky"
      style={{
        color: 'black',
        boxShadow: 'none',
        backgroundColor: determineNavBarBgColor(scrollPosition),
        position: 'fixed',
        top: 0,
      }}
    >
      <Container maxWidth="lg">
        <Toolbar>
          <Box
            sx={{
              width: '100%',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
            }}
          >
            <img src={Logo} alt="Logo" />
            <Hidden smDown>
              <div>
                {externalLinks.map((item) => (
                  <a
                    key={item.name}
                    href="https://education.ahcanalytics.com/"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    <NavBarItem itemName="User Guides" />
                  </a>
                ))}

                {internalLinks.map((item) => (
                  <NavLink key={item.name} variant="button" underline="none" to={item.href}>
                    <NavBarItem itemName={item.name} />
                  </NavLink>
                ))}
              </div>
            </Hidden>
          </Box>
          <Hidden smUp>
            <IconButton onClick={() => setOpen(true)}>
              <MenuIcon />
            </IconButton>
          </Hidden>
        </Toolbar>
      </Container>
      <SwipeableDrawer
        anchor="right"
        open={open}
        onOpen={() => setOpen(true)}
        onClose={() => setOpen(false)}
      >
        <div
          onClick={() => setOpen(false)}
          onKeyPress={() => setOpen(false)}
          role="button"
          tabIndex={0}
        >
          <IconButton>
            <ChevronRightIcon />
          </IconButton>
        </div>
        <Divider />
        <List>
          {externalLinks.map((item) => (
            <CustomListItem key={item.name} item={item} external />
          ))}
          {internalLinks.map((item) => (
            <CustomListItem key={item.name} item={item} />
          ))}
        </List>
      </SwipeableDrawer>
    </AppBar>
  );
}
