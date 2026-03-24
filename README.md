# 🚀 Facil Framework

![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%208.1-777BB4?logo=php)
![License](https://img.shields.io/badge/License-MIT-blue.svg)

**Facil** is a dev-friendly, insanely fast **file-based routing** PHP framework designed for rapid API and Fullstack development. 

Forget heavy boilerplates and complex configurations. Drop a file in the `routes/` folder, and your endpoint is ready. Built for developers and indie hackers who want to ship micro-SaaS projects fast without sacrificing a solid architecture.

## ✨ Features

* 📁 **File-Based Routing:** Next.js inspired routing. Your file structure dictates your API endpoints.
* 🗄️ **MicroORM & Database:** Elegant Query Builder and raw PDO wrapper (inspired by `better-sqlite3`).
* 🛠️ **Schema Builder:** Programmatic migrations out-of-the-box.
* 🛡️ **Built-in Security:** Automatic CSRF protection, CORS handling, and strict security headers.
* 🔐 **Authentication:** Simple, session-based Auth manager ready to go.
* ✅ **Validation:** Powerful payload validation including native support for Brazilian CPF/CNPJ.
* 🌍 **Environment Management:** Native `.env` loader.
* 🎨 **Views:** Easy HTML rendering with variable extraction.

## 📦 Installation

The easiest way to start a new Facil project is via Composer:

```bash
composer create-project rodrigocborges/facilphp my-app
cd my-app
```

## 🚀 Quick Start

Start the built-in development server:

```bash
composer run start-dev
```
*(For production or network testing, use `composer run start-prod`)*

### Creating your first Route
Create a file at `routes/api/users.php`. It automatically maps to `http://localhost:8000/api/users`.

```php
<?php

use Facil\Http\Response;
use Facil\Database\Query;

return [
    'GET' => function() {
        // Automatically paginated database response!
        $users = Query::table('users')->where('is_active', 1)->paginate(1, 15);
        
        return Response::json($users);
    }
];
```

### Dynamic Parameters
Need an ID? Create a file using brackets: `routes/users/[id].php`.

```php
<?php

use Facil\Http\Response;
use Facil\Database\Query;

return [
    'GET' => function(string $id) {
        $user = Query::table('users')->where('id', $id)->first();
        
        if (!$user) {
            return Response::json(['error' => 'User not found'], 404);
        }

        return Response::json($user);
    }
];
```

## 📚 Documentation

`Facil` comes with a beautiful, fully responsive single-page documentation. 

Simply open the `docs/index.html` (or wherever you placed your documentation file) in your browser to read the complete guide, featuring Dark/Light mode and detailed examples of every class in the framework.

## 🤝 Contributing

Contributions are what make the open-source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.

---

<div align="center">
  Developed by <a href="https://rodrigoborges.dev">Rodrigo Borges</a>
</div>