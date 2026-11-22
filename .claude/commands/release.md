# Release Command

Prepare and dispatch a verified Flux Font Awesome release.

**Usage:** `/release [patch|minor|major|vX.Y.Z]`

The public maintainer runbook is `RELEASING.md`. Follow it as the source of truth.

## Procedure

1. Require a clean working tree.
2. Fetch `origin`, switch to `main`, and fast-forward to `origin/main`.
3. Confirm the `Tests` workflow is green for the current `main` commit.
4. Run the local preflight:

   ```bash
   composer update --prefer-stable --prefer-dist --no-interaction
   composer check
   composer validate --strict
   composer audit
   ```

5. Determine the version:
   - With no existing tags, the first stable release is `v1.0.0`.
   - `patch`, `minor`, and `major` increment the latest semantic version.
   - An explicit `vX.Y.Z` argument is used as provided after validating its format.
6. Show the exact version and `main` commit that will be released, then ask for confirmation.
7. Dispatch the protected release workflow from `main`:

   ```bash
   gh workflow run release.yml --ref main -f version=vX.Y.Z
   ```

8. Monitor the workflow:

   ```bash
   gh run list --workflow release.yml --limit 1
   gh run watch <run-id> --exit-status
   ```

9. Verify that both the GitHub Release and Packagist version exist before reporting success.

## Safety rules

- Never create or push a release tag manually.
- Never dispatch from a branch other than `main`.
- Never release with a dirty checkout or failing local/remote checks.
- Stop if the tag already exists.
- Before the first release, confirm the repository is public and registered with Packagist.
