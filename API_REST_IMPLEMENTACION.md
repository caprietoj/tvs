# 🔌 API REST Y EJEMPLOS DE IMPLEMENTACIÓN

## 📡 ENDPOINTS DE API

### Diseño de API RESTful para el Módulo de Compras

```
┌─────────────────────────────────────────────────────────────────┐
│                    ESTRUCTURA DE API REST                        │
└─────────────────────────────────────────────────────────────────┘

Base URL: https://api.tvs.edu.co/v1
Authentication: Bearer Token (JWT)
Content-Type: application/json
```

### 1. Solicitudes de Compra (Purchase Requests)

```http
# Listar todas las solicitudes
GET /api/v1/purchase-requests
Parameters:
  - page: número de página (default: 1)
  - per_page: items por página (default: 20)
  - type: filtro por tipo (purchase|services|materials)
  - status: filtro por estado
  - section: filtro por sección
  - date_from: fecha desde (YYYY-MM-DD)
  - date_to: fecha hasta (YYYY-MM-DD)

Response 200:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "request_number": "SC-0001",
      "type": "purchase",
      "status": "pending",
      "requester": "Juan Pérez",
      "section_area": "Primaria",
      "budget": 5000000,
      "created_at": "2025-10-18T10:30:00Z",
      "items_count": 5,
      "quotations_count": 0
    }
  ],
  "meta": {
    "current_page": 1,
    "total_pages": 10,
    "total_items": 200,
    "per_page": 20
  }
}

# Crear nueva solicitud
POST /api/v1/purchase-requests
Body:
{
  "type": "purchase",
  "requester": "Juan Pérez",
  "section_area": "Primaria",
  "budget": 5000000,
  "delivery_date": "2025-11-15",
  "purchase_items": [
    {
      "description": "Laptop Dell",
      "quantity": 5,
      "unit_price": 1500000
    }
  ],
  "purchase_justification": "Equipos para sala de informática",
  "attached_files": ["base64_encoded_file"]
}

Response 201:
{
  "success": true,
  "message": "Solicitud creada exitosamente",
  "data": {
    "id": 123,
    "request_number": "SC-0123",
    "status": "pending",
    "created_at": "2025-10-23T15:45:00Z"
  }
}

# Ver detalle de solicitud
GET /api/v1/purchase-requests/{id}

Response 200:
{
  "success": true,
  "data": {
    "id": 1,
    "request_number": "SC-0001",
    "type": "purchase",
    "status": "pending",
    "requester": "Juan Pérez",
    "section_area": "Primaria",
    "budget": 5000000,
    "delivery_date": "2025-11-15",
    "purchase_items": [
      {
        "description": "Laptop Dell",
        "quantity": 5,
        "unit_price": 1500000,
        "total": 7500000
      }
    ],
    "purchase_justification": "Equipos para sala de informática",
    "quotations": [],
    "history": [
      {
        "action": "Solicitud creada",
        "user": "Juan Pérez",
        "timestamp": "2025-10-18T10:30:00Z"
      }
    ]
  }
}

# Aprobar solicitud
POST /api/v1/purchase-requests/{id}/approve
Body:
{
  "comments": "Aprobado según presupuesto disponible"
}

Response 200:
{
  "success": true,
  "message": "Solicitud aprobada exitosamente",
  "data": {
    "id": 1,
    "status": "approved",
    "approved_by": "Admin User",
    "approval_date": "2025-10-23T16:00:00Z"
  }
}

# Rechazar solicitud
POST /api/v1/purchase-requests/{id}/reject
Body:
{
  "reason": "Presupuesto insuficiente"
}

Response 200:
{
  "success": true,
  "message": "Solicitud rechazada",
  "data": {
    "id": 1,
    "status": "rejected",
    "rejection_reason": "Presupuesto insuficiente"
  }
}
```

### 2. Cotizaciones (Quotations)

```http
# Listar cotizaciones de una solicitud
GET /api/v1/purchase-requests/{request_id}/quotations

Response 200:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "quotation_number": "COT-0001",
      "provider_name": "Proveedor ABC",
      "provider_nit": "900123456-7",
      "subtotal": 7000000,
      "tax_amount": 1330000,
      "total_amount": 8330000,
      "items": [
        {
          "description": "Laptop Dell",
          "quantity": 5,
          "unit_price": 1400000,
          "total": 7000000
        }
      ],
      "file_url": "https://storage.tvs.edu.co/quotations/COT-0001.pdf"
    }
  ]
}

# Agregar cotización
POST /api/v1/quotations
Body (multipart/form-data):
{
  "purchase_request_id": 1,
  "provider_name": "Proveedor ABC",
  "provider_nit": "900123456-7",
  "provider_email": "ventas@proveedorabc.com",
  "subtotal": 7000000,
  "tax_percentage": 19,
  "tax_amount": 1330000,
  "total_amount": 8330000,
  "items": [
    {
      "description": "Laptop Dell",
      "quantity": 5,
      "unit_price": 1400000
    }
  ],
  "file": "PDF file"
}

Response 201:
{
  "success": true,
  "message": "Cotización agregada exitosamente",
  "data": {
    "id": 1,
    "quotation_number": "COT-0001"
  }
}

# Seleccionar cotización ganadora
POST /api/v1/quotations/{id}/select
Body:
{
  "comments": "Mejor precio y condiciones de pago"
}

Response 200:
{
  "success": true,
  "message": "Cotización seleccionada",
  "data": {
    "purchase_request_id": 1,
    "selected_quotation_id": 1,
    "status": "En pre-aprobación"
  }
}
```

### 3. Órdenes de Compra (Purchase Orders)

```http
# Listar órdenes de compra
GET /api/v1/purchase-orders
Parameters:
  - page, per_page
  - status: filtro por estado
  - provider: filtro por proveedor
  - date_from, date_to

Response 200:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "order_number": "OC-0001",
      "purchase_request": {
        "id": 1,
        "request_number": "SC-0001"
      },
      "provider": {
        "id": 1,
        "name": "Proveedor ABC",
        "nit": "900123456-7"
      },
      "total_amount": 8330000,
      "status": "pending",
      "created_at": "2025-10-23T17:00:00Z",
      "pdf_url": "https://storage.tvs.edu.co/orders/OC-0001.pdf"
    }
  ]
}

# Ver detalle de orden
GET /api/v1/purchase-orders/{id}

Response 200:
{
  "success": true,
  "data": {
    "id": 1,
    "order_number": "OC-0001",
    "purchase_request_id": 1,
    "provider": {
      "id": 1,
      "name": "Proveedor ABC",
      "nit": "900123456-7",
      "address": "Calle 123 #45-67",
      "phone": "601 234 5678",
      "email": "ventas@proveedorabc.com"
    },
    "items": [
      {
        "description": "Laptop Dell",
        "quantity": 5,
        "unit_price": 1400000,
        "subtotal": 7000000,
        "tax_amount": 1330000,
        "total": 8330000
      }
    ],
    "subtotal": 7000000,
    "tax_amount": 1330000,
    "total_amount": 8330000,
    "status": "pending",
    "payment_terms": "30 días",
    "delivery_date": "2025-11-15",
    "pdf_url": "https://storage.tvs.edu.co/orders/OC-0001.pdf"
  }
}

# Aprobar orden
POST /api/v1/purchase-orders/{id}/approve

Response 200:
{
  "success": true,
  "message": "Orden aprobada",
  "data": {
    "id": 1,
    "status": "approved",
    "approved_at": "2025-10-23T18:00:00Z"
  }
}

# Registrar pago
POST /api/v1/purchase-orders/{id}/payment
Body:
{
  "payment_method": "Transferencia bancaria",
  "payment_reference": "TRX-12345",
  "payment_date": "2025-10-25",
  "payment_file": "base64_encoded_receipt"
}

Response 200:
{
  "success": true,
  "message": "Pago registrado exitosamente"
}
```

### 4. Proveedores (Providers)

```http
# Listar proveedores
GET /api/v1/providers
Parameters:
  - search: búsqueda por nombre o NIT
  - active: filtro por activos (true|false)

Response 200:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nit": "900123456-7",
      "nombre": "Proveedor ABC SAS",
      "direccion": "Calle 123 #45-67",
      "telefono": "601 234 5678",
      "email": "ventas@proveedorabc.com",
      "persona_contacto": "María González",
      "activo": true
    }
  ]
}

# Crear proveedor
POST /api/v1/providers
Body:
{
  "nit": "900123456-7",
  "nombre": "Proveedor ABC SAS",
  "direccion": "Calle 123 #45-67",
  "telefono": "601 234 5678",
  "email": "ventas@proveedorabc.com",
  "persona_contacto": "María González"
}

Response 201:
{
  "success": true,
  "message": "Proveedor creado exitosamente",
  "data": {
    "id": 1,
    "nit": "900123456-7",
    "nombre": "Proveedor ABC SAS"
  }
}
```

---

## 💻 EJEMPLOS DE IMPLEMENTACIÓN EN OTROS LENGUAJES

### 1. Python (Flask + SQLAlchemy)

```python
# models.py - Definición de modelos

from flask_sqlalchemy import SQLAlchemy
from datetime import datetime
import json

db = SQLAlchemy()

class PurchaseRequest(db.Model):
    __tablename__ = 'purchase_requests'
    
    id = db.Column(db.Integer, primary_key=True)
    request_number = db.Column(db.String(50), unique=True, nullable=False)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id'), nullable=False)
    type = db.Column(db.Enum('purchase', 'services', 'materials'), nullable=False)
    status = db.Column(db.String(50), default='pending')
    requester = db.Column(db.String(255), nullable=False)
    section_area = db.Column(db.String(255), nullable=False)
    budget = db.Column(db.Numeric(15, 2))
    delivery_date = db.Column(db.Date)
    
    # JSON fields
    purchase_items = db.Column(db.Text)  # Store as JSON string
    purchase_justification = db.Column(db.Text)
    
    # Timestamps
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    # Relationships
    user = db.relationship('User', backref='purchase_requests')
    quotations = db.relationship('Quotation', backref='purchase_request', cascade='all, delete-orphan')
    orders = db.relationship('PurchaseOrder', backref='purchase_request', cascade='all, delete-orphan')
    
    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        if not self.request_number:
            self.request_number = self.generate_request_number()
    
    def generate_request_number(self):
        """Genera número de solicitud único"""
        prefix = {
            'purchase': 'SC',
            'services': 'SS',
            'materials': 'SM'
        }.get(self.type, 'SM')
        
        # Obtener último número
        last_request = PurchaseRequest.query.filter(
            PurchaseRequest.type == self.type,
            PurchaseRequest.request_number.like(f'{prefix}-%')
        ).order_by(PurchaseRequest.request_number.desc()).first()
        
        if last_request:
            last_number = int(last_request.request_number.split('-')[1])
            next_number = last_number + 1
        else:
            next_number = 1
        
        return f'{prefix}-{str(next_number).zfill(4)}'
    
    def get_items(self):
        """Obtiene los items como lista"""
        if self.purchase_items:
            return json.loads(self.purchase_items)
        return []
    
    def set_items(self, items):
        """Establece los items desde una lista"""
        self.purchase_items = json.dumps(items)
    
    def to_dict(self):
        """Convierte el modelo a diccionario"""
        return {
            'id': self.id,
            'request_number': self.request_number,
            'type': self.type,
            'status': self.status,
            'requester': self.requester,
            'section_area': self.section_area,
            'budget': float(self.budget) if self.budget else 0,
            'delivery_date': self.delivery_date.isoformat() if self.delivery_date else None,
            'items': self.get_items(),
            'justification': self.purchase_justification,
            'created_at': self.created_at.isoformat(),
            'quotations_count': len(self.quotations),
            'orders_count': len(self.orders)
        }


class Quotation(db.Model):
    __tablename__ = 'quotations'
    
    id = db.Column(db.Integer, primary_key=True)
    quotation_number = db.Column(db.String(50), unique=True, nullable=False)
    purchase_request_id = db.Column(db.Integer, db.ForeignKey('purchase_requests.id'), nullable=False)
    provider_name = db.Column(db.String(255), nullable=False)
    provider_nit = db.Column(db.String(50))
    subtotal = db.Column(db.Numeric(15, 2))
    tax_amount = db.Column(db.Numeric(15, 2))
    total_amount = db.Column(db.Numeric(15, 2))
    items = db.Column(db.Text)  # JSON
    file_path = db.Column(db.String(500))
    status = db.Column(db.String(50), default='active')
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    
    def __init__(self, **kwargs):
        super().__init__(**kwargs)
        if not self.quotation_number:
            self.quotation_number = self.generate_quotation_number()
    
    def generate_quotation_number(self):
        last_quotation = Quotation.query.order_by(Quotation.quotation_number.desc()).first()
        if last_quotation:
            last_number = int(last_quotation.quotation_number.split('-')[1])
            next_number = last_number + 1
        else:
            next_number = 1
        return f'COT-{str(next_number).zfill(4)}'
    
    def to_dict(self):
        return {
            'id': self.id,
            'quotation_number': self.quotation_number,
            'provider_name': self.provider_name,
            'provider_nit': self.provider_nit,
            'subtotal': float(self.subtotal) if self.subtotal else 0,
            'tax_amount': float(self.tax_amount) if self.tax_amount else 0,
            'total_amount': float(self.total_amount) if self.total_amount else 0,
            'items': json.loads(self.items) if self.items else [],
            'file_url': f'/storage/quotations/{self.file_path}' if self.file_path else None,
            'status': self.status,
            'created_at': self.created_at.isoformat()
        }


# routes.py - Endpoints de la API

from flask import Blueprint, request, jsonify
from models import db, PurchaseRequest, Quotation
from auth import token_required  # Middleware de autenticación

api = Blueprint('api', __name__, url_prefix='/api/v1')

@api.route('/purchase-requests', methods=['GET'])
@token_required
def get_purchase_requests(current_user):
    """Listar solicitudes de compra"""
    page = request.args.get('page', 1, type=int)
    per_page = request.args.get('per_page', 20, type=int)
    type_filter = request.args.get('type')
    status_filter = request.args.get('status')
    
    query = PurchaseRequest.query
    
    # Aplicar filtros
    if type_filter:
        query = query.filter_by(type=type_filter)
    if status_filter:
        query = query.filter_by(status=status_filter)
    
    # Si no es admin, solo sus solicitudes
    if not current_user.is_admin():
        query = query.filter_by(user_id=current_user.id)
    
    # Paginación
    pagination = query.paginate(page=page, per_page=per_page, error_out=False)
    
    return jsonify({
        'success': True,
        'data': [item.to_dict() for item in pagination.items],
        'meta': {
            'current_page': pagination.page,
            'total_pages': pagination.pages,
            'total_items': pagination.total,
            'per_page': per_page
        }
    })


@api.route('/purchase-requests', methods=['POST'])
@token_required
def create_purchase_request(current_user):
    """Crear nueva solicitud"""
    data = request.get_json()
    
    # Validar datos
    required_fields = ['type', 'requester', 'section_area', 'budget', 'delivery_date']
    for field in required_fields:
        if field not in data:
            return jsonify({
                'success': False,
                'message': f'Campo requerido: {field}'
            }), 400
    
    # Crear solicitud
    purchase_request = PurchaseRequest(
        user_id=current_user.id,
        type=data['type'],
        requester=data['requester'],
        section_area=data['section_area'],
        budget=data['budget'],
        delivery_date=datetime.strptime(data['delivery_date'], '%Y-%m-%d').date(),
        purchase_justification=data.get('purchase_justification')
    )
    
    # Agregar items
    if 'purchase_items' in data:
        purchase_request.set_items(data['purchase_items'])
    
    try:
        db.session.add(purchase_request)
        db.session.commit()
        
        # Enviar notificación por email (async)
        send_notification_email.delay(purchase_request.id)
        
        return jsonify({
            'success': True,
            'message': 'Solicitud creada exitosamente',
            'data': purchase_request.to_dict()
        }), 201
    
    except Exception as e:
        db.session.rollback()
        return jsonify({
            'success': False,
            'message': f'Error al crear solicitud: {str(e)}'
        }), 500


@api.route('/purchase-requests/<int:id>', methods=['GET'])
@token_required
def get_purchase_request(current_user, id):
    """Ver detalle de solicitud"""
    purchase_request = PurchaseRequest.query.get_or_404(id)
    
    # Verificar permisos
    if not current_user.is_admin() and purchase_request.user_id != current_user.id:
        return jsonify({
            'success': False,
            'message': 'No tiene permisos para ver esta solicitud'
        }), 403
    
    return jsonify({
        'success': True,
        'data': purchase_request.to_dict()
    })


@api.route('/purchase-requests/<int:id>/approve', methods=['POST'])
@token_required
def approve_purchase_request(current_user, id):
    """Aprobar solicitud"""
    if not current_user.is_admin():
        return jsonify({
            'success': False,
            'message': 'Solo administradores pueden aprobar'
        }), 403
    
    purchase_request = PurchaseRequest.query.get_or_404(id)
    
    purchase_request.status = 'approved'
    purchase_request.approved_by = current_user.id
    purchase_request.approval_date = datetime.utcnow()
    
    try:
        db.session.commit()
        
        # Crear orden de compra automáticamente
        create_purchase_order.delay(purchase_request.id)
        
        return jsonify({
            'success': True,
            'message': 'Solicitud aprobada exitosamente',
            'data': purchase_request.to_dict()
        })
    
    except Exception as e:
        db.session.rollback()
        return jsonify({
            'success': False,
            'message': f'Error al aprobar: {str(e)}'
        }), 500


# services.py - Lógica de negocio

from celery import Celery
from models import db, PurchaseRequest, PurchaseOrder
from email_utils import send_email
from pdf_generator import generate_order_pdf

celery = Celery('tasks', broker='redis://localhost:6379/0')

@celery.task
def send_notification_email(purchase_request_id):
    """Enviar email de notificación (tarea asíncrona)"""
    purchase_request = PurchaseRequest.query.get(purchase_request_id)
    
    if not purchase_request:
        return
    
    # Obtener email de compras
    compras_email = 'compras@tvs.edu.co'
    
    send_email(
        to=compras_email,
        subject=f'Nueva Solicitud - {purchase_request.request_number}',
        template='new_purchase_request',
        context={
            'purchase_request': purchase_request.to_dict()
        }
    )


@celery.task
def create_purchase_order(purchase_request_id):
    """Crear orden de compra automáticamente"""
    purchase_request = PurchaseRequest.query.get(purchase_request_id)
    
    if not purchase_request or not purchase_request.selected_quotation:
        return
    
    quotation = purchase_request.selected_quotation
    
    # Crear orden
    order = PurchaseOrder(
        purchase_request_id=purchase_request_id,
        provider_id=quotation.provider_id,
        subtotal=quotation.subtotal,
        tax_amount=quotation.tax_amount,
        total_amount=quotation.total_amount,
        items=quotation.items
    )
    
    db.session.add(order)
    db.session.commit()
    
    # Generar PDF
    pdf_path = generate_order_pdf(order.id)
    order.file_path = pdf_path
    db.session.commit()
    
    return order.id
```

### 2. Node.js (Express + Sequelize)

```javascript
// models/PurchaseRequest.js

const { DataTypes, Model } = require('sequelize');
const sequelize = require('../config/database');

class PurchaseRequest extends Model {
  // Generar número de solicitud
  static async generateRequestNumber(type) {
    const prefix = {
      'purchase': 'SC',
      'services': 'SS',
      'materials': 'SM'
    }[type] || 'SM';
    
    const lastRequest = await this.findOne({
      where: {
        type: type,
        request_number: {
          [sequelize.Op.like]: `${prefix}-%`
        }
      },
      order: [['request_number', 'DESC']]
    });
    
    let nextNumber = 1;
    if (lastRequest) {
      const lastNumber = parseInt(lastRequest.request_number.split('-')[1]);
      nextNumber = lastNumber + 1;
    }
    
    return `${prefix}-${String(nextNumber).padStart(4, '0')}`;
  }
  
  // Método para convertir a JSON
  toJSON() {
    const values = Object.assign({}, this.get());
    
    // Parsear JSON fields
    if (values.purchase_items) {
      values.purchase_items = JSON.parse(values.purchase_items);
    }
    
    return values;
  }
}

PurchaseRequest.init({
  id: {
    type: DataTypes.INTEGER,
    primaryKey: true,
    autoIncrement: true
  },
  request_number: {
    type: DataTypes.STRING(50),
    unique: true,
    allowNull: false
  },
  user_id: {
    type: DataTypes.INTEGER,
    allowNull: false,
    references: {
      model: 'users',
      key: 'id'
    }
  },
  type: {
    type: DataTypes.ENUM('purchase', 'services', 'materials'),
    allowNull: false
  },
  status: {
    type: DataTypes.STRING(50),
    defaultValue: 'pending'
  },
  requester: {
    type: DataTypes.STRING(255),
    allowNull: false
  },
  section_area: {
    type: DataTypes.STRING(255),
    allowNull: false
  },
  budget: {
    type: DataTypes.DECIMAL(15, 2)
  },
  delivery_date: {
    type: DataTypes.DATE
  },
  purchase_items: {
    type: DataTypes.TEXT,
    get() {
      const rawValue = this.getDataValue('purchase_items');
      return rawValue ? JSON.parse(rawValue) : [];
    },
    set(value) {
      this.setDataValue('purchase_items', JSON.stringify(value));
    }
  },
  purchase_justification: {
    type: DataTypes.TEXT
  },
  approved_by: {
    type: DataTypes.INTEGER,
    references: {
      model: 'users',
      key: 'id'
    }
  },
  approval_date: {
    type: DataTypes.DATE
  }
}, {
  sequelize,
  modelName: 'PurchaseRequest',
  tableName: 'purchase_requests',
  timestamps: true,
  underscored: true
});

module.exports = PurchaseRequest;


// routes/purchaseRequests.js

const express = require('express');
const router = express.Router();
const { PurchaseRequest, Quotation, User } = require('../models');
const { authenticate, authorize } = require('../middleware/auth');
const { validateRequest } = require('../middleware/validation');

// Listar solicitudes
router.get('/', authenticate, async (req, res) => {
  try {
    const { page = 1, per_page = 20, type, status, section } = req.query;
    const offset = (page - 1) * per_page;
    
    // Construir query
    const where = {};
    if (type) where.type = type;
    if (status) where.status = status;
    if (section) where.section_area = section;
    
    // Si no es admin, solo sus solicitudes
    if (req.user.role !== 'admin') {
      where.user_id = req.user.id;
    }
    
    const { count, rows } = await PurchaseRequest.findAndCountAll({
      where,
      limit: parseInt(per_page),
      offset,
      include: [
        { model: User, as: 'user', attributes: ['id', 'name', 'email'] },
        { model: Quotation, as: 'quotations' }
      ],
      order: [['created_at', 'DESC']]
    });
    
    res.json({
      success: true,
      data: rows,
      meta: {
        current_page: parseInt(page),
        total_pages: Math.ceil(count / per_page),
        total_items: count,
        per_page: parseInt(per_page)
      }
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      message: error.message
    });
  }
});

// Crear solicitud
router.post('/', authenticate, validateRequest('createPurchaseRequest'), async (req, res) => {
  try {
    const {
      type,
      requester,
      section_area,
      budget,
      delivery_date,
      purchase_items,
      purchase_justification
    } = req.body;
    
    // Generar número de solicitud
    const request_number = await PurchaseRequest.generateRequestNumber(type);
    
    // Crear solicitud
    const purchaseRequest = await PurchaseRequest.create({
      request_number,
      user_id: req.user.id,
      type,
      requester,
      section_area,
      budget,
      delivery_date,
      purchase_items,
      purchase_justification
    });
    
    // Enviar notificación (async)
    sendNotificationEmail(purchaseRequest.id);
    
    res.status(201).json({
      success: true,
      message: 'Solicitud creada exitosamente',
      data: purchaseRequest
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      message: error.message
    });
  }
});

// Ver detalle
router.get('/:id', authenticate, async (req, res) => {
  try {
    const purchaseRequest = await PurchaseRequest.findByPk(req.params.id, {
      include: [
        { model: User, as: 'user' },
        { model: Quotation, as: 'quotations' },
        { model: PurchaseOrder, as: 'orders' }
      ]
    });
    
    if (!purchaseRequest) {
      return res.status(404).json({
        success: false,
        message: 'Solicitud no encontrada'
      });
    }
    
    // Verificar permisos
    if (req.user.role !== 'admin' && purchaseRequest.user_id !== req.user.id) {
      return res.status(403).json({
        success: false,
        message: 'No tiene permisos para ver esta solicitud'
      });
    }
    
    res.json({
      success: true,
      data: purchaseRequest
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      message: error.message
    });
  }
});

// Aprobar solicitud
router.post('/:id/approve', authenticate, authorize('admin'), async (req, res) => {
  try {
    const purchaseRequest = await PurchaseRequest.findByPk(req.params.id);
    
    if (!purchaseRequest) {
      return res.status(404).json({
        success: false,
        message: 'Solicitud no encontrada'
      });
    }
    
    // Actualizar estado
    await purchaseRequest.update({
      status: 'approved',
      approved_by: req.user.id,
      approval_date: new Date()
    });
    
    // Crear orden de compra (async)
    createPurchaseOrder(purchaseRequest.id);
    
    res.json({
      success: true,
      message: 'Solicitud aprobada exitosamente',
      data: purchaseRequest
    });
  } catch (error) {
    res.status(500).json({
      success: false,
      message: error.message
    });
  }
});

module.exports = router;


// services/emailService.js

const nodemailer = require('nodemailer');
const handlebars = require('handlebars');
const fs = require('fs').promises;
const path = require('path');

class EmailService {
  constructor() {
    this.transporter = nodemailer.createTransport({
      host: process.env.MAIL_HOST,
      port: process.env.MAIL_PORT,
      secure: true,
      auth: {
        user: process.env.MAIL_USERNAME,
        pass: process.env.MAIL_PASSWORD
      }
    });
  }
  
  async sendEmail(to, subject, template, context) {
    try {
      // Cargar plantilla
      const templatePath = path.join(__dirname, '../views/emails', `${template}.hbs`);
      const templateSource = await fs.readFile(templatePath, 'utf8');
      const compiledTemplate = handlebars.compile(templateSource);
      const html = compiledTemplate(context);
      
      // Enviar email
      const info = await this.transporter.sendMail({
        from: '"TVS Compras" <noreply@tvs.edu.co>',
        to,
        subject,
        html
      });
      
      console.log('Email enviado:', info.messageId);
      return info;
    } catch (error) {
      console.error('Error enviando email:', error);
      throw error;
    }
  }
  
  async sendNewRequestNotification(purchaseRequestId) {
    const purchaseRequest = await PurchaseRequest.findByPk(purchaseRequestId);
    
    await this.sendEmail(
      'compras@tvs.edu.co',
      `Nueva Solicitud - ${purchaseRequest.request_number}`,
      'new_purchase_request',
      { purchaseRequest }
    );
  }
}

module.exports = new EmailService();
```

---

**Documento continúa en la siguiente parte con ejemplos en Java Spring Boot, C# .NET y consideraciones de deployment...**

