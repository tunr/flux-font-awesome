# Contributing

Thank you for considering contributing to Flux Font Awesome! This guide will help you get started.

## Code of Conduct

Be respectful, inclusive, and constructive in all interactions.

## Development Setup

1. **Fork the repository** and clone it locally:
```bash
git clone https://github.com/YOUR-USERNAME/flux-font-awesome.git
cd flux-font-awesome
```

2. **Install dependencies**:
```bash
composer install
```

3. **Create a new branch** for your feature or bug fix:
```bash
git checkout -b feature/your-feature-name
```

## Development Workflow

### Running Tests

Run the full test suite:
```bash
composer test
```

Run tests with coverage:
```bash
composer test:coverage
```

### Code Style

This project uses [Laravel Pint](https://laravel.com/docs/pint) for code formatting:

```bash
composer pint
```

### Before Committing

Run the same local quality gate used for release preparation:

```bash
composer check
composer validate --strict
composer audit
```

Then update documentation and add tests for any user-facing behavior changes.

## Pull Request Process

1. **Write clear commit messages** following conventional commits:
   - `feat: add new feature`
   - `fix: resolve bug`
   - `docs: update documentation`
   - `test: add missing tests`
   - `refactor: improve code structure`

2. **Ensure CI passes** - all tests and code style checks must pass

3. **Write a clear PR description** explaining:
   - What changes you made
   - Why you made them
   - How to test them

4. **Request review** and be responsive to feedback

## Testing Guidelines

- Write tests for all new features
- Maintain or improve code coverage
- Use descriptive test names
- Follow the existing test structure (Unit vs Feature)

## Documentation

- Update README.md for user-facing changes
- Add inline comments for complex logic
- Update configuration file comments
- Keep examples up to date

## Reporting Issues

When reporting bugs, please include:
- Laravel version
- PHP version
- Font Awesome version (Free or Pro)
- Steps to reproduce
- Expected vs actual behavior
- Error messages or logs

## Feature Requests

Feature requests are welcome! Please:
- Check if it already exists
- Explain the use case
- Provide examples
- Consider backward compatibility

## Questions?

- Open or search [Issues](https://github.com/tunr/flux-font-awesome/issues)

Thank you for contributing!
