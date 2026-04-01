import * as Yup from 'yup';

const initialValues = {
  group_flag: 0,
  group_id: null,
  group_name: '',
  role_id: null,
  dashboardAccess: {
    Dashboard_Access: [],
    Client_List: [],
  },
  checked: [],
};

const validationSchema = Yup.object().shape({
  group_name: Yup.string().min(2).required('Kindly enter a valid group name').label('Group Name'),
  role_id: Yup.number().required('Kindly select a role').label('Role').nullable(),
  checked: Yup.array().min(1, 'Kindly select atleast one Dashboard'),
});
const validationEditSchema = Yup.object().shape({
  group_id: Yup.number().required('Kindly select a Group').label('Group ID').nullable(),
  role_id: Yup.number().required('Kindly select a role').label('Role').nullable(),
  checked: Yup.array().min(1, 'Kindly select atleast one Dashboard'),
});

function createInitalValues(responseObj) {
  if (responseObj.group_details && responseObj.dashboard_details) {
    return {
      ...initialValues,
      group_flag: 1,
      group_id: responseObj.group_details[0].group_id,
      group_name: responseObj.group_details[0].group_name,
      role_id: responseObj.group_details[0].role_id,
      checked: [...convertDashboardAccessObjsToCheckedString(responseObj.dashboard_details)],
    };
  }
  return null;
}

function retDashboardAccessObj(checkedArray) {
  const Dashboard_Access = [];
  checkedArray
    .map((item) => item.split('_'))
    .map((item) => {
      Dashboard_Access.push({
        subcategory_id: item[0] === 'null' ? null : item[0] || null,
        client_primary_id: item[1] === 'null' ? null : item[1] || null,
        client_id: item[2] === 'null' ? null : item[2] || null,
        folder_id: item[3] === 'null' ? null : item[3] || null,
        dashboard_id: item[4] === 'null' ? null : item[4] || null,
      });
    });
  return Dashboard_Access;
}

function convertValuesToFormat(formikValuesObj) {
  return retDashboardAccessObj(formikValuesObj);
}

function convertDashboardAccessObjsToCheckedString(dashboardAccessObjs) {
  return dashboardAccessObjs
    .map((item) => {
      const { client_primary_id, client_id, folder_id, dashboard_id, subcategory_id = null } = item;
      return `${subcategory_id}_${client_primary_id}_${client_id}_${folder_id}_${dashboard_id}`;
    })
    .filter((item) => item !== undefined);
}

export default {
  initialValues,
  validationSchema,
  validationEditSchema,
  convertValuesToFormat,
  createInitalValues,
  convertDashboardAccessObjsToCheckedString,
  retDashboardAccessObj,
};
