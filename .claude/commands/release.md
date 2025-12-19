# Release Command

Create a new release for flux-font-awesome.

**Usage:** `/release [patch|minor|major|vX.Y.Z]`

---

## Important: Protected Branch Workflow

This package uses **tags as the source of truth for versions** - no version file to maintain.

**With protected `main` branch, the release flow is:**

1. Merge your PR to main (via GitHub)
2. Run `/release` to tag and publish

The tag points to the current commit on main. Branch protection doesn't block tag pushes.

---

## Instructions

You are executing a release for the flux-font-awesome package. Follow these steps precisely:

### Step 1: Parse Arguments

The argument provided is: `$ARGUMENTS`

Determine the release type:
- `patch` → increment patch version (1.0.0 → 1.0.1)
- `minor` → increment minor version (1.0.0 → 1.1.0)
- `major` → increment major version (1.0.0 → 2.0.0)
- `vX.Y.Z` → use explicit version (must start with 'v')
- Empty/missing → analyze commits and suggest (see Step 3)

### Step 2: Pre-flight Checks

**2a. Clean working directory first:**
```bash
git status --porcelain
```
- If output is not empty, STOP immediately and tell user to commit or stash changes

**2b. Ensure on main and up-to-date (auto-switch if clean):**

```bash
git fetch origin main
```

Check current branch:
```bash
git branch --show-current
```

- If not on `main` AND working directory is clean: automatically run `git checkout main && git pull origin main`
- If on `main` but behind remote: automatically run `git pull origin main`

Report what you did (e.g., "Switched to main and pulled latest changes").

**2c. Run verification:**

Run `composer check` which executes tests, static analysis, and code style.

If any check fails, STOP and report the failure.

### Step 3: Determine Version

**3a. Get current state:**

Latest tag:
```bash
git describe --tags --abbrev=0 2>/dev/null || echo "none"
```

Commits since last tag (or all commits if no tags):
```bash
git log $(git describe --tags --abbrev=0 2>/dev/null || git rev-list --max-parents=0 HEAD)..HEAD --oneline 2>/dev/null || git log --oneline
```

**3b. Analyze commits for version suggestion:**

Scan commit messages since last tag for conventional commit patterns:

- `BREAKING CHANGE:` or `!:` in any commit → suggest **major**
- `feat:` or `feat(` in any commit → suggest **minor**
- `fix:`, `docs:`, `chore:`, `refactor:`, `test:`, `style:` → suggest **patch**
- No conventional commits found → suggest **patch** (safe default)

**3c. If no argument was provided, show analysis and recommendation:**

```
Current version: v1.0.0 (or "none - first release")
Commits since last release: 5

Commit analysis:
  - 2 feat: commits (new features)
  - 3 fix: commits (bug fixes)
  - No breaking changes detected

Recommended: minor (v1.0.0 → v1.1.0)

Choose release type:
  - patch → v1.0.1
  - minor → v1.1.0 (recommended)
  - major → v2.0.0

Or specify explicit version (e.g., v1.2.3)
```

Wait for user input.

**3d. Calculate new version:**

- If no tags exist: v1.0.0 for major, v0.1.0 for minor, v0.0.1 for patch
- If tags exist: parse latest and increment appropriately

**3e. Show what will be tagged and confirm:**

```
Ready to release v1.1.0

Tagging commit: abc1234 "feat: add new icon styles"

Proceed? (y/n)
```

### Step 4: Create and Push Tag

1. Create annotated tag:
   ```bash
   git tag -a vX.Y.Z -m "Release vX.Y.Z"
   ```

2. Push the tag:
   ```bash
   git push origin vX.Y.Z
   ```

### Step 5: Confirm Release

Tell the user:

```
✓ Tag vX.Y.Z pushed successfully

GitHub Actions is now:
  1. Running tests, static analysis, and code style checks
  2. Creating GitHub Release with auto-generated notes

Monitor progress: https://github.com/tunr/flux-font-awesome/actions
View release: https://github.com/tunr/flux-font-awesome/releases/tag/vX.Y.Z

Packagist will automatically pick up the new version within a few minutes.
```

---

## Composer Scripts Reference

| Script | Purpose |
|--------|---------|
| `composer check` | Run all checks (test + larastan + pint:test) |
| `composer test` | Run PHPUnit tests |
| `composer larastan` | Run static analysis |
| `composer pint:test` | Check code style |

---

## Packagist Setup (One-Time)

Packagist auto-updates are automatic AFTER initial setup:

1. Register package at [packagist.org/packages/submit](https://packagist.org/packages/submit)
2. Add GitHub webhook (Packagist provides the URL)
3. Done - all future tags are picked up automatically

If not yet registered, remind the user to complete this step.

---

## Error Handling

- If working directory dirty: stop immediately, list files
- If `composer check` fails: show which step failed and stop
- If tag already exists: suggest next available version
- If push fails: suggest checking GitHub access or permissions

---

## Manual Release Process

```bash
# 1. Ensure on latest main
git checkout main && git pull origin main

# 2. Verify everything passes
composer check

# 3. Create and push tag
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

GitHub Actions and Packagist handle the rest automatically.
