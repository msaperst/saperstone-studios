FROM golang:1.26.6-bookworm AS gosu-builder

# The official MySQL 8.0 image currently bundles gosu 1.19 built with an
# unsupported Go standard library. Rebuild the same pinned gosu release with a
# supported Go toolchain so the runtime image does not inherit those CVEs.
ARG GOSU_COMMIT=6456aaa0f3c854d199d0f037f068eb97515b7513
RUN git init /src/gosu && \
    git -C /src/gosu fetch --depth 1 https://github.com/tianon/gosu.git "$GOSU_COMMIT" && \
    git -C /src/gosu checkout --detach FETCH_HEAD && \
    cd /src/gosu && \
    CGO_ENABLED=0 go build -trimpath -ldflags='-d -w' -o /usr/local/bin/gosu .

FROM mysql:8.0

# Install all published security updates and remove MySQL Shell, which is not
# used by this server image and otherwise brings an additional Python runtime.
RUN microdnf update -y && \
    microdnf remove -y mysql-shell && \
    microdnf clean all

COPY --from=gosu-builder /usr/local/bin/gosu /usr/local/bin/gosu
RUN gosu --version && gosu nobody true

# Setup the custom configuration
ADD .docker/sql/mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf
