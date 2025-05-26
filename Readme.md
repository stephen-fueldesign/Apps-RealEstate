# Oncord Real Estate App - Technical Documentation

This repository contains a technical example of an Oncord platform app for managing real estate listings. The app demonstrates the standard Oncord component architecture and development patterns.

## Project Structure

The project follows the Oncord component architecture with the following structure:

```
Components/
└── RealEstate/
    ├── Listings/
    │   ├── _admin/           # Admin interface definitions
    │   │   ├── delete/       # Delete functionality
    │   │   ├── edit/         # Edit functionality
    │   │   ├── entity.html   # Admin UI listing template
    │   │   └── entity.json   # Admin configuration
    │   ├── _fields/          # Database structure
    │   │   └── realestate_listings.json  # Table definition
    │   └── Listings.inc.php  # Main component class
    └── _admin/               # Component admin section
        ├── entity.html       # Admin UI template
        └── entity.json       # Admin configuration
```

## Technical Implementation

### Component Architecture

- The app extends the `DataABC` framework class, which provides standard CRUD operations:
  - `save()` - Create and update records
  - `delete()` - Remove records
  - `install()` - Set up database tables and initial data
  - `uninstall()` - Clean up database tables and data

### Database Structure

- Database tables are defined in the `_fields` directory using JSON configuration
- The `realestate_listings.json` file defines:
  - Table name and structure
  - Field definitions (name, type, length, etc.)
  - Indexes and constraints
- This structure is automatically created during component installation

### Admin Interface

- Admin pages are defined within the `_admin` directory
- Each admin section contains:
  - `entity.html` - UI template using Oncord's templating system
  - `entity.json` - Configuration for the admin page
  - `entity.js` - Client-side JavaScript functionality

### UI Implementation

- UI components are defined in HTML templates using Oncord's form and layout tags
- The edit form (`_admin/edit/entity.html`) defines the interface for creating/editing listings
- Client-side validation and behavior is implemented in JavaScript (`_admin/edit/entity.js`)

### Component Class

The main component class (`Listings.inc.php`) implements:

- Database table configuration (table name, primary key, field prefix)
- Event handlers for integration with other components
- Custom field methods
- Business logic for saving and retrieving listings

## Development Pattern

1. Define database structure in `_fields/*.json`
2. Implement component class extending `DataABC`
3. Create admin UI templates in `_admin/` directories
4. Implement client-side functionality in JavaScript

## Installation

The component is automatically installed when added to an Oncord platform instance. The installation process:

1. Creates database tables based on the `_fields` definitions
2. Registers admin pages based on `_admin` configurations
3. Sets up event handlers defined in the component class

## Extension

- Custom fields can be added through the Oncord admin interface
- Additional functionality can be implemented by extending the component class
- UI customizations can be made by modifying the admin templates
