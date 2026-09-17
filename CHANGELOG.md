# Changelog

All notable changes to DP Easy Social Share will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), 
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- Floating share sidebar, fixed to the left or right edge of the screen, with its own enable/disable toggle, post type targeting, and position setting, independent of inline icons.
- Subtle slide-in and fade-in entrance animation for the floating sidebar, with `prefers-reduced-motion` support.
- Time-delayed, dismissible admin notice inviting a plugin review, shown only after 7 days of active use, with rate now / already rated / maybe later actions that persist the choice via `wp_options`.
- Brand logo in the admin settings header, replacing the plain text heading, with a visually-hidden accessible label retained for screen readers.

## [1.1.3] - 2026-05-26
### Changed
- Updated compatibility to WordPress 7.0
- Bumped plugin version from `1.1.2` to `1.1.3`

## [1.1.2] - 2025-12-03  
### Changed
- Improved share URL encoding with `rawurlencode()` for RFC-compliant formatting.
- Escaped original URL and title prior to encoding for increased safety.
- Tested and updated compatibility to WordPress 6.9.

### Security
- Ensured returned share URLs remain safely escaped.

## [1.1.1] - 2025-09-03  
### Changed  
- Improved SVG icon handling and sanitization for security.
- Enhanced accessibility and responsiveness of icons.
- Minor code and style improvements.

## [1.1.0] - 2025-05-15  
### Added  
- dpessr_social_share_url_{$network} filter hook
- dpessr_social_share_icons filter hook

## [1.0.1] - 2025-04-08
### Changed
- Updated compatibility to WordPress 6.8
- Bumped plugin version from `1.0.0` to `1.0.1`

## [1.0.0] - 2025-03-26
### Added
- Initial plugin release
- Core social sharing functionality
- Support for Facebook, X, LinkedIn, WhatsApp, Reddit, Pinterest, Email
- Admin settings panel
- Frontend display customization options

*For more details, visit the [Git repository](https://github.com/DivyangPrajapati/dp-easy-social-share.git).*