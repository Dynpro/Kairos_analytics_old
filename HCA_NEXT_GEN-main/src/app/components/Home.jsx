import React from 'react';
import { Grid, Link, styled } from '@mui/material';
import KairosLogo from 'app/components/icons/kairos.png';

const MenuPathRoot = styled('div')(({ theme }) => ({
  display: 'flex',
  flexWrap: 'wrap',
  alignItems: 'center',
  backgroundColor: theme.palette.background.default,
  padding: '10px',
}));

const MenuPathName = styled('h4')(({ theme }) => ({
  margin: 0,
  fontSize: '16px',
  paddingBottom: '1px',
  verticalAlign: 'middle',
  textTransform: 'capitalize',
  color: theme.palette.text.secondary,
}));

const Home = () => (
  <div style={{ backgroundColor: '#f5f8fa', height: '100%' }}>
    <MenuPathRoot>{/* <MenuPathName>Kairos Landing Page</MenuPathName> */}</MenuPathRoot>
    <Grid container spacing={1}>
      <Grid item sm={12}>
        {/* First row, two columns */}
        <Grid container spacing={1}>
          <Grid item sm={5}>
            <img
              style={{
                objectFit: 'contain',
                height: '80%',
                width: '80%',
                marginTop: '5px',
              }}
              src={KairosLogo}
            />
            {/* 45% width */}
            {/* Content for the left column */}
          </Grid>
          <Grid item sm={7} style={{ textAlign: 'center' }}>
            <h1 style={{ fontSize: '48px', margin: 0, marginRight: '6%' }}>
              <span style={{ color: '#00B0F0' }}>Welcome to </span>
              <span style={{ color: '#006FC0', fontWeight: 'lighter' }}>
                Kairos Research Partners
              </span>
            </h1>
            {/* 55% width */}
            {/* Content for the right column */}
          </Grid>
        </Grid>
      </Grid>

      <Grid item sm={12}>
        {/* First row, two columns */}
        <Grid container spacing={1}>
          <Grid item lg={8}>
            <img
              style={{
                objectFit: 'contain',
                width: '90%',
                paddingLeft: '10%',
                marginTop: '-195px',
                marginBottom: '-125px',
              }}
              src={'/assets/images/landing-page/104.png'}
            />
            {/* 45% width */}
            {/* Content for the left column */}
          </Grid>
          <Grid item lg={4}>
            <div
              style={{
                width: '400px',
                fontSize: '36px',
                lineHeight: '38px',
                textAlign: 'right',
                color: '#0070C0',
              }}
            >
              <p style={{ margin: 0 }}>
                Kairos has served as a non-biased third party to investigate worksite clinic
                performance, wellness programs, and worksite safety programs on behalf of various
                self-funded employers, including State and City governments
              </p>
              <img
                style={{
                  width: '180px',
                }}
                src={'/assets/images/landing-page/Info_3.png'}
              />
            </div>

            {/* 55% width */}
            {/* Content for the right column */}
          </Grid>
        </Grid>
      </Grid>

      <Grid
        item
        sm={12}
        color="#2383C8"
        style={{
          textAlign: 'center',
          fontWeight: 'normal',
          fontSize: '20px',
          marginLeft: '10%',
          marginRight: '10%',
        }}
      >
        <p>
          <p>
            <div>
              Kairos Research Partners is a health care analytics consulting company, which delivers
              sophisticated data analysis in order to generate evidence-based population health
              management solutions. Kairos utilizes a proprietary relational database and reporting
              suite with the ability to integrate, store, and analyze various data sets.
            </div>
            {/* <div>
              Please find our detailed user guides available at{' '}
              <Link
                color="#2C4B67"
                href="https://education.ahcanalytics.com/"
                target="_blank"
                rel="noreferrer"
              >
                education.ahcanalytics.com
              </Link>
              .
            </div>

            <div>
              Please contact{' '}
              <Link
                style={{ color: '#2C4B67' }}
                target="_blank"
                href="mailto:support@allhealthchoice.com"
              >
                support@allhealthchoice.com
              </Link>{' '}
              with any questions.
            </div> */}
          </p>

          {/* <p>
            <div>
              AllHealth CHOICE can also help to identify and implement customized population health
              management solutions.
            </div>
            <div>
              To learn more, please visit{' '}
              <Link
                color="#2C4B67"
                href="https://allhealthchoice.com/"
                target="_blank"
                rel="noreferrer"
              >
                allhealthchoice.com
              </Link>
              .
            </div>
          </p> */}
        </p>
        {/* Third row */}
        {/* 100% width */}
        {/* Content for the full-width row */}
      </Grid>
    </Grid>
  </div>
);

export default Home;
