# Flux Font Awesome v1 Release Plan

This document outlines the systematic approach to preparing `tunr/flux-font-awesome` for a production-ready v1.0.0 release.

## Current State Assessment

| Aspect | Status | Notes |
|--------|--------|-------|
| Code Quality | Ready | PHPStan Level 8, Pint, strict types |
| Testing | Ready | Unit + Feature tests with PHPUnit 11 |
| Documentation | Ready | Comprehensive README, CONTRIBUTING |
| Dependencies | Ready | Modern, stable packages |
| API Stability | Ready | Clear interfaces, config-driven |
| **Version Management** | Missing | No version, tags, or CHANGELOG |
| **Release Automation** | Missing | No automated release workflow |

---

## Phase 1: Version Infrastructure

### 1.1 Add Version to composer.json

Add version tracking (optional but useful for `composer show`):

```json
{
    "version": "1.0.0"
}
```

> **Note**: For Composer packages, the version is primarily determined by git tags, not composer.json. However, adding it provides clarity.

### 1.2 Create CHANGELOG.md

Create a CHANGELOG following [Keep a Changelog](https://keepachangelog.com/) format:

```markdown
# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - YYYY-MM-DD

### Added
- Initial stable release
- `flux-fa:build` command for building Font Awesome icons as Flux components
- `flux-fa:rebuild` command for rebuilding existing icons
- `flux-fa:install` command for package setup
- `flux-fa:list` command for listing built icons
- `flux-fa:status` command for checking installation status
- Support for Font Awesome Free and Pro icons
- Support for all icon styles (solid, regular, light, thin, duotone, sharp variants)
- Automatic brands icon detection
- Single-file multi-style icon components
- Configurable icon sizes with Tailwind CSS mappings
- Per-icon style overrides via configuration
- Auto-detection of Font Awesome installation (npm and Composer)
- Published configuration file for customization

[Unreleased]: https://github.com/tunr/flux-font-awesome/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/tunr/flux-font-awesome/releases/tag/v1.0.0
```

---

## Phase 2: CI/CD Improvements

### 2.1 Add Static Analysis to CI

Update `.github/workflows/tests.yml` to include PHPStan:

```yaml
  static-analysis:
    runs-on: ubuntu-latest
    name: Static Analysis

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
          extensions: dom, curl, libxml, mbstring, zip
          coverage: none

      - name: Install dependencies
        run: composer update --prefer-stable --prefer-dist --no-interaction

      - name: Run PHPStan
        run: vendor/bin/phpstan analyse --memory-limit=512M
```

### 2.2 Create Release Workflow

Create `.github/workflows/release.yml` for automated releases:

```yaml
name: Release

on:
  push:
    tags:
      - 'v*'

permissions:
  contents: write

jobs:
  release:
    runs-on: ubuntu-latest
    name: Create Release

    steps:
      - name: Checkout code
        uses: actions/checkout@v4
        with:
          fetch-depth: 0

      - name: Extract version from tag
        id: version
        run: echo "VERSION=${GITHUB_REF#refs/tags/v}" >> $GITHUB_OUTPUT

      - name: Extract changelog for version
        id: changelog
        run: |
          VERSION=${{ steps.version.outputs.VERSION }}
          # Extract the section for this version from CHANGELOG.md
          awk "/^## \[${VERSION}\]/{flag=1; next} /^## \[/{flag=0} flag" CHANGELOG.md > release_notes.md
          echo "NOTES<<EOF" >> $GITHUB_OUTPUT
          cat release_notes.md >> $GITHUB_OUTPUT
          echo "EOF" >> $GITHUB_OUTPUT

      - name: Create GitHub Release
        uses: softprops/action-gh-release@v2
        with:
          name: v${{ steps.version.outputs.VERSION }}
          body: ${{ steps.changelog.outputs.NOTES }}
          draft: false
          prerelease: ${{ contains(steps.version.outputs.VERSION, '-') }}
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
```

---

## Phase 3: Pre-Release Checklist

### 3.1 Code Quality Verification

- [ ] Run full test suite: `composer test`
- [ ] Run static analysis: `composer larastan`
- [ ] Run code style check: `composer pint:test`
- [ ] Run rector dry-run: `composer rector:dry`
- [ ] Review and fix any issues

### 3.2 Documentation Review

- [ ] README.md is accurate and up-to-date
- [ ] All commands are documented with examples
- [ ] Installation instructions are complete
- [ ] Configuration options are documented
- [ ] CONTRIBUTING.md is current
- [ ] LICENSE is correct (MIT)

### 3.3 Dependency Audit

- [ ] All dependencies are on stable versions
- [ ] No known security vulnerabilities: `composer audit`
- [ ] Dependencies are appropriate (no dev-only packages in require)

### 3.4 Final Code Review

- [ ] No TODO/FIXME comments left unaddressed
- [ ] No debug code or dump statements
- [ ] All public APIs have appropriate visibility
- [ ] Error messages are user-friendly

---

## Phase 4: Release Process

### 4.1 Version Tagging Strategy

Follow [Semantic Versioning](https://semver.org/):

- **MAJOR** (1.0.0 → 2.0.0): Breaking changes to public API
- **MINOR** (1.0.0 → 1.1.0): New features, backward compatible
- **PATCH** (1.0.0 → 1.0.1): Bug fixes, backward compatible

### 4.2 Creating a Release

1. **Update CHANGELOG.md**
   - Move items from `[Unreleased]` to new version section
   - Add release date
   - Update comparison links at bottom

2. **Commit the changelog**
   ```bash
   git add CHANGELOG.md
   git commit -m "chore: prepare v1.0.0 release"
   ```

3. **Create and push tag**
   ```bash
   git tag -a v1.0.0 -m "Release v1.0.0"
   git push origin main --tags
   ```

4. **Verify release**
   - GitHub Actions creates the release automatically
   - Packagist picks up the new version (if configured)

### 4.3 Post-Release

- [ ] Verify GitHub Release was created with correct notes
- [ ] Verify Packagist shows the new version
- [ ] Test installation from Packagist: `composer require tunr/flux-font-awesome`
- [ ] Announce release (if applicable)

---

## Phase 5: Packagist Publication

### 5.1 Initial Packagist Setup (if not done)

1. Go to [packagist.org](https://packagist.org)
2. Sign in with GitHub
3. Click "Submit" and enter: `https://github.com/tunr/flux-font-awesome`
4. Enable auto-update via GitHub webhook:
   - Go to GitHub repo → Settings → Webhooks
   - Add webhook URL from Packagist
   - Set content type to `application/json`
   - Select "Just the push event"

### 5.2 Verify Package Metadata

Ensure composer.json has all required fields:
- [x] `name` - tunr/flux-font-awesome
- [x] `description` - Present
- [x] `license` - MIT
- [x] `authors` - Present
- [x] `require` - PHP and dependencies specified
- [x] `autoload` - PSR-4 configured

---

## Phase 6: Ongoing Maintenance

### 6.1 Branch Protection (Recommended)

Configure branch protection for `main`:
- Require pull request reviews
- Require status checks to pass (tests, code-style, static-analysis)
- Require branches to be up to date

### 6.2 Release Cadence

- **Patch releases**: As needed for bug fixes
- **Minor releases**: When new features are ready
- **Major releases**: Only for breaking changes (rare)

### 6.3 Dependency Updates

- Regularly update dependencies: `composer update`
- Use Dependabot or similar for automated PRs
- Review and test before merging

---

## Implementation Order

Execute phases in this order for minimal risk:

| Step | Task | Estimated Time |
|------|------|----------------|
| 1 | Create CHANGELOG.md | 20 min |
| 2 | Add static analysis to CI | 10 min |
| 3 | Create release workflow | 15 min |
| 4 | Run pre-release checklist | 30 min |
| 5 | Create v1.0.0 tag and release | 10 min |
| 6 | Configure Packagist (if needed) | 15 min |
| **Total** | | **~1.5-2 hours** |

---

## Files to Create/Modify

### New Files
- `CHANGELOG.md` - Version history
- `.github/workflows/release.yml` - Automated releases

### Modified Files
- `.github/workflows/tests.yml` - Add static analysis job

### Optional
- `composer.json` - Add version field (not required, tags are authoritative)

---

## Success Criteria

The v1 release is complete when:

1. CHANGELOG.md exists with v1.0.0 documented
2. Git tag `v1.0.0` is pushed
3. GitHub Release is automatically created with release notes
4. Package is installable via `composer require tunr/flux-font-awesome:^1.0`
5. All CI checks pass on the tagged commit
6. Packagist shows v1.0.0 as the latest stable version
