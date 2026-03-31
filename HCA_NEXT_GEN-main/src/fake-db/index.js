import Mock from './mock';

import './db/auth';
import './db/ecommerce';
import './db/notification';

Mock.onAny().passThrough();
//console.log('mock passed', Mock.onAny().passThrough());
