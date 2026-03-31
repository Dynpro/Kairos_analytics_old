import React from 'react';
import { Grid } from '@mui/material';
import CallToAction from './assets/images/12.jpg';

const LearnMore = () => {
  return (
    <Grid container sx={{ backgroundColor: '#0067f4', textAlign: 'center' }}>
      <Grid item sm={12} md={6}>
        <img
          style={{
            maxWidth: '100%',
            width: '100%',
            height: '100%',
            objectFit: 'cover',
            objectPosition: 'center center',
          }}
          src={CallToAction}
          alt="call-to-action"
        />
      </Grid>
      <Grid
        item
        sm={12}
        md={6}
        style={{ display: 'flex', justifyContent: 'center', alignItems: 'center' }}
      >
        <h1 style={{ margin: '0 auto', color: 'white' }}>
          Lean More about the AllHealth CHOICE Analytics Tool today!
        </h1>
      </Grid>
    </Grid>
  );
};

export default LearnMore;
