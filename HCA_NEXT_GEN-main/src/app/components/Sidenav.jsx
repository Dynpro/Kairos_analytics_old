// import { styled } from '@mui/system';
// import { MatxVerticalNav } from 'app/components';
// import useSettings from 'app/hooks/useSettings';
// import { createSubCategory } from 'app/navigations';
// import { Fragment, memo, useMemo, useState } from 'react';
// import Scrollbar from 'react-perfect-scrollbar';
// import { useSelector } from 'react-redux';

// const StyledScrollBar = styled(Scrollbar)(() => ({
//   paddingLeft: '0.1rem',
//   paddingRight: '0.1rem',
//   position: 'relative',
// }));

// const SideNavMobile = styled('div')(({ theme }) => ({
//   position: 'fixed',
//   top: 0,
//   left: 0,
//   bottom: 0,
//   right: 0,
//   width: '100vw',
//   background: 'rgba(0, 0, 0, 0.54)',
//   zIndex: -1,
//   [theme.breakpoints.up('lg')]: { display: 'none' },
// }));

// const Sidenav = ({ children }) => {
//   const { uaSubCategories, uaFolders, uaDashboards, uaSubFolders } = useSelector((state) => ({
//     uaSubCategories: state.userAccess.uaSubCategories,
//     uaFolders: state.userAccess.uaFolders,
//     uaDashboards: state.userAccess.uaDashboards,
//     uaSubFolders: state.userAccess.uaSubFolders,
//   }));
//   const { settings, updateSettings } = useSettings();
//   const [searchVal, setSearchVal] = useState('');

//   const requestSearch = (items, searchedVal) => {
//     if (typeof searchedVal !== 'string' || !searchedVal.trim()) {
//       return items;
//     }

//     const searchTerms = searchedVal.trim().toLowerCase().split(' ');
//     const arr = [];
//     const onlyClients = [];
//     const sortedClients = [];


//     const searchInClients = (clients) => {
//       if (clients && Array.isArray(clients)) {
//         for (const client of clients) {
//           const clientDashboardName = client.client_name;
//           if (
//             searchTerms.every((searchItem) =>
//               clientDashboardName.toLowerCase().includes(searchItem)
//             )
//           ) {
//             sortedClients.push({
//               ...client,
//               name: `${client.client_name}`,
//             });
//           }
//         }
//       }
//     };

//     items.forEach((client) => {
//       // checks in clients
//       if (!client.subcategory_id) {
//         const newClient = { ...client };
//         delete newClient.children;
//         newClient.level = 3;
//         newClient.icon = 'person';
//         newClient.client_name = `${newClient?.client_name}`;
//         newClient.dashboard_name = `Default ${newClient?.client_name} Dashboard`;
//         onlyClients.push(newClient);

//         client.children.forEach((dashboard) => {
//           if (dashboard.children) {
//             dashboard.children.forEach((dashboardNew) => {
//               const clientDashboardName = dashboardNew.client_name + '' + dashboardNew.name;
//               const searchTerms = searchedVal.trim().split(' ');
//               if (
//                 searchTerms.every((searchItem) =>
//                   clientDashboardName.toLowerCase().includes(searchItem.toLowerCase())
//                 )
//               ) {
//                 arr.push({
//                   ...dashboardNew,
//                   name: `${dashboardNew.client_name} | ${dashboardNew.name}`,
//                 });
//               }
//             });
//           }
//         });
//         //checks in subcategories
//       } else if (client.subcategory_id) {
//         client.children.forEach((folder) => {
//           const newClient = { ...folder };
//           delete newClient.children;
//           newClient.level = 3;
//           newClient.icon = 'person';
//           newClient.client_name = `${newClient?.client_name}`;
//           newClient.dashboard_name = `Default ${newClient?.client_name} Dashboard`;
//           onlyClients.push(newClient);
//           if (folder.children) {
//             folder.children.forEach((dashboard) => {
//               if (dashboard.children) {
//                 dashboard.children.forEach((dashboardNew) => {
//                   const clientDashboardName = dashboardNew.client_name + '' + dashboardNew.name;
//                   const searchTerms = searchedVal.trim().split(' ');
//                   if (
//                     searchTerms.every((searchItem) =>
//                       clientDashboardName.toLowerCase().includes(searchItem.toLowerCase())
//                     )
//                   ) {
//                     arr.push({
//                       ...dashboardNew,
//                       name: `${dashboardNew.client_name} | ${dashboardNew.name}`,
//                     });
//                   }
//                 });
//               }
//             });
//           }
//         });
//       }
//     });
//     searchInClients(onlyClients);
//     return [...sortedClients, ...arr];
//   };


//   const updateSidebarMode = (sidebarSettings) => {
//     let activeLayoutSettingsName = settings.activeLayout + 'Settings';
//     let activeLayoutSettings = settings[activeLayoutSettingsName];

//     updateSettings({
//       ...settings,
//       [activeLayoutSettingsName]: {
//         ...activeLayoutSettings,
//         leftSidebar: {
//           ...activeLayoutSettings.leftSidebar,
//           ...sidebarSettings,
//         },
//       },
//     });
//   };

//   const searchResult = useMemo(() => {
//     return requestSearch(
//       createSubCategory(uaSubCategories || {}, uaFolders || {}, uaDashboards || {}, uaSubFolders || {}),
//       searchVal
//     );
//   }, [uaSubCategories, uaFolders, uaDashboards, uaSubFolders, searchVal]);

//   return (
//     <Fragment>
//       <StyledScrollBar options={{ suppressScrollX: true }}>
//         {children}
//         <MatxVerticalNav
//           items={[
//             {
//               label: 'Search Dashboards...',
//               type: 'search',
//               searchVal,
//               onSearch: (str) => setSearchVal(str),
//               totalCount: searchResult.length,
//             },
//             ...searchResult,
//           ]}
//         />
//       </StyledScrollBar>
//       <SideNavMobile onClick={() => updateSidebarMode({ mode: 'close' })} />
//     </Fragment>
//   );
// };

// export default memo(Sidenav);

import { styled } from '@mui/system';
import { MatxVerticalNav } from 'app/components';
import useSettings from 'app/hooks/useSettings';
import useRefreshData from 'app/hooks/useRefreshData';
import { createSubCategory } from 'app/navigations';
import { Fragment, memo, useMemo, useState } from 'react';
import Scrollbar from 'react-perfect-scrollbar';
import { useSelector } from 'react-redux';
import { useEffect } from 'react';

const StyledScrollBar = styled(Scrollbar)(() => ({
  paddingLeft: '0.1rem',
  paddingRight: '0.1rem',
  position: 'relative',
}));

const SideNavMobile = styled('div')(({ theme }) => ({
  position: 'fixed',
  top: 0,
  left: 0,
  bottom: 0,
  right: 0,
  width: '100vw',
  background: 'rgba(0, 0, 0, 0.54)',
  zIndex: -1,
  [theme.breakpoints.up('lg')]: { display: 'none' },
}));

const Sidenav = ({ children }) => {
  const { uaSubCategories, uaFolders, uaDashboards, uaSubFolders } = useSelector((state) => ({
    uaSubCategories: state.userAccess.uaSubCategories,
    uaFolders: state.userAccess.uaFolders,
    uaDashboards: state.userAccess.uaDashboards,
    uaSubFolders: state.userAccess.uaSubFolders,
  }));
  const { handleRefreshData } = useRefreshData();
  const { settings, updateSettings } = useSettings();
  const [searchVal, setSearchVal] = useState('');

  useEffect(() => {
    handleRefreshData();
  }, []);

  const requestSearch = (items, searchedVal) => {
    if (typeof searchedVal !== 'string' || !searchedVal.trim()) {
      return items;
    }
  
    const searchTerms = searchedVal.trim().toLowerCase().split(' ');
    const results = [];
  
    const searchInItem = (item) => {
      
      const clientName = item.client_name || '';
      const itemName = item.name || ''; 
  
      const clientDashboardName = `${clientName} ${itemName}`.toLowerCase();
      return searchTerms.every((term) => clientDashboardName.includes(term));
    };
  
    const processClient = (client) => {
      if (searchInItem(client)) {
        const clientName = client.client_name || ''; 
        const itemName = client.name || ''; 
        results.push({
          ...client,
          name: `${clientName} | ${itemName}`,
        });
      }
  
      if (client.children) {
        client.children.forEach((child) => processClient(child));
      }
    };
  
    items.forEach((item) => processClient(item));
  
    return results;
  };
 

  const updateSidebarMode = (sidebarSettings) => {
    let activeLayoutSettingsName = settings.activeLayout + 'Settings';
    let activeLayoutSettings = settings[activeLayoutSettingsName];

    updateSettings({
      ...settings,
      [activeLayoutSettingsName]: {
        ...activeLayoutSettings,
        leftSidebar: {
          ...activeLayoutSettings.leftSidebar,
          ...sidebarSettings,
        },
      },
    });
  };

  const searchResult = useMemo(() => {
    return requestSearch(
      createSubCategory(uaSubCategories || {}, uaFolders || {}, uaDashboards || {}, uaSubFolders || {}),
      searchVal
    );
  }, [uaSubCategories, uaFolders, uaDashboards, uaSubFolders, searchVal]);

  return (
    <Fragment>
      <StyledScrollBar options={{ suppressScrollX: true }}>
        {children}
        <MatxVerticalNav
          items={[
            {
              label: 'Search Dashboards...',
              type: 'search',
              searchVal,
              onSearch: (str) => setSearchVal(str),
              totalCount: searchResult.length,
            },
            ...searchResult,
          ]}
        />
      </StyledScrollBar>
      <SideNavMobile onClick={() => updateSidebarMode({ mode: 'close' })} />
    </Fragment>
  );
};

export default memo(Sidenav);