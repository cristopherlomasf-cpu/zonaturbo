const db = require('../config/db');

class Servicio {
  static async getAll() {
    const [rows] = await db.execute('SELECT * FROM servicios WHERE activo = 1 ORDER BY nombre');
    return rows;
  }

  static async getById(id) {
    const [rows] = await db.execute('SELECT * FROM servicios WHERE id = ?', [id]);
    return rows[0] || null;
  }

  static async create({ nombre, descripcion, precio, duracion_min }) {
    const [result] = await db.execute(
      'INSERT INTO servicios (nombre, descripcion, precio, duracion_min) VALUES (?, ?, ?, ?)',
      [nombre, descripcion || null, precio, duracion_min]
    );
    return result.insertId;
  }

  static async update(id, { nombre, descripcion, precio, duracion_min, activo }) {
    await db.execute(
      'UPDATE servicios SET nombre=?, descripcion=?, precio=?, duracion_min=?, activo=? WHERE id=?',
      [nombre, descripcion || null, precio, duracion_min, activo, id]
    );
  }
}

module.exports = Servicio;
