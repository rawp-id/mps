PRAGMA foreign_keys = OFF;

BEGIN TRANSACTION;

CREATE TABLE
    IF NOT EXISTS "migrations" (
        "id" integer primary key autoincrement not null,
        "migration" varchar not null,
        "batch" integer not null
    );

CREATE TABLE
    IF NOT EXISTS "proccess_groups" (
        "id" integer primary key autoincrement not null,
        "created_at" datetime,
        "updated_at" datetime
    );

CREATE TABLE
    IF NOT EXISTS "users" (
        "id" integer primary key autoincrement not null,
        "name" varchar not null,
        "email" varchar not null,
        "email_verified_at" datetime,
        "password" varchar not null,
        "remember_token" varchar,
        "created_at" datetime,
        "updated_at" datetime
    );

CREATE TABLE
    IF NOT EXISTS "password_reset_tokens" (
        "email" varchar not null,
        "token" varchar not null,
        "created_at" datetime,
        primary key ("email")
    );

CREATE TABLE
    IF NOT EXISTS "sessions" (
        "id" varchar not null,
        "user_id" integer,
        "ip_address" varchar,
        "user_agent" text,
        "payload" text not null,
        "last_activity" integer not null,
        primary key ("id")
    );

CREATE TABLE
    IF NOT EXISTS "cache" (
        "key" varchar not null,
        "value" text not null,
        "expiration" integer not null,
        primary key ("key")
    );

CREATE TABLE
    IF NOT EXISTS "cache_locks" (
        "key" varchar not null,
        "owner" varchar not null,
        "expiration" integer not null,
        primary key ("key")
    );

CREATE TABLE
    IF NOT EXISTS "jobs" (
        "id" integer primary key autoincrement not null,
        "queue" varchar not null,
        "payload" text not null,
        "attempts" integer not null,
        "reserved_at" integer,
        "available_at" integer not null,
        "created_at" integer not null
    );

CREATE TABLE
    IF NOT EXISTS "job_batches" (
        "id" varchar not null,
        "name" varchar not null,
        "total_jobs" integer not null,
        "pending_jobs" integer not null,
        "failed_jobs" integer not null,
        "failed_job_ids" text not null,
        "options" text,
        "cancelled_at" integer,
        "created_at" integer not null,
        "finished_at" integer,
        primary key ("id")
    );

CREATE TABLE
    IF NOT EXISTS "failed_jobs" (
        "id" integer primary key autoincrement not null,
        "uuid" varchar not null,
        "connection" text not null,
        "queue" text not null,
        "payload" text not null,
        "exception" text not null,
        "failed_at" datetime not null default CURRENT_TIMESTAMP
    );

CREATE TABLE
    IF NOT EXISTS "machines" (
        "id" integer primary key autoincrement not null,
        "name" varchar not null,
        "capacity" integer not null default '0',
        "created_at" datetime,
        "updated_at" datetime
    );

CREATE TABLE
    IF NOT EXISTS "processes" (
        "id" integer primary key autoincrement not null,
        "code" varchar not null,
        "name" varchar not null,
        "speed" integer not null default '0',
        "created_at" datetime,
        "updated_at" datetime
    );

CREATE TABLE
    IF NOT EXISTS "operations" (
        "id" integer primary key autoincrement not null,
        "process_id" integer not null,
        "machine_id" integer not null,
        "code" varchar,
        "name" varchar,
        "duration" integer not null default '0',
        "is_setting" tinyint (1) not null default '0',
        "created_at" datetime,
        "updated_at" datetime,
        foreign key ("process_id") references "processes" ("id") on delete cascade,
        foreign key ("machine_id") references "machines" ("id") on delete cascade
    );

CREATE TABLE
    IF NOT EXISTS "products" (
        "id" integer primary key autoincrement not null,
        "code" varchar not null,
        "name" varchar not null,
        "process_details" varchar,
        "is_completed" tinyint (1) not null default '0',
        "stock" integer not null default '0',
        "created_at" datetime,
        "updated_at" datetime
    );

CREATE TABLE
    IF NOT EXISTS "shifts" (
        "id" integer primary key autoincrement not null,
        "machine_id" integer not null,
        "name" varchar not null,
        "start_time" time not null,
        "end_time" time not null,
        "is_active" tinyint (1) not null,
        "created_at" datetime,
        "updated_at" datetime,
        foreign key ("machine_id") references "machines" ("id") on delete cascade
    );

CREATE TABLE
    IF NOT EXISTS "product_components" (
        "id" integer primary key autoincrement not null,
        "parent_product_id" integer not null,
        "component_product_id" integer not null,
        "quantity" numeric not null default '1',
        "unit" varchar not null default 'pcs',
        "created_at" datetime,
        "updated_at" datetime,
        foreign key ("parent_product_id") references "products" ("id") on delete cascade,
        foreign key ("component_product_id") references "products" ("id") on delete cascade
    );

CREATE TABLE
    IF NOT EXISTS "cos" (
        "id" integer primary key autoincrement not null,
        "code" varchar not null,
        "name" varchar not null,
        "description" varchar,
        "co_user" varchar,
        "process_details" varchar,
        "is_completed" tinyint (1) not null default '0',
        "status" varchar not null default 'pending',
        "remarks" varchar,
        "draft" tinyint (1) not null default '0',
        "created_at" datetime,
        "updated_at" datetime
    );

CREATE TABLE
    IF NOT EXISTS "co_products" (
        "id" integer primary key autoincrement not null,
        "co_id" integer not null,
        "product_id" integer not null,
        "shipment_date" date,
        "created_at" datetime,
        "updated_at" datetime,
        foreign key ("co_id") references "cos" ("id") on delete cascade,
        foreign key ("product_id") references "products" ("id") on delete cascade
    );

CREATE TABLE
    IF NOT EXISTS "schedules" (
        "id" integer primary key autoincrement not null,
        "co_product_id" integer,
        "process_id" integer,
        "machine_id" integer,
        "operation_id" integer,
        "previous_schedule_id" integer,
        "process_dependency_id" integer,
        "is_start_process" tinyint (1) not null default '0',
        "is_final_process" tinyint (1) not null default '0',
        "quantity" integer not null default '0',
        "plan_speed" integer not null default '0',
        "conversion_value" numeric,
        "plan_duration" integer not null default '0',
        "start_time" datetime,
        "end_time" datetime,
        "is_completed" tinyint (1) not null default '0',
        "shift_id" integer,
        "is_overtime" tinyint (1) not null default '0',
        "adjusted_start" datetime,
        "adjusted_end" datetime,
        "created_at" datetime,
        "updated_at" datetime,
        foreign key ("co_product_id") references "co_products" ("id") on delete cascade,
        foreign key ("process_id") references "processes" ("id") on delete cascade,
        foreign key ("machine_id") references "machines" ("id") on delete cascade,
        foreign key ("operation_id") references "operations" ("id") on delete cascade,
        foreign key ("previous_schedule_id") references "schedules" ("id") on delete cascade,
        foreign key ("process_dependency_id") references "schedules" ("id") on delete cascade
    );

CREATE TABLE
    IF NOT EXISTS "plans" (
        "id" integer primary key autoincrement not null,
        "name" varchar not null,
        "description" varchar,
        "product_id" integer,
        "co_id" integer,
        "is_applied" tinyint (1) not null default '0',
        "start_date" datetime,
        "created_at" datetime,
        "updated_at" datetime,
        foreign key ("product_id") references "products" ("id") on delete cascade,
        foreign key ("co_id") references "cos" ("id") on delete cascade
    );

CREATE TABLE
    IF NOT EXISTS "simulate_schedules" (
        "id" integer primary key autoincrement not null,
        "plan_id" integer not null,
        "co_product_id" integer,
        "process_id" integer,
        "machine_id" integer,
        "operation_id" integer,
        "previous_schedule_id" integer,
        "process_dependency_id" integer,
        "is_start_process" tinyint (1) not null default '0',
        "is_final_process" tinyint (1) not null default '0',
        "quantity" integer not null default '0',
        "plan_speed" integer not null default '0',
        "conversion_value" numeric,
        "plan_duration" integer not null default '0',
        "duration" integer not null default '0',
        "start_time" datetime,
        "end_time" datetime,
        "shift_id" integer,
        "is_overtime" tinyint (1) not null default '0',
        "adjusted_start" datetime,
        "adjusted_end" datetime,
        "created_at" datetime,
        "updated_at" datetime,
        foreign key ("plan_id") references "plans" ("id") on delete cascade,
        foreign key ("co_product_id") references "co_products" ("id") on delete cascade,
        foreign key ("process_id") references "processes" ("id") on delete cascade,
        foreign key ("machine_id") references "machines" ("id") on delete cascade,
        foreign key ("operation_id") references "operations" ("id") on delete cascade,
        foreign key ("previous_schedule_id") references "schedules" ("id") on delete cascade,
        foreign key ("process_dependency_id") references "schedules" ("id") on delete cascade
    );

CREATE TABLE
    IF NOT EXISTS "plan_product_cos" (
        "id" integer primary key autoincrement not null,
        "plan_id" integer not null,
        "co_product_id" integer,
        "shipment_date" datetime,
        "created_at" datetime,
        "updated_at" datetime,
        foreign key ("plan_id") references "plans" ("id") on delete cascade,
        foreign key ("co_product_id") references "co_products" ("id") on delete cascade
    );

CREATE TABLE
    IF NOT EXISTS "personal_access_tokens" (
        "id" integer primary key autoincrement not null,
        "tokenable_type" varchar not null,
        "tokenable_id" integer not null,
        "name" text not null,
        "token" varchar not null,
        "abilities" text,
        "last_used_at" datetime,
        "expires_at" datetime,
        "created_at" datetime,
        "updated_at" datetime
    );

CREATE TABLE
    IF NOT EXISTS "components" (
        "id" integer primary key autoincrement not null,
        "code" varchar,
        "name" varchar,
        "description" varchar,
        "unit" varchar,
        "stock" numeric not null default '0',
        "created_at" datetime,
        "updated_at" datetime
    );

CREATE TABLE
    IF NOT EXISTS "b_o_m_s" (
        "id" integer primary key autoincrement not null,
        "product_id" integer not null,
        "component_id" integer not null,
        "quantity" integer not null default '1',
        "unit" varchar not null default 'pcs',
        "usage_type" varchar check ("usage_type" in ('consumable', 'usage_based')) not null default 'consumable',
        "created_at" datetime,
        "updated_at" datetime,
        foreign key ("product_id") references "products" ("id") on delete cascade,
        foreign key ("component_id") references "components" ("id") on delete cascade
    );

CREATE TABLE
    IF NOT EXISTS "component_products" (
        "id" integer primary key autoincrement not null,
        "product_id" integer not null,
        "code" varchar not null,
        "name" varchar not null,
        "unit" varchar,
        "quantity" integer,
        "created_at" datetime,
        "updated_at" datetime,
        foreign key ("product_id") references "products" ("id") on delete cascade
    );

CREATE TABLE
    IF NOT EXISTS "process_products" (
        "id" integer primary key autoincrement not null,
        "product_id" integer not null,
        "component_product_id" integer,
        "operation_id" integer not null,
        "type" varchar check ("type" in ('operation', 'setting')) not null,
        "notes" text,
        "created_at" datetime,
        "updated_at" datetime,
        foreign key ("product_id") references "products" ("id") on delete cascade,
        foreign key ("component_product_id") references "component_products" ("id") on delete cascade,
        foreign key ("operation_id") references "operations" ("id") on delete cascade
    );

CREATE TABLE
    IF NOT EXISTS "assemblies" (
        "id" integer primary key autoincrement not null,
        "is_combined" tinyint (1) not null,
        "notes" text,
        "created_at" datetime,
        "updated_at" datetime
    );

CREATE TABLE
    IF NOT EXISTS "assembly_processes" (
        "id" integer primary key autoincrement not null,
        "assembly_id" integer not null,
        "process_product_id" integer not null,
        "created_at" datetime,
        "updated_at" datetime,
        foreign key ("assembly_id") references "assemblies" ("id") on delete cascade,
        foreign key ("process_product_id") references "process_products" ("id") on delete cascade
    );

CREATE TABLE
    IF NOT EXISTS "groups" (
        "id" integer primary key autoincrement not null,
        "name" varchar not null,
        "description" varchar,
        "created_at" datetime,
        "updated_at" datetime
    );

CREATE TABLE
    IF NOT EXISTS "grouping_proccesses" (
        "id" integer primary key autoincrement not null,
        "group_id" integer not null,
        "process_product_id" integer not null,
        "created_at" datetime,
        "updated_at" datetime,
        foreign key ("group_id") references "groups" ("id") on delete cascade,
        foreign key ("process_product_id") references "process_products" ("id") on delete cascade
    );

CREATE TABLE
    IF NOT EXISTS "calender_days" (
        "id" integer primary key autoincrement not null,
        "date" date not null,
        "is_workday" tinyint (1) not null default '1',
        "description" varchar,
        "created_at" datetime,
        "updated_at" datetime
    );

CREATE TABLE
    IF NOT EXISTS "overtimes" (
        "id" integer primary key autoincrement not null,
        "machine_id" integer not null,
        "date" date not null,
        "start_time" time not null,
        "end_time" time not null,
        "reason" varchar,
        "created_at" datetime,
        "updated_at" datetime
    );

CREATE TABLE
    IF NOT EXISTS "downtimes" (
        "id" integer primary key autoincrement not null,
        "machine_id" integer not null,
        "start_datetime" datetime not null,
        "end_datetime" datetime not null,
        "reason" varchar,
        "created_at" datetime,
        "updated_at" datetime
    );

DELETE FROM sqlite_sequence;

INSERT INTO
    sqlite_sequence
VALUES
    ('migrations', 620);

CREATE UNIQUE INDEX "users_email_unique" on "users" ("email");

CREATE INDEX "sessions_user_id_index" on "sessions" ("user_id");

CREATE INDEX "sessions_last_activity_index" on "sessions" ("last_activity");

CREATE INDEX "jobs_queue_index" on "jobs" ("queue");

CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs" ("uuid");

CREATE UNIQUE INDEX "processes_code_unique" on "processes" ("code");

CREATE UNIQUE INDEX "operations_code_unique" on "operations" ("code");

CREATE UNIQUE INDEX "products_code_unique" on "products" ("code");

CREATE INDEX "personal_access_tokens_tokenable_type_tokenable_id_index" on "personal_access_tokens" ("tokenable_type", "tokenable_id");

CREATE UNIQUE INDEX "personal_access_tokens_token_unique" on "personal_access_tokens" ("token");

CREATE UNIQUE INDEX "components_code_unique" on "components" ("code");

CREATE UNIQUE INDEX "component_products_code_unique" on "component_products" ("code");

CREATE UNIQUE INDEX "calender_days_date_unique" on "calender_days" ("date");

COMMIT;