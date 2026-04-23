const router = require('express').Router();
const Usuario = require('../models/Usuario');
const { generateToken } = require('../config/jwt');
const { authenticate } = require('../middleware/auth');

// POST /api/auth/register
router.post('/register', async (req, res) => {
  try {
    const { nombre, email, password, telefono } = req.body;
    if (!nombre || !email || !password)
      return res.status(400).json({ error: 'nombre, email y password son requeridos' });
    const existe = await Usuario.findByEmail(email);
    if (existe) return res.status(409).json({ error: 'El email ya está registrado' });
    const id = await Usuario.create({ nombre, email, password, telefono });
    const user = await Usuario.findById(id);
    const token = generateToken({ id: user.id, email: user.email, rol: user.rol });
    res.status(201).json({ token, user });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// POST /api/auth/login
router.post('/login', async (req, res) => {
  try {
    const { email, password } = req.body;
    if (!email || !password)
      return res.status(400).json({ error: 'email y password requeridos' });
    const user = await Usuario.findByEmail(email);
    if (!user) return res.status(401).json({ error: 'Credenciales inválidas' });
    const valid = await Usuario.comparePassword(password, user.password);
    if (!valid) return res.status(401).json({ error: 'Credenciales inválidas' });
    const token = generateToken({ id: user.id, email: user.email, rol: user.rol });
    const { password: _, ...userSafe } = user;
    res.json({ token, user: userSafe });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// GET /api/auth/me
router.get('/me', authenticate, async (req, res) => {
  try {
    const user = await Usuario.findById(req.user.id);
    if (!user) return res.status(404).json({ error: 'Usuario no encontrado' });
    res.json(user);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

module.exports = router;
