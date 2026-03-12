<?php
const DEFAULT_DB = 'mysql';
const ENV_MODE =  'DEV';

const DB_SERVER = 'localhost';
const DB_USERNAME = 'root';
const DB_PASSWORD = '';
const DB_NAME = 'ioi-sales';

// const DB_SERVER = 'tuinstra.iad1-mysql-e2-10a.dreamhost.com';
// const DB_USERNAME = 'salespfadmin';
// const DB_PASSWORD = 'un1t3dn30ngr0up';
// const DB_NAME = 'salesdb_live';

const HEADER = [
    'alg' => 'HS256',
    'typ' => 'JWT'
];
const SECRET = 'salesioiapi';

// const MAIL_USERNAME = 'noreply@unitedneon.com';
// const MAIL_FROM = 'noreply@unitedneon.com';
// const MAIL_NAME = 'UNMG Sales Platform Admin';
// const MAIL_PASSWORD = 'ydcncqqkjjmtuvkb';

date_default_timezone_set('Asia/Manila');