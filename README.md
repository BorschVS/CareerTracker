# 🚀 Career Tracker

<div align="center">

![Career Tracker](https://img.shields.io/badge/WordPress-Theme-blue?style=for-the-badge&logo=wordpress)
![License](https://img.shields.io/badge/License-Educational-green?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple?style=for-the-badge&logo=php)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-yellow?style=for-the-badge&logo=javascript)

**Beautiful Apple-style glassmorphism project tracking application**

*Personal chat-notebook-library for creating notes, tutorials, and documentation*

[🚀 Quick Start](#-quick-start) • [📖 Documentation](#-features) • [🎨 Screenshots](#-screenshots) • [🔧 Development](#-development)

</div>

## 🚀 Quick Start

### With Docker (Recommended)

```bash
# Clone the repository
git clone https://github.com/borschvs/CareerTracker.git
cd CareerTracker

# Start Docker containers
docker-compose up -d

# Open in browser
open http://localhost:8080
```

### Manual Installation

1. **Copy the theme into WordPress**:
   ```bash
   cp -r . /path/to/wordpress/wp-content/themes/career-tracker/
   ```

2. **Activate the theme** in the WordPress admin panel

3. **Done!** The application is ready to use

📖 **Detailed setup guide**: [local-setup.md](local-setup.md)

---

## 🎯 Features

### 🏠 **Homepage**
- Project grid with beautiful cards
- Quick creation of new projects
- Search and filtering (planned)

### 📄 **Project Page**
- Edit project information
- Add and manage sections
- Rich text editor for each section

### 🛠️ **Content Types**
- **Rich Text**: Formatted text with a toolbar
- **Subheadings**: Documentation structuring
- **Code Blocks**: Syntax highlighting for multiple languages
- **Images**: Upload, display, and download support

---

## 🎨 Screenshots

> *Screenshots will be added after testing*

---

## 🏗️ Architecture

### 📁 Project Structure

```text
career-tracker/
├── 🎨 style.css               # Glassmorphism design and styles
├── 🏠 index.php               # Homepage (project list)
├── 📄 single.php              # Single project page
├── 🧩 header.php & footer.php # Templates
├── ⚙️ functions.php           # AJAX handlers and functions
├── 📁 js/
│   ├── 🚀 app.js              # Main application
│   ├── 🗂️ project-manager.js  # Project management
│   └── ✏️ editor.js           # Rich text editor
├── 🐳 docker-compose.yml      # Docker configuration
├── 🚀 local-setup.md          # Installation guide
└── 📖 README.md               # Documentation
```

### 🗄️ Database

**`wp_career_sections` table**:
- `id` - Primary key
- `project_id` - Relation to project (`wp_posts`)
- `title` - Section title
- `content` - Content (JSON)
- `section_order` - Display order
- `created_at`, `updated_at` - Timestamps

### 🔌 API Endpoints

| Endpoint | Description |
|----------|-------------|
| `create_project` | Create a new project |
| `update_project` | Update a project |
| `load_project_sections` | Load project sections |
| `create_project_section` | Create a new section |
| `save_section_content` | Save section content |
| `upload_editor_image` | Upload images |

---

## 🛡️ Security

- ✅ **Nonce validation** for all AJAX requests
- ✅ **Input/output sanitization**
- ✅ **Permission checks** for every action
- ✅ **Forced authentication** for site access
- ✅ **File validation** for image uploads
- ✅ **XSS protection** in the rich text editor

---

## 🔧 Development

### 🛠️ Tech Stack

- **Backend**: WordPress, PHP 7.4+, MySQL
- **Frontend**: jQuery, Vanilla JavaScript, CSS3
- **DevOps**: Docker, Docker Compose
- **Tools**: Git, GitHub

---

## 📝 License

This project was created for **educational purposes**.