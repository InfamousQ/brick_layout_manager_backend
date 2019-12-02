#!/bin/bash
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    CREATE USER bl_admin WITH ENCRYPTED PASSWORD 'test';
    CREATE DATABASE lmanager;
    GRANT ALL PRIVILEGES ON DATABASE lmanager TO bl_admin;
EOSQL