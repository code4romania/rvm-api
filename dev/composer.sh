#!/bin/bash

docker run --rm -v $(pwd)/src:/app composer "$@" --ignore-platform-reqs

