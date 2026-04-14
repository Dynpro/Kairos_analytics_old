import React from 'react';
import AppGenerateReportsList from './AppGenerateReportsList';
import AppGenerateSUMReportsList from './AppGenerateSUMReportsList';
import { Tabs, Tab, Typography, Box } from '@mui/material';
import { Breadcrumb } from 'app/components';

function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`simple-tabpanel-${index}`}
      aria-labelledby={`simple-tab-${index}`}
      {...other}
    >
      {value === index && (
        <Box sx={{ p: 3 }}>
          <Typography component={'span'} variant="body2">
            {children}
          </Typography>
        </Box>
      )}
    </div>
  );
}

function a11yProps(index) {
  return {
    id: `simple-tab-${index}`,
    'aria-controls': `simple-tabpanel-${index}`,
  };
}

export default function AppGenerateReportsSplitList() {
  const [value, setValue] = React.useState(0);

  const handleChange = (event, newValue) => {
    setValue(newValue);
  };

  return (
    <>
      <Box className="breadcrumb" sx={{ m: 1 }}>
        <Breadcrumb routeSegments={[{ name: 'Generate Reports' }]} />
      </Box>
      <Box sx={{ width: '100%' }}>
        <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
          <Tabs
            value={value}
            onChange={handleChange}
            aria-label="basic tabs example"
            indicatorColor="secondary"
            textColor="secondary"
          >
            <Tab label="PHM REPORTS" {...a11yProps(0)} />
            <Tab label="Patient Summary Reports" {...a11yProps(1)} />
          </Tabs>
        </Box>
        <TabPanel value={value} index={0}>
          <AppGenerateReportsList />
        </TabPanel>
        <TabPanel value={value} index={1}>
          <AppGenerateSUMReportsList />
        </TabPanel>
      </Box>
    </>
  );
}
