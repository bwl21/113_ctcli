FROM php:7.4-cli

WORKDIR /ctcli
COPY scripts scripts
COPY assets assets
COPY bin bin

ENTRYPOINT ["php", "/ctcli/bin/ctcli.php"]
