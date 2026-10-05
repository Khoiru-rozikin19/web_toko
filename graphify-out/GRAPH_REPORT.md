# Graph Report - web_toko  (2026-10-05)

## Corpus Check
- Corpus is ~9,845 words - fits in a single context window. You may not need a graph.

## Summary
- 172 nodes · 182 edges · 26 communities (8 shown, 18 thin omitted)
- Extraction: 99% EXTRACTED · 1% INFERRED · 0% AMBIGUOUS · INFERRED: 2 edges (avg confidence: 0.9)
- Token cost: 1,200 input · 600 output

## Community Hubs (Navigation)
- Composer Dependencies and Autoloading
- Frontend Tooling and Build Scripts
- User and Session Migrations
- User Authentication and Seeding
- Database and Cache Configuration
- Application Bootstrapping and Exceptions
- Composer Lifecycle Scripts
- Development and Testing Dependencies
- Composer Plugins and Configuration
- Service Provider Registration
- Feature and Integration Testing
- Documentation and AI Guidelines
- Unit Testing Suite
- Base Controller
- Robots Crawler Policy

## God Nodes (most connected - your core abstractions)
1. `User` - 9 edges
2. `scripts` - 9 edges
3. `require-dev` - 8 edges
4. `AppServiceProvider` - 5 edges
5. `config` - 5 edges
6. `UserFactory` - 5 edges
7. `require` - 4 edges
8. `psr-4` - 4 edges
9. `DatabaseSeeder` - 4 edges
10. `TestCase` - 4 edges

## Surprising Connections (you probably didn't know these)
- `Laravel Application Guidelines` --semantically_similar_to--> `Laravel Claude Setup`  [INFERRED] [semantically similar]
  AGENTS.md → CLAUDE.md
- `Laravel Claude Setup` --references--> `Laravel Boost`  [EXTRACTED]
  CLAUDE.md → AGENTS.md
- `Agentic Development with Boost` --references--> `Laravel Boost`  [EXTRACTED]
  README.md → AGENTS.md
- `ExampleTest` --inherits--> `TestCase`  [EXTRACTED]
  tests/Feature/ExampleTest.php → tests/TestCase.php

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Agent Setup Guidelines Flow** — agents_laravel_application, claude_laravel_application, readme_agentic_development [INFERRED 0.85]

## Communities (26 total, 18 thin omitted)

### Community 0 - "Composer Dependencies and Autoloading"
Cohesion: 0.08
Nodes (23): autoload, autoload-dev, psr-4, psr-4, description, extra, laravel, keywords (+15 more)

### Community 1 - "Frontend Tooling and Build Scripts"
Cohesion: 0.10
Nodes (20): devDependencies, concurrently, laravel-vite-plugin, tailwindcss, @tailwindcss/vite, vite, optionalDependencies, @laravel/multiplex (+12 more)

### Community 2 - "User and Session Migrations"
Cohesion: 0.16
Nodes (8): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}()

### Community 5 - "Application Bootstrapping and Exceptions"
Cohesion: 0.33
Nodes (3): {closure#1}(), {closure#2}(), {closure#3}()

### Community 6 - "Composer Lifecycle Scripts"
Cohesion: 0.22
Nodes (9): scripts, dev, post-autoload-dump, post-create-project-cmd, post-root-package-install, post-update-cmd, pre-package-uninstall, setup (+1 more)

### Community 7 - "Development and Testing Dependencies"
Cohesion: 0.25
Nodes (8): require-dev, fakerphp/faker, laravel/pail, laravel/pao, laravel/pint, mockery/mockery, nunomaduro/collision, phpunit/phpunit

### Community 8 - "Composer Plugins and Configuration"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 11 - "Documentation and AI Guidelines"
Cohesion: 0.50
Nodes (5): Laravel Application Guidelines, Laravel Boost, Laravel Claude Setup, About Laravel Framework, Agentic Development with Boost

## Knowledge Gaps
- **53 isolated node(s):** `Controller`, `$schema`, `name`, `type`, `description` (+48 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 92 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **18 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `scripts` connect `Composer Lifecycle Scripts` to `Composer Dependencies and Autoloading`?**
  _High betweenness centrality (0.023) - this node is a cross-community bridge._
- **Why does `require-dev` connect `Development and Testing Dependencies` to `Composer Dependencies and Autoloading`?**
  _High betweenness centrality (0.021) - this node is a cross-community bridge._
- **Why does `config` connect `Composer Plugins and Configuration` to `Composer Dependencies and Autoloading`?**
  _High betweenness centrality (0.018) - this node is a cross-community bridge._
- **What connects `Controller`, `$schema`, `name` to the rest of the system?**
  _53 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Composer Dependencies and Autoloading` be split into smaller, more focused modules?**
  _Cohesion score 0.08333333333333333 - nodes in this community are weakly interconnected._
- **Should `Frontend Tooling and Build Scripts` be split into smaller, more focused modules?**
  _Cohesion score 0.09956709956709957 - nodes in this community are weakly interconnected._