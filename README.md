# **Librepim – Community-Driven LTS Fork of Akeneo PIM Community Edition**

> ⚠️ **This is a community fork, not an official Akeneo project.** Librepim is maintained independently to provide long-term support for Akeneo PIM Community Edition users. For details, see [CONTRIBUTORS.md](./CONTRIBUTORS.md).

**Librepim** is an open-source, community-maintained fork of the **Akeneo PIM Community Edition**.
Its goal is to provide **Long-Term Support (LTS)**, continued improvements, security fixes, and compatibility updates for teams that rely on the community version of Akeneo for product information management.

Librepim is built on the foundation of Akeneo CE, extending its life through community contributions and modern package updates.

---

## 🚀 Key Highlights

### **🔒 Long-Term Support (LTS)**

Continuous improvements, bug fixes, dependency upgrades, and security patches.

### **🔧 Compatible Migration**

Users of Akeneo PIM CE can migrate to Librepim with minimal changes.

### **🤝 Community-Driven**

Contributions are welcome—from fixes and feature enhancements to documentation improvements.

### **⚡ Stability & Performance**

Focused on maintaining a stable and secure PIM experience for real-world workloads.

---

## 📦 Installation

Librepim follows the same installation workflow as the Akeneo PIM Community Edition, with only a few configuration-level differences.

Refer to the official installation steps here:

👉 **Installation Guide (Akeneo CE compatible)**
[https://docs.akeneo.com/master/install_pim/index.html](https://docs.akeneo.com/master/install_pim/index.html)

To build the development Docker image:

```bash
docker build --target dev -t webkul/librepim-php-dev:master .
```

---

## 🔍 Search Engine (Elasticsearch or OpenSearch)

LibrePIM runs on **Elasticsearch 8** by default and also supports
**OpenSearch 2.x** as an alternative. The choice is made by the
`SEARCH_ENGINE` environment variable; the rest of the codebase is
engine-agnostic.

### Selecting the engine

In your environment configuration:

```
# Default. Native Elasticsearch 8 client.
SEARCH_ENGINE=elasticsearch
APP_INDEX_HOSTS=<your-elasticsearch-url>

# Or opt into OpenSearch.
# SEARCH_ENGINE=opensearch
# APP_INDEX_HOSTS=<your-opensearch-url>
```

`SEARCH_ENGINE` and `APP_INDEX_HOSTS` must point at the same engine. If
`SEARCH_ENGINE` is unset or empty, Elasticsearch is used.

After switching engines, clear the Symfony cache and re-index:

```bash
rm -rf var/cache/*
php bin/console akeneo:elasticsearch:reset-indexes --env=prod
php bin/console pim:product:index --all --env=prod
php bin/console pim:product-model:index --all --env=prod
```

### How it works

When `SEARCH_ENGINE=opensearch`, the `akeneo_elasticsearch.client_builder`
service returns an `OpenSearchClientBuilderAdapter` instead of the native
`Elastic\Elasticsearch\ClientBuilder`. The adapter wraps the real
`OpenSearch\Client` so calls like `->index()`, `->search()`, and
`->indices()->putMapping()` route to OpenSearch, and translates
`OpenSearch\Common\Exceptions\OpenSearchException` (and its subclasses)
into `Elastic\Elasticsearch\Exception\ClientResponseException` or
`ServerResponseException`, preserving the HTTP status on the attached
PSR-7 response. Existing `catch (ElasticsearchException $e)` blocks
across LibrePIM keep working unchanged.

The adapter layer lives at
`src/Akeneo/Tool/Bundle/ElasticsearchBundle/SearchEngine/` and is the
only place that references both vendor SDKs. The Elasticsearch path is
unchanged — when `SEARCH_ENGINE` is `elasticsearch` (or unset),
`SearchEngineClientBuilderFactory` returns the native
`Elastic\Elasticsearch\ClientBuilder` and the rest of the codebase runs
against Elasticsearch exactly as before.

### Spec coverage

The OS→ES exception translator is unit-tested at
`src/Akeneo/Tool/Bundle/ElasticsearchBundle/spec/SearchEngine/SearchEngineExceptionTranslatorSpec.php`.

```bash
vendor/bin/phpspec run src/Akeneo/Tool/Bundle/ElasticsearchBundle/spec
```

### Pinning notes

`opensearch-project/opensearch-php` is pinned at `~2.3.1`. Newer 2.x
releases require `psr/http-message ^2.0`, which conflicts with other
LibrePIM dependencies.

### Common pitfalls

| Symptom | Cause | Fix |
|---|---|---|
| `ProductCheckException: server is not Elasticsearch` | The Elasticsearch client is talking to an OpenSearch server | Set `SEARCH_ENGINE=opensearch`, then `rm -rf var/cache/*` |
| `alias missing` (404) on a fresh OpenSearch instance | First-run reset before any indexes exist | Re-run `akeneo:elasticsearch:reset-indexes` — it is idempotent and will create the missing aliases |
| UI shows zero products after switching | The search index for the new engine is empty (MySQL data is shared, the search index is per-engine) | Run `pim:product:index --all` and `pim:product-model:index --all` |
| `cache:clear` fails | The compiled cache references stale class names | `rm -rf var/cache/*` instead of `cache:clear` |
| `composer install` drops `opensearch-php` | The lock file may not track it | `composer require opensearch-project/opensearch-php:~2.3.1` |

---

## 🔄 Upgrading

Librepim follows Akeneo CE’s upgrade flow, making transitions simple and predictable.

👉 **Upgrade Guide (Akeneo process compatible)**
[https://docs.akeneo.com/master/migrate_pim/index.html](https://docs.akeneo.com/master/migrate_pim/index.html)

---

## 🧪 Testing

Testing workflows are the same as Akeneo CE.
You can follow their testing documentation to run unit, integration, and end-to-end tests.

👉 **Testing Guide**
[https://github.com/akeneo/pim-community-dev/blob/master/internal_doc/tests/running_the_tests.md](https://github.com/akeneo/pim-community-dev/blob/master/internal_doc/tests/running_the_tests.md)

---

## 🤝 Contributing to Librepim

We encourage the community to participate and help improve the project.

Please read our **Contributing Guidelines** before submitting a pull request:

👉 [CONTRIBUTING.md](./CONTRIBUTING.md)

Contributions may include:

* Bug fixes
* Documentation improvements
* Dependency updates
* Feature enhancements
* Security improvements

A respectful and collaborative environment is expected from everyone involved.

---

## 📄 License

Librepim is distributed under the **Open Software License (OSL 3.0)**, the same license used by Akeneo PIM CE.

👉 [LICENSE.txt](./LICENSE.txt)

---

## 🎯 Why Librepim?

* **Continued Support:** Ongoing maintenance for users of Akeneo CE
* **Security First:** Regular patches and dependency updates
* **Predictable Migration:** Drop-in replacement for Akeneo CE
* **Community Ownership:** Built and improved by open-source contributors
* **Open & Transparent:** 100% free and community-governed

---

## ℹ️ About the Project

**Librepim** aims to ensure a long-term, stable future for the Akeneo Community Edition ecosystem by:

* Providing LTS
* Ensuring modern PHP, Symfony, and package compatibility
* Maintaining open-source availability for businesses and developers
* Encouraging community collaboration and contributions

Whether you're using Akeneo CE for small catalogs or managing large-scale product data, Librepim helps you continue with confidence.

---

## 🔗 Useful Links

* **Akeneo PIM Documentation**
  [https://docs.akeneo.com/](https://docs.akeneo.com/)

* **Librepim Repository**
  [https://github.com/libre-pim/librepim-dev](https://github.com/libre-pim/librepim-dev)

* **Contributing Guide**
  ./CONTRIBUTING.md

* **Issue Tracker**
  [https://github.com/libre-pim/librepim-dev/issues](https://github.com/libre-pim/librepim-dev/issues)

---

## 💬 Final Note

Librepim is built for the community and maintained by the community.
If you're looking for a stable, secure, and continuously maintained version of Akeneo CE, Librepim is the ideal choice.

Join us, contribute, and help keep the open-source PIM ecosystem strong.
 
