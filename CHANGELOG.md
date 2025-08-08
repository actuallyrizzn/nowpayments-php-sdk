# Changelog

All notable changes to the NowPayments PHP SDK will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial release of NowPayments PHP SDK
- Complete API coverage for all NowPayments endpoints
- Payment creation and management
- Subscription and recurring payment support
- Mass payout functionality with 2FA support
- Custody (sub-account) management
- Internal cryptocurrency conversions
- IPN (Instant Payment Notification) handling with signature verification
- Comprehensive error handling and validation
- Full type hints and documentation
- Sandbox environment support
- Custom HTTP client configuration
- Extensive examples and documentation

### Features
- **Payments API**: Create, manage, and track cryptocurrency payments
- **Subscriptions API**: Handle recurring payments and subscription plans
- **Payouts API**: Mass payouts with 2FA verification and address validation
- **Custody API**: Sub-account management, balances, transfers, and withdrawals
- **Conversions API**: Internal cryptocurrency conversions
- **General API**: Status checks, currency information, and price estimates
- **IPN Support**: Webhook signature verification and payment status handling

### Technical
- PHP 7.4+ compatibility
- PSR-4 autoloading
- Guzzle HTTP client integration
- Comprehensive exception handling
- Input validation and sanitization
- HMAC-SHA512 signature verification for IPNs
- Support for both production and sandbox environments 