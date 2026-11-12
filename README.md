# Swagger
[![Tests](https://github.com/programster/package-swagger/actions/workflows/tests.yml/badge.svg)](https://github.com/programster/package-swagger/actions/workflows/tests.yml)

## Contents
- [About](#about)
- [Validation](#validation)
- [Testing](docs/testing.md)

## About
A PHP package to simplify the generation of OpenAPI 3.0.3 specification
documents. This package *used* to generate swagger documents, before OpenAPI
became "the standard".

The [specification for OpenAPI 3.0.3](https://swagger.io/specification/), which
this package adheres to, is available online.

## Validation
You may wish to install the swagger-cli tool in order to perform validation.
One can install the tool with:

```bash
npm install -g @apidevtools/swagger-cli
```

Once the tool is installed, validate your document with:

```bash
swagger-cli validate /path/to/swagger.json
```
