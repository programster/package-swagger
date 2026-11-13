#!/bin/sh
# Checks that the documents the package outputs are valid OpenAPI 3.0.3, with Redocly CLI
# (https://redocly.com/docs/cli/). The smoke tests only compare the output with what it should be, so this checks it
# against the specification itself. It validates:
#   - testing/assets/users-api.json, which the smoke tests compare a document with,
#   - and a document that uses every feature of the package (from testing/validation/build-documents.php), as both
#     JSON and YAML.
#
# Usage:
#   testing/validate-with-redocly.sh
#
# Requires Docker, and "composer install" to have been run. The document is built with the PHP 8.2 test image from
# testing/run-all-php-versions.sh (built here if it doesn't exist yet), as toYaml() needs the yaml extension.
# Exits with 0 if every document is valid, or 1 if not.

# The version of Redocly CLI to use. Pinned, so that a new release's rules can't fail a build that passed before.
REDOCLY_VERSION="2.72.1"
PHP_VERSION="8.2"

scriptDir=$(cd "$(dirname "$0")" && pwd)
repoDir=$(dirname "$scriptDir")
image="swagger-php${PHP_VERSION}-test"

if [ ! -f "$repoDir/vendor/autoload.php" ]
then
    echo "vendor/autoload.php is missing. Please run \"composer install\" first."
    exit 1
fi

if ! docker image inspect "$image" > /dev/null 2>&1
then
    echo "building ${image}..."

    if ! docker build -q --pull --build-arg PHP_VERSION="$PHP_VERSION" -t "$image" "$scriptDir/php-versions" > /dev/null
    then
        echo "FAILED to build the image for PHP ${PHP_VERSION}"
        exit 1
    fi
fi

documentsDir=$(mktemp -d)
trap 'rm -rf "$documentsDir"' EXIT
cp "$scriptDir/assets/users-api.json" "$scriptDir/validation/redocly.yaml" "$documentsDir/"

# The documents are written as the current user, so that they can be deleted afterwards.
if ! docker run --rm --user "$(id -u):$(id -g)" -v "$repoDir":/src:ro -v "$documentsDir":/documents "$image" \
    php /src/testing/validation/build-documents.php /documents
then
    echo "FAILED to build the documents"
    exit 1
fi

docker run --rm -v "$documentsDir":/documents:ro -w /documents node:22-slim \
    npx --yes "@redocly/cli@${REDOCLY_VERSION}" lint --config redocly.yaml --format=stylish \
    users-api.json all-features.json all-features.yaml
