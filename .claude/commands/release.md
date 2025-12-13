# Release Command

Create a new release for flux-font-awesome. Argument: version bump type or explicit version.

**Usage:** `/release <patch|minor|major|vX.Y.Z>`

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

If no argument or invalid argument, ask the user which type they want.

### Step 2: Pre-flight Checks

Run these checks and **stop if any fail**:

1. **Clean working directory**
   ```bash
   git status --porcelain
   ```
   - If output is not empty, STOP and tell user to commit or stash changes

2. **On main branch** (warn but allow override)
   ```bash
   git branch --show-current
   ```
   - If not `main`, warn the user and ask if they want to continue

3. **Synced with remote**
   ```bash
   git fetch origin main && git rev-list HEAD...origin/main --count
   ```
   - If count > 0, STOP and tell user to pull/push first

4. **Tests pass**
   ```bash
   composer test
   ```
   - If tests fail, STOP and tell user to fix tests first

5. **Static analysis passes**
   ```bash
   composer larastan
   ```
   - If analysis fails, STOP and tell user to fix issues first

6. **Code style passes**
   ```bash
   composer pint:test
   ```
   - If style check fails, STOP and tell user to run `composer pint`

### Step 3: Determine Version

1. Get the latest tag:
   ```bash
   git describe --tags --abbrev=0 2>/dev/null || echo "none"
   ```

2. Calculate new version:
   - If no tags exist and bump type specified: start at v1.0.0 (or v0.1.0 for minor, v0.0.1 for patch)
   - If no tags exist and explicit version: use that version
   - If tags exist: parse latest tag and increment appropriately

3. Confirm the new version with the user before proceeding:
   - Show: "Current version: vX.Y.Z (or none) → New version: vA.B.C"
   - Ask: "Proceed with release? (y/n)"

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

1. Tell the user:
   - Tag `vX.Y.Z` has been pushed
   - GitHub Actions will now run tests and create the release
   - Provide link: `https://github.com/tunr/flux-font-awesome/actions`
   - Once complete, release will be at: `https://github.com/tunr/flux-font-awesome/releases/tag/vX.Y.Z`

2. Remind them that Packagist will automatically pick up the new version (if webhook is configured)

---

## Error Handling

- If any git command fails, show the error and stop
- If push fails due to permissions, suggest checking GitHub access
- If tag already exists, ask if they want to delete and recreate (dangerous) or choose different version

---

## Example Outputs

**Success:**
```
✓ Working directory clean
✓ On main branch
✓ Synced with remote
✓ Tests passing (42 assertions)
✓ Static analysis clean
✓ Code style valid

Current version: v1.0.0 → New version: v1.0.1

Proceed with release? Confirmed.

✓ Created tag v1.0.1
✓ Pushed to origin

Release initiated! GitHub Actions is now:
1. Running verification tests
2. Creating the GitHub release with auto-generated notes

Monitor: https://github.com/tunr/flux-font-awesome/actions
Release: https://github.com/tunr/flux-font-awesome/releases/tag/v1.0.1
```

**Failure:**
```
✗ Working directory has uncommitted changes:
  M src/Commands/BuildCommand.php
  ?? new-file.php

Please commit or stash your changes before releasing.
```
