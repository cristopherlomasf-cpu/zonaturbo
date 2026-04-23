const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
require('dotenv').config();

const authRoutes = require('./routes/auth');
const citasRoutes = require('./routes/citas');
const serviciosRoutes = require('./routes/servicios');
const usuariosRoutes = require('./routes/usuarios');

const app = express();

app.use(helmet());
app.use(cors({ origin: process.env.CLIENT_URL || '*' }));
app.use(express.json());

app.get('/health', (req, res) => res.json({ status: 'ok', app: 'Zona Turbo API' }));

app.use('/api/auth', authRoutes);
app.use('/api/citas', citasRoutes);
app.use('/api/servicios', serviciosRoutes);
app.use('/api/usuarios', usuariosRoutes);

app.use((err, req, res, next) => {
  console.error(err.stack);
  res.status(500).json({ error: 'Error interno del servidor' });
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log(`Zona Turbo API corriendo en puerto ${PORT}`));

module.exports = app;
