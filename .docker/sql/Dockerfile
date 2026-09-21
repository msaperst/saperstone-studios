FROM mysql:8.0

# Install all published security updates and remove MySQL Shell, which is not
# used by this server image and otherwise brings an additional Python runtime.
RUN microdnf update -y && \
    microdnf remove -y mysql-shell && \
    microdnf clean all

# Setup the custom configuration
ADD .docker/sql/mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf
