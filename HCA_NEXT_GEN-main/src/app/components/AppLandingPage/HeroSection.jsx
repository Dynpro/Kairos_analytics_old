import { useEffect, useState } from 'react';

import { Box, Button, Container, Grid, Paper, Typography, Zoom } from '@mui/material';
import BgImg from './assets/images/8.jpg';
import WhiteSVG from './assets/images/header-shape.svg';

const BlueWord = ({ word, style }) => <span style={{ color: '#1476bd', ...style }}>{word}</span>;

export default function HeroSection() {
  const [shouldShow, setShouldShow] = useState(false);
  useEffect(() => setShouldShow(true), []);
  return (
    <Paper
      sx={{
        height: '90vh',
        position: 'relative',
      }}
      id="about"
    >
      <div
        style={{
          backgroundImage:
            'linear-gradient(rgba(232, 237, 255, 0.9), rgba(239, 254, 255, 0.9) 50%, rgba(254, 255, 244, 0.3) 68%, rgba(255, 255, 255, 0.12))',
          position: 'absolute',
          height: '100%',
          width: '100%',
          zIndex: 2,
        }}
      ></div>
      <img
        src={BgImg}
        alt="landingpage_mainimg"
        style={{
          position: 'absolute',
          height: '100%',
          width: '100%',
          zIndex: 1,
        }}
      />
      <img
        src={WhiteSVG}
        alt="shape"
        style={{
          position: 'absolute',
          bottom: '-10px',
          width: '100%',
          zIndex: 3,
        }}
      />
      <Container
        sx={{
          height: '100%',
        }}
        maxWidth="md"
      >
        <Grid
          sx={{
            height: '100%',
            zIndex: 100,
            position: 'relative',
            paddingTop: '175px',
          }}
          container
          justifyContent="space-between"
        >
          <Zoom in={shouldShow}>
            <Grid item sm={12} textAlign={'center'} color="black">
              <Typography
                component="h1"
                variant="h3"
                style={{
                  fontStyle: 'normal',
                  textAlign: 'center !important',
                  padding: '0',
                  boxSizing: 'border-box',
                  margin: '0px',
                  fontSize: '60px',
                  lineHeight: '55px',
                  color: 'rgb(129 131 134)',
                  fontWeight: 600,
                  paddingBottom: '18px',
                  letterSpacing: '0em',
                }}
              >
                All
                <BlueWord word={'Health '} />
                CHOICE
              </Typography>
              <Typography
                variant="h5"
                style={{
                  fontStyle: 'normal',
                  textAlign: 'center !important',
                  padding: '0',
                  boxSizing: 'border-box',
                  fontWeight: 400,
                  margin: '0px',
                  fontSize: '20px',
                  lineHeight: '32px',
                  color: '#6c6c6c',
                  marginTop: '16px',
                }}
              >
                All
                <BlueWord style={{ fontWeight: 'bold' }} word={'Health'} /> CHOICE is a data driven
                population health management company that evokes change behaviors and improves
                health outcomes resulting in overall reduced healthcare expenses.
              </Typography>
              <Box my={2}>
                <Button
                  href="https://allhealthchoice.com/"
                  variant="contained"
                  style={{
                    letterSpacing: '0.00938em',
                    fontFamily: "'Poppins', sans-serif",
                    fontStyle: 'normal',
                    listStyleType: 'none',
                    margin: '0',
                    boxSizing: 'border-box',
                    textDecoration: 'none',
                    display: 'inline-block',
                    fontWeight: 700,
                    textAlign: 'center',
                    whiteSpace: 'nowrap',
                    verticalAlign: 'middle',
                    userSelect: 'none',
                    padding: '0 32px',
                    fontSize: '16px',
                    cursor: 'pointer',
                    zIndex: 5,
                    transition: 'all 0.4s ease-out 0s',
                    position: 'relative',
                    textTransform: 'uppercase',
                    borderRadius: '50px',
                    overflow: 'hidden',
                    boxShadow: '0 3px 6px 0 rgba(0, 0, 0, 0.16)',
                    border: '0',
                    lineHeight: '50px',
                    color: '#fff',
                    backgroundColor: 'dodgerblue',
                  }}
                >
                  Get in Touch!
                </Button>
              </Box>
            </Grid>
          </Zoom>
        </Grid>
      </Container>
    </Paper>
  );
}
