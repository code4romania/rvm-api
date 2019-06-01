#!/bin/bash

docker run --rm -v $(pwd)/src:/app composer/composer "$@"

