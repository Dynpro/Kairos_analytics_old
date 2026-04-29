import React from 'react';
import AppGenerateReportsList from './AppGenerateReportsList';
import AppGenerateSUMReportsList from './AppGenerateSUMReportsList';
import StudioTemplateConfig from './StudioTemplateConfig';
import { Tabs, Tab, Typography, Box } from '@mui/material';
import { Breadcrumb } from 'app/components';
import { useSelector } from 'react-redux';

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

  // Only superadmins see the Studio Template tab
  const isSuperAdmin = useSelector(
    (state) => state.userDetails?.user?.role === 'Super Admin'
  );

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
            aria-label="generate reports tabs"
            indicatorColor="secondary"
            textColor="secondary"
          >
            <Tab label="PHM REPORTS" {...a11yProps(0)} />
            <Tab label="Patient Summary Reports" {...a11yProps(1)} />
            {isSuperAdmin && <Tab label="Studio Templates" {...a11yProps(2)} />}
          </Tabs>
        </Box>
        <TabPanel value={value} index={0}>
          <AppGenerateReportsList />
        </TabPanel>
        <TabPanel value={value} index={1}>
          <AppGenerateSUMReportsList />
        </TabPanel>
        {isSuperAdmin && (
          <TabPanel value={value} index={2}>
            <StudioTemplateConfig />
          </TabPanel>
        )}
      </Box>
    </>
  );
}
