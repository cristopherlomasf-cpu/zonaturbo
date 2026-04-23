const jwt = require('jsonwebtoken');

const SECRET = process.env.JWT_SECRET || 'zonaturbo_secret_dev';
const EXPIRES = process.env.JWT_EXPIRES || '7d';

const generateToken = (payload) => jwt.sign(payload, SECRET, { expiresIn: EXPIRES });

const verifyToken = (token) => jwt.verify(token, SECRET);

module.exports = { generateToken, verifyToken };
