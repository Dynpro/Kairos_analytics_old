import * as Yup from 'yup';

const initialValues = {
  personalInfo: {
    entity_id: process.env.REACT_APP_env_entity_id,
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    permissions: '',
    user_attributes: '',
    group_id: null,
    role: null,
  },
  userAccess: {
    users: false,
    user_add: false,
    user_edit: false,
    user_delete: false,
    user_view: false,
    looker: false,
    snowflake: false,
    clients: false,
    client_add: false,
    client_edit: false,
    client_delete: false,
    client_view: false,
    group_module: false,
    group_add: false,
    group_edit: false,
    group_delete: false,
    group_view: false,
    reports: false,
    report_add: false,
    report_edit: false,
    report_delete: false,
    report_view: false,
    roles: false,
    role_add: false,
    role_edit: false,
    role_delete: false,
    role_view: false,
    generate_report: false,
    generate_report_add: false,
    generate_report_edit: false,
    generate_report_delete: false,
    generate_report_view: false,
    invite_user: false,
    approval_pending_user: false,
    permission_btn: false,
    referral: false,
  },
  dashboardAccess: {
    Dashboard_Access: [],
    Client_List: [],
  },
  checked: [],
};

const EditUserSchema = Yup.object().shape({
  personalInfo: Yup.object().shape({
    first_name: Yup.string()
      .min(2, 'Too Short!')
      .max(50, 'Too Long!')
      .required('Required')
      .label('Firstname'),
    last_name: Yup.string()
      .min(2, 'Too Short!')
      .max(50, 'Too Long!')
      .required('Required')
      .label('Lastname'),
    email: Yup.string().email('Invalid email').required('Required').label('Email'),
    permissions: Yup.string().required('Please select a Permission').label('Permission').nullable(),
    role: Yup.number().required('Kindly select a role').label('Role').nullable(),
    group_id: Yup.number().required('Kindly select a Group').label('Group').nullable(),
  }),
});

//  "Must Contain 8 Characters, One Uppercase, One Lowercase, One Number and One Special Case Character"
const CreateUserSchema = Yup.object().shape({
  personalInfo: Yup.object().shape({
    first_name: Yup.string()
      .min(2, 'Too Short!')
      .max(50, 'Too Long!')
      .required('Required')
      .label('Firstname'),
    last_name: Yup.string()
      .min(2, 'Too Short!')
      .max(50, 'Too Long!')
      .required('Required')
      .label('Lastname'),
    email: Yup.string().email('Invalid email').required('Required').label('Email'),
    password: Yup.string()
      .required(
        'Must Contain 8 Characters, 1 Uppercase, 1 Lowercase, 1 Number and 1 Special Case Character'
      )
      .matches(
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})/,
        'Must Contain 8 Characters, 1 Uppercase, 1 Lowercase, 1 Number and 1 Special Case Character'
      )
      .max(16)
      .label('Password'),
    permissions: Yup.string().required('Please select a Permission').label('Permission').nullable(),
    role: Yup.number().required('Kindly select a role').label('Role').nullable(),
    group_id: Yup.number().required('Kindly select a Group').label('Group').nullable(),
  }),
});

function retDashboardAccessObj(checkedArray) {
  const Dashboard_Access = [];
  const Client_List = [];
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
      Client_List.push({
        subcategory_id: item[0] || null,
        client_primary_id: item[1] || null,
        client_id: item[2] || null,
      });
    });
  return {
    Dashboard_Access: Dashboard_Access,
    Client_List: Client_List.filter((value, index) => {
      const _value = JSON.stringify(value);
      return (
        index ===
        Client_List.findIndex((obj) => {
          return JSON.stringify(obj) === _value;
        })
      );
    }),
  };
}

function retUserAccessObj(obj) {
  let newObj = {};
  for (let key in obj) newObj[key] = obj[key] === true ? 1 : 0;
  if (obj['reports']) newObj['phm'] = newObj['reports'];
  if (obj['snowflake']) newObj['matillion'] = newObj['snowflake'];
  return newObj;
}
function retRoleAccessObj(obj) {
  let newObj = {};
  for (let key in obj) newObj[key] = obj[key] === 1 ? true : false;

  return newObj;
}

function convertDashboardAccessObjsToCheckedString(dashboardAccessObjs) {
  return dashboardAccessObjs
    .map((item) => {
      const { client_primary_id, client_id, folder_id, dashboard_id, subcategory_id = null } = item;

      return `${subcategory_id}_${client_primary_id}_${client_id}_${folder_id}_${dashboard_id}`;
    })
    .filter((item) => item !== undefined);
}

function compareResponseAndInitialObj(responseObj, initalObj) {
  if (responseObj === undefined) return initalObj;
  const newobj = {};
  const newObj2 = {};
  for (let key in responseObj) {
    if (key in initalObj) newobj[key] = responseObj[key];
  }
  for (let key in newobj) newObj2[key] = newobj[key] === 1 ? true : false;
  return newObj2;
}

function createInitalValues(responseObj) {
  if (responseObj.user_details && responseObj.role_access)
    return {
      personalInfo: {
        ...responseObj.user_details[0],
        group_id: responseObj.user_details[0].user_group_id,
        first_name: responseObj.user_details[0].name,
      },
      userAccess: {
        ...compareResponseAndInitialObj(responseObj.role_access[0], initialValues.userAccess),
      },
      dashboardAccess: {
        Dashboard_Access: [],
        Client_List: [],
      },
      checked: [...convertDashboardAccessObjsToCheckedString(responseObj.dashboard_access)],
    };
  return null;
}

function transformUserAccessObj(initalObj, responseObj = {}) {
  const obj = {};
  for (let key in responseObj) {
    if (key in initalObj) obj[key] = responseObj[key];
  }
  if (responseObj.hasOwnProperty('phm')) obj['reports'] = responseObj['phm'];
  if (responseObj.hasOwnProperty('matillion')) obj['snowflake'] = responseObj['matillion'];
  return obj;
}

const util = {
  initialValues,
  CreateUserSchema,
  EditUserSchema,
  createInitalValues,
  retDashboardAccessObj,
  retUserAccessObj,
  retRoleAccessObj,
  transformUserAccessObj,
  convertDashboardAccessObjsToCheckedString,
};

export default util;
