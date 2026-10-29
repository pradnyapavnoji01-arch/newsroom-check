# Newsroom Check

A PHP and MySQL website for checking news claims using source evidence and optional AI analysis.

## Setup

1. Copy `config.example.php` to `config.php`.
2. Set your database host, database name, database username, and password in `config.php`.
3. Add a Groq API key only if you want the optional AI feature.
4. Import the project's SQL schema into your MySQL database.
5. Run the site on a PHP server with the required PHP extensions enabled.

## Security

- Never commit `config.php`, API keys, database passwords, or server error logs.
- `config.php` is intentionally excluded from this repository. Use `config.example.php` as a template.
- Rotate any credentials that may have been exposed.
