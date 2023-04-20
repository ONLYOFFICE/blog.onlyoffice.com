=== WPGraphQL Smart Cache ===
Contributors: WPGraphQL, markkelnar, jasonbahl
Tags: WPGraphQL, Cache, API, Invalidation, Persisted Queries, GraphQL, Performance, Speed
Requires at least: 5.6
Tested up to: 6.1
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

=== Description ===

Do you want your API data _fast_ or _accurate_? With WPGraphQL Smart Cache, you can have both.

WPGraphQL Smart Cache is a free, open-source WordPress plugin that provides support for caching and cache invalidation of WPGraphQL Queries.

To get the most out of this plugin, we recommend using GET requests with Network Caching, which requires your WordPress install to be on a [supported host](https://github.com/wp-graphql/wp-graphql-smart-cache/blob/main/docs/network-cache.md#supported-hosts).

*BREAKING CHANGES:* We may make breaking changes in the future to improve functionality and experience. If we do, we will use semver to do so. Pay attention to release notes and upgrade notices before updating.

== Video Overview ==

<a href="https://youtu.be/t_y6q02q7K4" target="_blank"><img src="https://github.com/wp-graphql/wp-graphql-smart-cache/raw/main/docs/images/banner-wp-graphql-smart-cache-v1.jpg" width="640px" /></a>

== Docs ==

- [Overview](https://github.com/wp-graphql/wp-graphql-smart-cache#overview)
- [Quick Start](https://github.com/wp-graphql/wp-graphql-smart-cache#-quick-start)
- Features
  - [Network Cache](https://github.com/wp-graphql/wp-graphql-smart-cache/blob/main/docs/network-cache.md)
  - [Object Cache](https://github.com/wp-graphql/wp-graphql-smart-cache/blob/main/docs/object-cache.md)
  - [Persisted Queries](https://github.com/wp-graphql/wp-graphql-smart-cache/blob/main/docs/persisted-queries.md)
  - [Cache Invalidation](https://github.com/wp-graphql/wp-graphql-smart-cache/blob/main/docs/cache-invalidation.md)
- [Extending / Customizing Functionality](https://github.com/wp-graphql/wp-graphql-smart-cache/blob/main/docs/extending.md)
- [FAQ and Troubleshooting](https://github.com/wp-graphql/wp-graphql-smart-cache#faq--troubleshooting)
- [Known Issues](https://github.com/wp-graphql/wp-graphql-smart-cache#known-issues)
- [Providing Feedback](https://github.com/wp-graphql/wp-graphql-smart-cache#providing-feedback)

= Upgrading =

It is recommended that anytime you want to update WPGraphQL Smart Cache that you get familiar with what's changed in the release.

WPGraphQL Smart Cache publishes [release notes on GitHub](https://github.com/wp-graphql/wp-graphql-smart-cache/releases).

WPGraphQL Smart Cache will follow Semver versioning.

The summary of Semver versioning is as follows:

- *MAJOR* version when you make incompatible API changes,
- *MINOR* version when you add functionality in a backwards compatible manner, and
- *PATCH* version when you make backwards compatible bug fixes.

You can read more about the details of Semver at [semver.org](https://semver.org)

== Privacy Policy ==

WPGraphQL Smart Cache uses [Appsero](https://appsero.com) SDK to collect some telemetry data upon user's confirmation. This helps us to troubleshoot problems faster & make product improvements.

Appsero SDK **does not gather any data by default.** The SDK only starts gathering basic telemetry data **when a user allows it via the admin notice**. We collect the data to ensure a great user experience for all our users.

Integrating Appsero SDK **DOES NOT IMMEDIATELY** start gathering data, **without confirmation from users in any case.**

Learn more about how [Appsero collects and uses this data](https://appsero.com/privacy-policy/).


== Upgrade Notice ==

= 0.2.0 =

This release removes a lot of code that has since been released as part of WPGraphQL core.

In order to use v0.2.0+ of WPGraphQL Smart Cache, you will need WPGraphQL v1.12.0 or newer.

== Changelog ==

= 1.0.3 =

- [#207](https://github.com/wp-graphql/wp-graphql-smart-cache/pull/207) fix: ignore updates to "apple_news_update" meta key. Add `graphql_cache_ignored_meta_keys` filter for modifying the list of ignored meta keys.
- [#205](https://github.com/wp-graphql/wp-graphql-smart-cache/pull/205) fix: ErrorException Warning: Attempt to read property "post_type" on null. Thanks @izzygld!

= 1.0.2 =

**Chores / Bugfixes**

- [#202](https://github.com/wp-graphql/wp-graphql-smart-cache/pull/202) fix: ErrorException Warning: Attempt to read property "post_type" on null. Thanks @izzygld!

= 1.0.1 =

**Chores / Bugfixes**

- Add workflow to update plugin assets/readme when those files are changed
- update links to docs. Thanks @rodrigo-arias!
- set internal taxonomies to public => false, add tests.
- fix bug with the "purge cache" button in the settings page not properly purging all caches for WPEngine users

= 1.0 =

- Version change. no functional changes.

= 0.3.9 =

- fix: vendor directory not properly deploying to WordPress.org.

= 0.3.8 =

- fix: rename constant that didn't get updated in 0.3.4. Thanks @colis!

= 0.3.7 =

- chore: update readme.txt file which is displayed on WordPress.org

= 0.3.6 =

- fix: correct slug in deploy workflow

= 0.3.5 =

- ([#189](https://github.com/wp-graphql/wp-graphql-smart-cache/pull/189)): chore: add workflow to deploy to the WordPress.org repo

= 0.3.4 =

- ([#188](https://github.com/wp-graphql/wp-graphql-smart-cache/pull/188)): fix: update constant name for min required version of WPGraphQL. Conflict with constant name defined in WPGraphQL for ACF.

= 0.3.3 =

- ([#184](https://github.com/wp-graphql/wp-graphql-smart-cache/pull/184)): fix: update min required version of WPGraphQL. This plugin relies on features introduced in v1.12.0 of WPGraphQL.

= 0.3.2 =

**New Features**

- ([#178](https://github.com/wp-graphql/wp-graphql-smart-cache/pull/178)): feat: add new "graphql_cache_is_object_cache_enabled" filter

**Chores/Bugfixes**

- ([#179](https://github.com/wp-graphql/wp-graphql-smart-cache/pull/179)): fix: prevent error when users install the plugin with Composer

= 0.3.1 =

- chore: update readme.txt with tags, updated "tested up to" version
- chore: update testing matrix to run tests on more versions of WordPress and PHP
- chore: update docs
- chore: add icons and banner for WordPress.org

= 0.3.0 =

- feat: a LOT of updates to the documentation
- feat: add opt-in telemetry via Appsero.

= 0.2.3 =

- fix: fixes a bug where X-GraphQL-Keys weren't being returned properly when querying a persisted query by queryId

= 0.2.2 =

- fix bug with patch. Missing namespace

= 0.2.1 =

- add temporary patch for wp-engine users. Will be removed when the wp engine mu plugin is updated.


= 0.2.0

- chore: remove unreferenced .zip build artifact
- feat: remove a lot of logic from Collection.php that analyzes queries to generate cache keys and response headers, as this has been moved to core WPGraphQL
- feat: reference core WPGraphQL functions for storing cache maps for object caching
- chore: remove unused "use" statements in Invalidation.php
- feat: introduce new "graphql_purge" action, which can be hooked into by caching clients to purge caches by key
- chore: remove $collection->node_key() method and references to it.
- feat: add "purge("skipped:$type_name)" event when purge_nodes is called
- chore: remove model class prefixes from purge_nodes() calls
- chore: rename const WPGRAPHQL_LABS_PLUGIN_DIR to WPGRAPHQL_SMART_CACHE_PLUGIN_DIR
- chore: update tests to remove "node:" prefix from expected keys
- chore: update tests to use self::factory() instead of $this->tester->factory()
- chore: update Plugin docblock
- feat: add logic to ensure minimum version of WPGraphQL is active before executing functionality needed by it
- chore: remove filters that add model definitions to Types as that's been moved to WPGraphQL core

= 0.1.2 =

- Updates to support batch queries
- move save urls out of this plugin into the wpengine cache plugin
- updates to tests

= 0.1.1 =

- Initial release to beta users
