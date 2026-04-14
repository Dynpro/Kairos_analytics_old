import * as Yup from 'yup';
const initialValues = {
  role: '',
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
};
const CreateRoleSchema = Yup.object().shape({
  role: Yup.string().required('Role is Required'),
});

function retRoleAccessObj(obj) {
  const newObj = {};
  for (let key in obj) {
    if (typeof obj[key] === 'boolean') {
      newObj[key] = obj[key] === true ? 1 : 0;
    } else newObj[key] = obj[key];
  }
  return newObj;
}

function compareResponseAndInitialObj(responseObj, initalObj) {
  const newObj = { ...initalObj, ...responseObj };
  const newObj2 = {};
  for (let key in newObj) {
    if (typeof newObj[key] === 'number' && (newObj[key] === 0 || newObj[key] === 1)) {
      newObj2[key] = newObj[key] === 1 ? true : false;
    } else newObj2[key] = newObj[key];
  }
  if (responseObj.role_id) newObj2['role_id'] = responseObj['role_id'];
  return newObj2;
}

function createInitalValues(responseObj) {
  if (responseObj[0] && responseObj[0].role)
    return {
      ...compareResponseAndInitialObj(responseObj[0], initialValues),
    };
  return null;
}

const util = {
  initialValues,
  createInitalValues,
  retRoleAccessObj,
  //validationSchema,
  CreateRoleSchema,
};

export default util;
