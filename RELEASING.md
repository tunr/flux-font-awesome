# Releasing Flux Font Awesome

Releases are created by a manually dispatched GitHub Actions workflow. The workflow runs the complete supported Laravel/PHP matrix and quality gates before creating a tag, so an unverified tag cannot be published accidentally.

## One-time public package setup

Before the first stable release:

1. Make the GitHub repository public after completing the public-history and secret scan.
2. Confirm GitHub private vulnerability reporting remains enabled.
3. Require the stable `Tests / Required` check on `main` branch protection.
4. Submit `https://github.com/tunr/flux-font-awesome` to Packagist.
5. Enable Packagist's GitHub integration for automatic package updates.
6. Confirm Packagist resolves the package's `dev-main` metadata.

## Release process

1. Merge all intended changes into `main`.
2. Confirm the `Tests` workflow is green on `main`.
3. From GitHub, open **Actions → Release → Run workflow**.
4. Select the `main` branch and enter a semantic version tag such as `v1.0.0`.
5. Wait for the reusable test matrix to pass. The workflow will then:
   - create the version tag at the exact verified commit;
   - create a GitHub Release with generated notes.
6. Confirm Packagist detects the new version.

The workflow rejects malformed or existing tags and refuses to release from any branch other than `main`.

## Local preflight

Run this before requesting a release:

```bash
composer update --prefer-stable --prefer-dist --no-interaction
composer check
composer validate --strict
composer audit
```

Do not create or push release tags manually. A manually pushed tag can be indexed by Packagist before the repository's verification workflow finishes.
