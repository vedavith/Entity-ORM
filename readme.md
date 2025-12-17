# Entity-ORM

A simple PHP Entity ORM for generating and managing entity models.

## Installation

Install via Composer:

```bash
composer require entity-forge/entity-forge
```

## Features

- Generating MySQL tables and related POCO classes from JSON objects.

## Folder Structure

- `src/Core/` - Core classes like ModelGenerator
- `src/EntityConnector/` - Database connection classes
- `src/EntityGenerator/` - Model generation logic
- `src/EntityModels/` - Generated model classes
# EntityForge

EntityForge is a lightweight PHP utility to generate PHP model classes and repository scaffolding from JSON model definitions. It focuses on developer productivity: define your data model as JSON, then generate POPO model classes and thin repositories that delegate data access to a central `EntityDriver`.

Version: 1.0

## Goals

- Provide a simple, composable generator to convert JSON model descriptions into PHP model classes and repositories.
- Keep runtime footprint minimal — generated model classes are plain PHP objects; repositories are thin adapters.
- Make the code generation pipeline extensible (custom templates or additional generators).

## Quick Install

Install via Composer (local development):

```bash
composer install --dev
```

## Project Layout

- `src/Core/` — Generators: `ModelGenerator`, `RepositoryGenerator`.
- `src/EntityGenerator/` — Orchestration code that reads JSON models and invokes generators.
- `src/EntityModels/` — Generated model classes (POPOs).
- `src/EntityRepository/` — Generated repository classes (thin wrappers using `EntityDriver`).
- `src/JsonModels/` — JSON model definitions included with the package (used by the generator).
- `tests/` — PHPUnit tests that validate generation behavior.

## JSON Model Format

Each model is a JSON file with a top-level `model` (class name) and `fields` object. Example `src/JsonModels/users.model.json`:

```json
{
  "model": "User",
  "fields": {
    "id": { "type": "int", "primary": true },
    "username": { "type": "string", "maxLength": 100 },
    "email": { "type": "string", "maxLength": 255 }
  }
}
```

The generator creates a PHP class `User` in `src/EntityModels/User.php` and a repository `UserRepository` in `src/EntityRepository/UserRepository.php`.

## CLI: Generate Models & Repositories

Use the bundled CLI to generate model and repository files from JSON models included in `src/JsonModels`:

```bash
php src/EntityGenerator/Entity:Gen create-model --model=users
```

- `--model` refers to the base filename in `src/JsonModels` (without `.model.json`).
- The command writes the generated model file to `src/EntityModels/` and repository to `src/EntityRepository/`.

## Running Tests

Install dev dependencies and run PHPUnit:

```bash
composer install --dev
vendor/bin/phpunit --testdox
```

Note: PHPUnit requires the PHP `dom` extension. On Debian/Ubuntu install via `sudo apt install php-xml`.

## Future Work (planned for next releases)

- Read and write tables from the JSON model definitions directly (persist schema/state as JSON) instead of auto-creating SQL tables.
- Add configurable templates for code generation (allow custom class templates).
- Dependency injection for repositories and integration tests with an in-memory database.

## Contribution

Contributions welcome. Fork the repo, create a feature branch, and open a PR describing the change.

## License

MIT

