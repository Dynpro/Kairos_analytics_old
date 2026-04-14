import React from 'react';
import AppLocalizedClients from './LocalizationLists/AppLocalizedClients';
import { Tabs, Tab, Typography, Box } from '@mui/material';
import { Breadcrumb } from 'app/components';
import commonConfig from '../commonConfig';

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

export default function AppLocalizationSplitList() {
  const [value, setValue] = React.useState(0);

  const handleChange = (event, newValue) => {
    setValue(newValue);
  };

  return (
    <>
      <Box className="breadcrumb" sx={{ m: 1 }}>
        <Breadcrumb routeSegments={[{ name: 'Localization' }]} />
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
            <Tab label="Looker Client List" {...a11yProps(0)} />
            <Tab label="PHM Folder Client List" {...a11yProps(1)} />
            <Tab label="Looker Dashboards List" {...a11yProps(2)} />
          </Tabs>
        </Box>
        <TabPanel value={value} index={0}>
          <AppLocalizedClients
            fetchListUrl={commonConfig.urls.localizedClients}
            diff={false}
            listName="LOOKER CLIENTS"
            refreshUrl={commonConfig.urls.pullLookerClients}
          />
        </TabPanel>
        <TabPanel value={value} index={1}>
          <AppLocalizedClients
            fetchListUrl={commonConfig.urls.localizedPHMClients}
            diff={false}
            listName="PHM FOLDER CLIENTS"
            refreshUrl={commonConfig.urls.pullLookerPHMFolders}
          />
        </TabPanel>
        <TabPanel value={value} index={2} diff={true}>
          <AppLocalizedClients
            fetchListUrl={commonConfig.urls.localizedDashboards}
            diff={true}
            listName="LOOKER DASHBOARDS"
            refreshUrl={commonConfig.urls.pullLookerDashboards}
          />
        </TabPanel>
      </Box>
    </>
  );
}
