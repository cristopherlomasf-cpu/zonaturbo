# 🏎️ Zona Turbo — Sistema de Gestión para Taller Automotriz

> Sistema web cliente-servidor para administrar clientes, vehículos, órdenes de trabajo, inventario y costos de un taller mecánico.

---

## 🎯 Objetivo

Desarrollar un sistema web que permita:

- Gestionar **clientes y vehículos**
- Controlar **órdenes de trabajo (OT)**
- Manejar **inventario de repuestos**
- Calcular **costos y generar reportes**
- Permitir al cliente **consultar el estado de su vehículo**

---

## 👥 Roles del Sistema

| Rol | Permisos |
|---|---|
| 🛡️ **Administrador** | Acceso total, reportes de ingresos, gestión de usuarios |
| 🔧 **Mecánico** | Registrar clientes/vehículos, abrir OTs, diagnóstico, repuestos |
| 👤 **Cliente** | Consultar estado del vehículo por placa (sin precios) |

---

## ⚙️ Módulos del Sistema

### 🔹 Gestión Básica
- Registro de clientes (con WhatsApp validado)
- Registro de vehículos (**placa única** como identificador principal)
- Búsqueda de historial por placa

### 🔹 Órdenes de Trabajo (OT)
- Crear y gestionar OTs
- Asignar mecánico responsable
- Registrar diagnóstico
- Seguimiento de estado (pendiente / en proceso / listo)

### 🔹 Inventario
- Añadir repuestos a cada OT
- Descuento automático de stock

### 🔹 Finanzas
- Cálculo de costo total: `repuestos + mano de obra`
- Generación de reportes de ingresos

### 🔹 Portal del Cliente
- Consultar estado del vehículo por placa
- Ver progreso de la reparación (sin precios)

---

## 🧠 Reglas de Negocio

- La **placa** es el identificador principal del vehículo
- No se permiten duplicados de vehículos
- Todo el flujo gira alrededor de la **Orden de Trabajo (OT)**
- Sistema completamente **web** (cliente-servidor)

---

## ⚡ Requisitos No Funcionales

| Requisito | Valor |
|---|---|
| Tiempo de respuesta | ≤ 2 segundos |
| Seguridad | Contraseñas encriptadas + control de roles |
| Backups | Automáticos cada 24 horas |
| Disponibilidad | 99.5% |
| Diseño | Responsivo (móvil y PC) |

---

## 🗂️ Estructura del Proyecto

```
zonaturbo/
├── backend/               # Lógica del servidor
│   ├── controllers/       # Controladores por módulo
│   ├── models/            # Modelos de datos
│   ├── routes/            # Rutas de la API
│   ├── middleware/        # Autenticación y roles
│   └── config/            # BD y variables de entorno
├── frontend/              # Interfaz de usuario
│   ├── pages/             # Vistas por módulo
│   ├── components/        # Componentes reutilizables
│   └── assets/            # Estilos e imágenes
├── database/
│   └── schema.sql         # Esquema de la base de datos
├── docs/                  # Documentación del proyecto
└── README.md
```

---

## 🛠️ Tecnologías Sugeridas

| Capa | Tecnología |
|---|---|
| Frontend | HTML, CSS, JavaScript (o framework como Vue/React) |
| Backend | Node.js / PHP / Python (Flask o Django) |
| Base de datos | MySQL / PostgreSQL |
| Autenticación | JWT + bcrypt |
| Despliegue | VPS / Railway / Render |

---

## 👨‍💻 Autor

**Cristopher Lomas**  
📍 Tulcán, Carchi, Ecuador  
🏷️ Universidad Politécnica Estatal del Carchi (UPEC)  
🔗 [GitHub](https://github.com/cristopherlomasf-cpu)

---

> _Zona Turbo — Control total sobre tu taller_ 🏎️⚡
