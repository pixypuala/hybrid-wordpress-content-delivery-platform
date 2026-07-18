# Changelog

All notable changes to this project are documented here. The format is based on
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres
to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Repository scaffolding: governance files, docs, and CI skeleton.
- Versioned content contract: ArticleResource DTO, ArticleTransformer (validates, normalises timestamps to ISO-8601, cleans tags), and a versioned API Envelope.
- Published article JSON Schema (contract v1).
- SurrogateKeyResolver: deterministic cache keys (versioned) and invalidation tag sets (article tag, per-term tags, global all-articles tag) for headless edge purging.
- 21 PHPUnit tests; PHPCS/WPCS clean; CI on PHP 8.1 and 8.3.
