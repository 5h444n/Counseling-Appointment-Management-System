# CI/CD Documentation

This repository uses GitHub Actions for Continuous Integration (CI) and Continuous Deployment (CD).

## Workflows

### 1. CI Workflow (`.github/workflows/ci.yml`)

The CI workflow runs automatically on:
- Push to `main` or `develop` branches
- Pull requests targeting `main` or `develop` branches

**Jobs:**

#### laravel-tests
- Sets up MySQL 8.0 service for database testing
- Installs PHP 8.2 with required extensions
- Installs Composer dependencies
- Installs NPM dependencies and builds assets
- Runs Laravel Pint for code style validation
- Executes PHPUnit tests using SQLite in-memory database

#### code-quality
- Checks code style using Laravel Pint
- Ensures code follows PSR-12 coding standards

**Status Badge:**
```markdown
![CI Status](https://github.com/5h444n/Counseling-Appointment-Management-System/actions/workflows/ci.yml/badge.svg)
```

### 2. CD Workflow (`.github/workflows/cd.yml`)

The CD workflow runs automatically on:
- Push to `main` branch
- Manual trigger via workflow_dispatch

**Jobs:**

#### deploy
- Builds production-ready assets
- Installs optimized dependencies (without dev dependencies)
- Contains placeholder for deployment steps

**Deployment Options:**

The workflow includes commented examples for:
1. **SSH Deployment** - Deploy to a server via SSH
2. **Laravel Forge** - Deploy using Forge webhook
3. **Custom deployment** - AWS, DigitalOcean, or other providers

**To enable deployment:**
1. Uncomment the relevant deployment section in `.github/workflows/cd.yml`
2. Add required secrets to GitHub repository settings:
   - `DEPLOY_HOST` - Server hostname
   - `DEPLOY_USER` - SSH username
   - `DEPLOY_KEY` - SSH private key
   - `FORGE_DEPLOY_WEBHOOK` - Forge deployment webhook URL

**Status Badge:**
```markdown
![CD Status](https://github.com/5h444n/Counseling-Appointment-Management-System/actions/workflows/cd.yml/badge.svg)
```

## Local Testing

Before pushing changes, you can run tests locally:

```bash
cd CAMS

# Run tests
composer test

# Run code style check
./vendor/bin/pint --test

# Auto-fix code style issues
./vendor/bin/pint
```

## Troubleshooting

### CI Workflow Fails

1. **Composer install fails**: Check `composer.json` for syntax errors
2. **NPM build fails**: Ensure all frontend dependencies are in `package.json`
3. **Tests fail**: Run tests locally first with `composer test`
4. **Pint fails**: Run `./vendor/bin/pint` locally to auto-fix style issues

### CD Workflow Issues

1. **Build succeeds but deployment fails**: Check deployment credentials in GitHub Secrets
2. **SSH connection fails**: Verify `DEPLOY_HOST`, `DEPLOY_USER`, and `DEPLOY_KEY` are correct
3. **Webhook fails**: Verify webhook URL is correct

## Adding New Tests

1. Create test files in `tests/Feature` or `tests/Unit`
2. Follow existing test naming conventions
3. Run locally with `php artisan test`
4. Push to trigger CI workflow

## Workflow Artifacts

The CI workflow does not currently save artifacts. To enable:

```yaml
- name: Upload test results
  uses: actions/upload-artifact@v4
  if: failure()
  with:
    name: test-results
    path: storage/logs/
```

## Environment Variables

The CI workflow uses SQLite in-memory database for testing. To use MySQL instead, update the test job environment:

```yaml
env:
  DB_CONNECTION: mysql
  DB_DATABASE: testing
  DB_USERNAME: root
  DB_PASSWORD: password
```

## Security

- Never commit secrets or credentials to the repository
- Use GitHub Secrets for sensitive data
- Keep dependencies updated to avoid security vulnerabilities
- Review Dependabot alerts regularly
