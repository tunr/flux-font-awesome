# Release Command

Create a new release for flux-font-awesome.

**Usage:** `/release [patch|minor|major|vX.Y.Z]`

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
- Empty/missing → proceed to Step 2, then ask user to choose

### Step 2: Pre-flight Checks

Run `composer check` which executes tests, static analysis, and code style in sequence.

If any check fails, STOP and report the failure.

Additionally verify:

1. **Clean working directory**
   ```bash
   git status --porcelain
   ```
   - If output is not empty, STOP and tell user to commit or stash changes

2. **On main branch** (warn but allow override)
   ```bash
   git branch --show-current
   ```
   - If not `main`, warn and ask if they want to continue

3. **Synced with remote**
   ```bash
   git fetch origin main 2>/dev/null && git rev-list HEAD...origin/main --count 2>/dev/null || echo "0"
   ```
   - If count > 0, STOP and tell user to pull/push first

### Step 3: Determine Version

1. Get the latest tag:
   ```bash
   git describe --tags --abbrev=0 2>/dev/null || echo "none"
   ```

2. If no argument was provided, show the user:
   - Current version (or "none" if first release)
   - What each bump type would produce
   - Ask them to choose: patch, minor, major, or explicit version

   Example prompt:
   ```
   Current version: v1.0.0

   Choose release type:
   - patch → v1.0.1 (bug fixes)
   - minor → v1.1.0 (new features)
   - major → v2.0.0 (breaking changes)

   Or specify explicit version (e.g., v1.2.3)
   ```

3. Calculate new version:
   - If no tags exist: v1.0.0 for major, v0.1.0 for minor, v0.0.1 for patch
   - If tags exist: parse latest and increment appropriately

4. Confirm the new version with the user before proceeding

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
- Tag `vX.Y.Z` has been pushed
- GitHub Actions will run verification and create the release
- Links:
  - Actions: `https://github.com/tunr/flux-font-awesome/actions`
  - Release: `https://github.com/tunr/flux-font-awesome/releases/tag/vX.Y.Z`
- Packagist will pick up the new version automatically (if webhook configured)

---

## Composer Scripts Reference

The slash command uses these composer scripts:

| Script | Purpose |
|--------|---------|
| `composer check` | Run all checks (test + larastan + pint:test) |
| `composer test` | Run PHPUnit tests |
| `composer larastan` | Run static analysis |
| `composer pint:test` | Check code style |

For manual release verification, run:
```bash
composer check && git status --porcelain
```

---

## Error Handling

- If `composer check` fails, show which step failed and stop
- If git commands fail, show the error and stop
- If push fails, suggest checking GitHub access
- If tag already exists, ask if user wants a different version

---

## Manual Release Process

If you need to release without Claude:

```bash
# 1. Verify everything passes
composer check

# 2. Check clean state
git status

# 3. Create and push tag
git tag -a v1.0.0 -m "Release v1.0.0"
git push origin v1.0.0
```

GitHub Actions handles the rest automatically.
