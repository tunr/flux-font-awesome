# Package Structure

```
flux-font-awesome/
├── .github/
│   └── workflows/
│       └── tests.yml                 # GitHub Actions CI/CD
├── config/
│   └── flux-font-awesome.php         # Package configuration
├── resources/
│   └── views/
│       └── flux/
│           └── icon/
│               └── fa/
│                   ├── .gitkeep
│                   └── example.blade.php  # Example icon component
├── src/
│   ├── Commands/
│   │   ├── BuildCommand.php          # fa:build - Build icons from SVGs
│   │   ├── ConfigCommand.php         # fa:config - Display configuration
│   │   ├── InstallCommand.php        # fa:install - Package installation
│   │   └── StatusCommand.php         # fa:status - Show package status
│   ├── Services/
│   │   ├── IconWriter.php            # Generate Blade component files
│   │   └── SvgParser.php             # Parse Font Awesome SVGs
│   ├── Support/
│   │   └── Variant.php               # Variant constants and helpers
│   └── FluxFontAwesomeServiceProvider.php  # Laravel service provider
├── tests/
│   ├── Feature/
│   │   ├── ConfigCommandTest.php     # Feature test for fa:config
│   │   └── InstallCommandTest.php    # Feature test for fa:install
│   ├── Unit/
│   │   ├── SvgParserTest.php         # Unit test for SvgParser
│   │   └── VariantTest.php           # Unit test for Variant
│   └── TestCase.php                  # Base test case
├── .gitattributes                    # Git export configuration
├── .gitignore                        # Git ignore rules
├── CHANGELOG.md                      # Version history
├── CONTRIBUTING.md                   # Contribution guidelines
├── LICENSE.md                        # MIT License
├── README.md                         # Package documentation
├── composer.json                     # Composer package manifest
├── phpunit.xml                       # PHPUnit configuration
└── pint.json                         # Laravel Pint code style config
```

## Key Components

### Commands (`src/Commands/`)
Artisan commands for package management and icon building.

### Services (`src/Services/`)
Core business logic for parsing SVGs and generating icon components.

### Support (`src/Support/`)
Helper classes and utilities for variant management.

### Tests (`tests/`)
- **Unit tests**: Test individual classes in isolation
- **Feature tests**: Test commands and integration scenarios

### Configuration (`config/`)
Customizable package settings for paths, variants, sizing, and build options.

### Resources (`resources/views/`)
Generated icon Blade components following Flux UI patterns.

## Generated Icons

When you run `php artisan fa:build`, icon components are created in:
```
resources/views/flux/icon/fa/
├── user.blade.php
├── home.blade.php
├── github.blade.php
└── ... (all built icons)
```

Each icon file contains all available variants in a single-file architecture.
