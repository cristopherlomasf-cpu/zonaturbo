const router = require('express').Router();
const Servicio = require('../models/Servicio');
const { authenticate, authorize } = require('../middleware/auth');

router.get('/', async (req, res) => {
  try {
    const servicios = await Servicio.getAll();
    res.json(servicios);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

router.get('/:id', async (req, res) => {
  try {
    const s = await Servicio.getById(req.params.id);
    if (!s) return res.status(404).json({ error: 'Servicio no encontrado' });
    res.json(s);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

router.post('/', authenticate, authorize('admin'), async (req, res) => {
  try {
    const { nombre, descripcion, precio, duracion_min } = req.body;
    if (!nombre || !precio || !duracion_min)
      return res.status(400).json({ error: 'nombre, precio y duracion_min requeridos' });
    const id = await Servicio.create({ nombre, descripcion, precio, duracion_min });
    res.status(201).json({ id, message: 'Servicio creado' });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

router.put('/:id', authenticate, authorize('admin'), async (req, res) => {
  try {
    await Servicio.update(req.params.id, req.body);
    res.json({ message: 'Servicio actualizado' });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

module.exports = router;
