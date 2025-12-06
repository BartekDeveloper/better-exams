# Project Purpose
School project - Website for practice to exams in Poland INF.02, INF.03, INF.04 For high school students.
Project needs to be developed in PHP language, with MySQL database. I am using Bootstrap and graffity for styles,
Custom api for dynamic JavaScript content, and PHP for server side and overall backend.

Tables are generated dynamically from "(root)/model/tables.php" file.
Data is crawled from another website using Python scripts.

# Technologies
- HTML
- CSS:
    - Bootstrap
    - Graffity
- JavaScript
- PHP
    - v8.x.x
    - mysqli
- MySQL
    - mariadb
- Python
    - requests
    - BeautifulSoup

# Project Structure
```
.
├── controller
├── model
│   └── data
│       ├── inf_02 # AUTOMATICALLY GENERATED
│       │   ├── images
│       │   └── venv
│       ├── inf_03 # AUTOMATICALLY GENERATED
│       │   ├── images
│       │   └── venv
│       └── inf_04 # AUTOMATICALLY GENERATED
│           ├── images
│           └── venv
├── scripts
├── sql
└── view
    ├── assets
    ├── css
    ├── js
    └── module
        └── card
```

# Project Requirements
1.  The website should be responsive and mobile friendly.
2.  Website should focus on the accessibility of the user (for blind people, visually impaired people, etc. - meaning neutral colors, AAA contrast, reduced motion option, labeled, titled and sematic elements with descriptions, etc.).
3.  Website should have two or more themes: dark, light.
4.  Website should have nice animations and transitions.
5.  Website should have a nice design.
6.  Website should be protected from cheating on exams.
7.  Website should have user account system - for registration, login. Statistics per user and per exam.
8.  Website should have a nice admin panel.
9.  Website should use many reusable 'modules' and 'components'.
10. Website should have a nice API custom API for backend - dynamic content.
11. Website should be server-side rendered mostly(SSR via PHP) and client should be only for the frontend and not-critical parts.
12. Website should be the best SEO friendly as it can be.
13. Website should follow DRY principle - Don't Repeat Yourself.
14. Website should be the good in terms of performance.
15. Website should be the very good in terms of security.
16. Exams should be dynamic server-side rendered and server-side validated.
17. UX should be the best it can be.

# Modules
- Auth
- History

# Components
- Card
- Button
- Form
- Input
- Modal
- Navbar
- Table

# Scripts
- Should work for both Linux and Windows systems - bash or shell scripts should have window .bat or .ps1 equivalents.
- Python scripts for Python 3.x

# Database
- MySQL

