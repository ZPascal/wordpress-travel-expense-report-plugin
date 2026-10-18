## Description
<!-- Describe your changes in detail -->

## Type of Change
- [ ] Bug fix (non-breaking change)
- [ ] New feature (non-breaking change)
- [ ] Breaking change
- [ ] Documentation update

## Related Issue
Closes #<!-- GitHub issue number -->

## Checklist
### Code Quality
- [ ] Code follows WordPress coding standards (verified by PHPCS)
- [ ] PHPStan analysis passes without errors
- [ ] PHP Lint check passes
- [ ] No hardcoded credentials or secrets

### Assets
- [ ] Minified CSS/JS assets have been rebuilt (`npm run build`)
- [ ] No uncommitted minified asset changes
- [ ] CSS/JS changes are properly minified and optimized

### Testing
- [ ] Unit tests pass (`composer test`)
- [ ] Code has been tested in WordPress environment
- [ ] Backward compatibility maintained (if applicable)

### Documentation
- [ ] README.md updated (if applicable)
- [ ] Inline comments added for complex logic
- [ ] Documentation files updated (if applicable)

### Plugin Compatibility
- [ ] Works with PHP 8.1+
- [ ] WordPress Plugin Check passes
- [ ] No deprecation warnings in WordPress logs
- [ ] Tested on multiple WordPress versions (if applicable)

### Security
- [ ] Input validation implemented where needed
- [ ] SQL injection protection verified (if applicable)
- [ ] XSS protection implemented
- [ ] CSRF tokens in place (wp_nonce_field/check_ajax_referer)
- [ ] Proper permission checks (current_user_can)

## Screenshots (if applicable)
<!-- Add screenshots for UI changes -->

## Notes
<!-- Any additional information for reviewers -->

