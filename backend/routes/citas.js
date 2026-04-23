const router = require('express').Router();
const Cita = require('../models/Cita');
const { authenticate, authorize } = require('../middleware/auth');

// GET /api/citas
router.get('/', authenticate, async (req, res) => {
  try {
    const filtros = {};
    if (req.user.rol === 'cliente') filtros.cliente_id = req.user.id;
    if (req.user.rol === 'barbero') filtros.barbero_id = req.user.id;
    if (req.query.fecha) filtros.fecha = req.query.fecha;
    const citas = await Cita.getAll(filtros);
    res.json(citas);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// GET /api/citas/:id
router.get('/:id', authenticate, async (req, res) => {
  try {
    const cita = await Cita.getById(req.params.id);
    if (!cita) return res.status(404).json({ error: 'Cita no encontrada' });
    res.json(cita);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// POST /api/citas
router.post('/', authenticate, async (req, res) => {
  try {
    const { barbero_id, servicio_id, fecha_hora, notas } = req.body;
    if (!barbero_id || !servicio_id || !fecha_hora)
      return res.status(400).json({ error: 'barbero_id, servicio_id y fecha_hora son requeridos' });
    const id = await Cita.create({ cliente_id: req.user.id, barbero_id, servicio_id, fecha_hora, notas });
    const cita = await Cita.getById(id);
    res.status(201).json(cita);
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// PATCH /api/citas/:id/estado
router.patch('/:id/estado', authenticate, authorize('admin', 'barbero'), async (req, res) => {
  try {
    const { estado } = req.body;
    const validos = ['pendiente', 'confirmada', 'completada', 'cancelada'];
    if (!validos.includes(estado))
      return res.status(400).json({ error: 'Estado inválido' });
    await Cita.updateEstado(req.params.id, estado);
    res.json({ message: 'Estado actualizado' });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

// DELETE /api/citas/:id
router.delete('/:id', authenticate, async (req, res) => {
  try {
    await Cita.delete(req.params.id);
    res.json({ message: 'Cita eliminada' });
  } catch (err) {
    res.status(500).json({ error: err.message });
  }
});

module.exports = router;
