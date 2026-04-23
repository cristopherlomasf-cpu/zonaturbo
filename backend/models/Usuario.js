const db = require('../config/db');
const bcrypt = require('bcryptjs');

class Usuario {
  static async findByEmail(email) {
    const [rows] = await db.execute('SELECT * FROM usuarios WHERE email = ?', [email]);
    return rows[0] || null;
  }

  static async findById(id) {
    const [rows] = await db.execute('SELECT id, nombre, email, telefono, rol, created_at FROM usuarios WHERE id = ?', [id]);
    return rows[0] || null;
  }

  static async create({ nombre, email, password, telefono, rol = 'cliente' }) {
    const hash = await bcrypt.hash(password, 10);
    const [result] = await db.execute(
      'INSERT INTO usuarios (nombre, email, password, telefono, rol) VALUES (?, ?, ?, ?, ?)',
      [nombre, email, hash, telefono || null, rol]
    );
    return result.insertId;
  }

  static async comparePassword(plain, hash) {
    return bcrypt.compare(plain, hash);
  }

  static async getAll() {
    const [rows] = await db.execute('SELECT id, nombre, email, telefono, rol, created_at FROM usuarios ORDER BY created_at DESC');
    return rows;
  }
}

module.exports = Usuario;
