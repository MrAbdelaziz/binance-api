# Contributing to Binance API Package

Thank you for considering contributing to the Binance API package! This document provides guidelines for contributing to the project.

## 🤝 How to Contribute

### Reporting Issues
- Use the [GitHub Issues](https://github.com/MrAbdelaziz/binance-api/issues) page
- Search existing issues before creating a new one
- Provide detailed information including:
  - Laravel version
  - PHP version
  - Package version
  - Steps to reproduce
  - Expected vs actual behavior
  - Error messages and stack traces

### Suggesting Features
- Open a feature request issue
- Describe the feature in detail
- Explain the use case and benefits
- Consider providing a draft implementation

### Pull Requests
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass (`composer test`)
6. Update documentation if needed
7. Commit your changes (`git commit -m 'Add amazing feature'`)
8. Push to the branch (`git push origin feature/amazing-feature`)
9. Open a Pull Request

## 🧪 Development Setup

### Prerequisites
- PHP 8.1 or higher
- Composer
- Laravel 10.0 or higher

### Local Development
```bash
# Clone the repository
git clone https://github.com/MrAbdelaziz/binance-api.git
cd binance-api

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Add your Binance API keys for testing
BINANCE_API_KEY=your_test_api_key
BINANCE_API_SECRET=your_test_api_secret
BINANCE_TESTNET=true

# Run tests
composer test
```

### Testing
- Write tests for all new functionality
- Ensure existing tests continue to pass
- Use Binance testnet for testing
- Mock external API calls where appropriate

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Run static analysis
composer analyse
```

## 📝 Coding Standards

### PHP Standards
- Follow PSR-12 coding standards
- Use strict types where possible
- Add proper docblocks for all methods
- Use meaningful variable and method names

### Code Quality
- Keep methods focused and small
- Use dependency injection
- Handle exceptions appropriately
- Add logging for important operations

### Documentation
- Update README.md for new features
- Add examples for complex functionality
- Update CHANGELOG.md
- Include docblocks for all public methods

## 🔒 Security

### Reporting Security Issues
- **DO NOT** open public issues for security vulnerabilities
- Email security concerns to: security@MrAbdelaziz.com
- Include detailed information about the vulnerability
- Allow time for the issue to be addressed before public disclosure

### Security Best Practices
- Never commit API keys or secrets
- Use environment variables for configuration
- Validate all user inputs
- Use HTTPS for all API communications
- Follow Binance API security guidelines

## 🎯 Focus Areas

We welcome contributions in these areas:

### High Priority
- Bug fixes and security improvements
- Performance optimizations
- Documentation improvements
- Test coverage improvements

### Medium Priority
- New Binance API endpoint integrations
- Additional utility methods
- Better error handling
- Code quality improvements

### Low Priority
- New features and enhancements
- Additional exchanges support
- Advanced analytics features

## 📋 Checklist for Contributors

Before submitting a PR, ensure:

- [ ] Code follows PSR-12 standards
- [ ] All tests pass
- [ ] New functionality includes tests
- [ ] Documentation is updated
- [ ] CHANGELOG.md is updated
- [ ] No API keys or secrets in code
- [ ] Error handling is appropriate
- [ ] Code is well-commented

## 🏷️ Versioning

This project follows [Semantic Versioning](https://semver.org/):
- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

## 📄 License

By contributing to this project, you agree that your contributions will be licensed under the MIT License.

## 🙏 Recognition

All contributors will be recognized in:
- README.md contributors section
- CHANGELOG.md for their contributions
- GitHub releases notes

## 💬 Communication

- GitHub Issues for bugs and features
- GitHub Discussions for questions and ideas
- Email for security issues: security@MrAbdelaziz.com

## 🎉 Thank You!

Your contributions help make this package better for everyone in the cryptocurrency and Laravel communities!
