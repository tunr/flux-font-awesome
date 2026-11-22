# Security Policy

## Supported versions

Until the first stable release, security fixes are applied to the latest commit on `main`. After v1.0.0, the latest stable major release will receive security fixes.

## Reporting a vulnerability

Please report suspected vulnerabilities privately through GitHub's **Report a vulnerability** feature for this repository. Do not open a public issue containing exploit details, credentials, or private Font Awesome assets.

Include:

- the affected package version or commit;
- Laravel, PHP, Flux, and Font Awesome versions;
- reproduction steps or a minimal test case;
- the expected impact;
- any suggested mitigation.

You should receive an acknowledgment within seven days. Confirmed issues will be investigated, fixed, and disclosed with an appropriate security advisory and release.

## Scope

The package parses local Font Awesome SVG files and writes generated Blade components. Reports involving path traversal, unsafe SVG content, arbitrary file writes, generated-template injection, or unintended exposure of licensed Font Awesome assets are in scope.
