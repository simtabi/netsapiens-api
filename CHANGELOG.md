# Changelog

All notable changes to `netsapiens` will be documented in this file

## [Unreleased]

### Fixed

- **`OAuth2` and `Request` load again.** They used `pheg()` and
  `Simtabi\Laranail\Nails\General\Traits\HasErrorStorage`, and `composer.json` required
  neither; the `nails` package no longer exists, so neither class could be loaded. The grant-type
  check is now `strcasecmp()` (pheg's comparison was case-insensitive too), and the error storage is
  an in-package trait, `Simtabi\NetSapiens\Traits\HasErrorStorage`, with the same public methods.

## 1.0.0 - 201X-XX-XX

- initial release
