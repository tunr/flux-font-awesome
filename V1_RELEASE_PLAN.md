# Flux Font Awesome v1 Release Plan

Streamlined release process with zero-maintenance versioning.

## Current State

| Aspect | Status |
|--------|--------|
| Code Quality | Ready (PHPStan Level 8, Pint, strict types) |
| Testing | Ready (Unit + Feature tests) |
| Documentation | Ready (README, CONTRIBUTING) |
| CI/CD | Ready (Tests, Code Style, Static Analysis) |
| Release Automation | Ready (Tag-triggered releases) |

---

## Release Infrastructure

### Files Created

```
.github/
├── release.yml              # Auto-generates release notes from commits/PRs
└── workflows/
    ├── tests.yml            # Tests, code style, static analysis
    └── release.yml          # Creates GitHub release on tag push

.claude/
└── commands/
    └── release.md           # /release slash command for Claude Code
```

### How It Works

1. **No CHANGELOG to maintain** - GitHub auto-generates release notes from commits and PRs
2. **Tag-triggered releases** - Push a tag → GitHub Actions creates the release
3. **Claude Code integration** - Use `/release` command to handle everything

---

## Using the /release Command

The `/release` slash command handles the entire release process:

```
/release patch    # 1.0.0 → 1.0.1
/release minor    # 1.0.0 → 1.1.0
/release major    # 1.0.0 → 2.0.0
/release v1.2.3   # Explicit version
```

### What It Does

1. **Pre-flight checks**
   - Clean working directory
   - On main branch (warns if not)
   - Synced with remote
   - Tests pass
   - Static analysis passes
   - Code style valid

2. **Version calculation**
   - Gets latest tag (or starts at v1.0.0)
   - Calculates next version based on bump type
   - Confirms with you before proceeding

3. **Release execution**
   - Creates annotated git tag
   - Pushes tag to origin
   - GitHub Actions takes over from here

4. **GitHub Actions**
   - Runs full test suite
   - Creates GitHub Release with auto-generated notes
   - Packagist picks up the new version automatically

---

## First Release (v1.0.0)

To create the initial v1.0.0 release:

```
/release v1.0.0
```

Or simply:

```
/release major
```

Claude will:
1. Verify all checks pass
2. Confirm the version with you
3. Create and push the tag
4. Provide links to monitor the release

---

## Release Notes Configuration

Release notes are auto-generated based on PR labels:

| Label | Section |
|-------|---------|
| `breaking`, `breaking-change` | ⚠️ Breaking Changes |
| `feature`, `enhancement` | 🚀 Features |
| `bug`, `bugfix`, `fix` | 🐛 Bug Fixes |
| `documentation`, `docs` | 📚 Documentation |
| `chore`, `maintenance`, `refactor` | 🧹 Maintenance |

PRs from dependabot and items labeled `skip-changelog` are excluded.

---

## Pre-v1.0.0 Checklist

Before running `/release v1.0.0`:

- [ ] Merge this PR to main
- [ ] Ensure all tests pass on main
- [ ] Verify Packagist webhook is configured (or set it up after first release)
- [ ] Review README for accuracy

---

## Packagist Setup

After the first release, if not already configured:

1. Go to [packagist.org](https://packagist.org) and submit the package
2. Add GitHub webhook for auto-updates:
   - Repo Settings → Webhooks → Add webhook
   - URL: `https://packagist.org/api/github?username=YOUR_PACKAGIST_USERNAME`
   - Content type: `application/json`
   - Events: Just push

---

## Version Strategy

Following [Semantic Versioning](https://semver.org/):

- **MAJOR**: Breaking changes to commands, config structure, or output format
- **MINOR**: New features, new commands, new config options
- **PATCH**: Bug fixes, documentation, internal improvements
