# Use the exact MySQL image from your compose file
FROM mysql:8.0

# Copy your initialization script so it runs on first boot
COPY ./init.sql /docker-entrypoint-initdb.d/01_init.sql

# Apply the exact authentication and charset commands from your compose file
CMD ["mysqld", "--default-authentication-plugin=mysql_native_password", "--character-set-server=utf8mb4", "--collation-server=utf8mb4_unicode_ci"]