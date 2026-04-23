const db = require('../config/db');

class Cita {
  static async getAll({ barbero_id, cliente_id, fecha } = {}) {
    let query = `
      SELECT c.*, 
        u_cliente.nombre AS cliente_nombre,
        u_barbero.nombre AS barbero_nombre,
        s.nombre AS servicio_nombre, s.precio, s.duracion_min
      FROM citas c
      JOIN usuarios u_cliente ON c.cliente_id = u_cliente.id
      JOIN usuarios u_barbero ON c.barbero_id = u_barbero.id
      JOIN servicios s ON c.servicio_id = s.id
      WHERE 1=1`;
    const params = [];
    if (barbero_id) { query += ' AND c.barbero_id = ?'; params.push(barbero_id); }
    if (cliente_id) { query += ' AND c.cliente_id = ?'; params.push(cliente_id); }
    if (fecha)      { query += ' AND DATE(c.fecha_hora) = ?'; params.push(fecha); }
    query += ' ORDER BY c.fecha_hora ASC';
    const [rows] = await db.execute(query, params);
    return rows;
  }

  static async getById(id) {
    const [rows] = await db.execute(
      `SELECT c.*, u_c.nombre AS cliente_nombre, u_b.nombre AS barbero_nombre,
        s.nombre AS servicio_nombre, s.precio
       FROM citas c
       JOIN usuarios u_c ON c.cliente_id = u_c.id
       JOIN usuarios u_b ON c.barbero_id = u_b.id
       JOIN servicios s ON c.servicio_id = s.id
       WHERE c.id = ?`, [id]
    );
    return rows[0] || null;
  }

  static async create({ cliente_id, barbero_id, servicio_id, fecha_hora, notas }) {
    const [result] = await db.execute(
      'INSERT INTO citas (cliente_id, barbero_id, servicio_id, fecha_hora, notas) VALUES (?, ?, ?, ?, ?)',
      [cliente_id, barbero_id, servicio_id, fecha_hora, notas || null]
    );
    return result.insertId;
  }

  static async updateEstado(id, estado) {
    await db.execute('UPDATE citas SET estado = ? WHERE id = ?', [estado, id]);
  }

  static async delete(id) {
    await db.execute('DELETE FROM citas WHERE id = ?', [id]);
  }
}

module.exports = Cita;
