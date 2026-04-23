const router = require('express').Router();
const Usuario = require('../models/Usuario');
const { authenticate, authorize } = require('../middleware/auth');

// GET /api/usuarios — solo admin
router.get('/', authenticate, authorize('admin'), async (req, res) => {
  try {
    const usuarios = await Usuario.getAll();
    res.json(usuarios);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// GET /api/usuarios/barberos — público
router.get('/barberos', async (req, res) => {
  try {
    const db = require('../config/db');
    const [rows] = await db.execute(
      "SELECT id, nombre, email, telefono FROM usuarios WHERE rol = 'barbero' ORDER BY nombre"
    );
    res.json(rows);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// GET /api/usuarios/:id
router.get('/:id', authenticate, async (req, res) => {
  try {
    const user = await Usuario.findById(req.params.id);
    if (!user) return res.status(404).json({ error: 'Usuario no encontrado' });
    res.json(user);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

module.exports = router;
