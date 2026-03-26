

# Sistema Comunidad de Emprendedores

**Corporación de Fomento La Granja**

---

## Descripción General

El **Sistema Comunidad de Emprendedores** es una plataforma web desarrollada en PHP y MySQL para la gestión integral de personas, emprendedores y actividades comunitarias.

Permite centralizar información administrativa, financiera y operativa en un solo sistema digital, facilitando el control, seguimiento y toma de decisiones.

---

## Objetivo del Sistema

El sistema permite:

* Registrar personas y emprendedores.
* Administrar contratos y créditos.
* Registrar pagos (cobranzas).
* Gestionar talleres e inscripciones.
* Controlar carritos o puestos.
* Subir y gestionar documentos.
* Generar listados exportables a PDF.
* Consultar historial completo por persona.
* Realizar auditoría de acciones del sistema.
* Configurar tema (modo claro/oscuro) y color institucional.

---

## Módulos Principales

### Personas

* Registro, edición y eliminación.
* Búsqueda avanzada.
* Historial completo por persona.

### Emprendedores

* Asociación a personas.
* Información de negocio y rubro.
* Límite de crédito.

### Finanzas

* Contratos
* Créditos
* Cobranzas (pagos registrados)
* Estado activo, vencido, cancelado.

### Actividades

* Talleres
* Inscripciones
* Jornadas

### Carritos

* Registro de responsables
* Control de asistencia
* Equipamiento

### 🗂 Documentos

* Subida de archivos
* Asociación a emprendedores
* Registro de tamaño y tipo

### 📊 Auditoría

* Registro de acciones del sistema
* Usuario
* Fecha
* Tabla afectada

### ⚙ Configuración

* Modo claro / oscuro
* Personalización de color
* Idioma
* Ajustes visuales

---

## 🔎 Barra de Búsqueda Global

La barra superior permite buscar en:

* Personas
* Carritos
* Tarjetas de presentación

Al seleccionar un resultado:

* Se redirige al detalle completo
* Se muestra el historial relacionado

---

## 🖨 Exportación y Listados

Todos los módulos con tabla incluyen:

* Exportación a PDF
* Impresión
* Búsqueda avanzada (DataTables)
* Paginación automática

---

##Tecnologías Utilizadas

* PHP 8+
* MySQL
* PDO (conexión segura)
* Bootstrap 5
* DataTables (Export PDF / Print)
* CSS personalizado dinámico
* JavaScript moderno (Fetch API)

---

## Estructura de Base de Datos

Base de datos:

```
comunidad_de_emprendedores
```

Tablas principales:

* personas
* emprendedores
* contratos
* creditos
* cobranzas
* talleres
* inscripciones_talleres
* jornadas
* carritos
* documentos
* usuarios
* auditoria
* configuraciones

Todas las relaciones usan:

* Claves foráneas
* Índices optimizados
* Motor InnoDB
* Charset utf8mb4

---

## Instalación

### Requisitos

* XAMPP o servidor Apache
* PHP 8 o superior
* MySQL

### 2️Pasos

1. Copiar carpeta a:

```
C:\xampp\htdocs\comunidad_de_emprendedores
```

2. Crear base de datos en phpMyAdmin:

```
comunidad_de_emprendedores
```

3. Importar el archivo SQL incluido.

4. Configurar conexión en:

```
includes/database.php
```

5. Abrir en navegador:

```
http://localhost/comunidad_de_emprendedores/
```

---

## Seguridad

El sistema incluye:

* Sesiones seguras
* Protección CSRF
* Sanitización de datos
* Consultas preparadas PDO
* Control de roles
* Auditoría de acciones

---

## Tema Visual

El sistema utiliza:

* Tema oscuro por defecto
* Soporte modo claro
* Fondo animado
* Personalización RGB institucional
* Diseño adaptable (responsive)

---

## Autor

Desarrollado como sistema de gestión comunitaria para la Corporación de Fomento La Granja.

---

## Licencia

Uso interno institucional.

---

---

Si quieres, puedo hacer también:

* Manual de Usuario (separado)
* Manual Técnico con ERD
* Documento académico formal
* README estilo GitHub profesional con badges
* Documento Word listo para imprimir

