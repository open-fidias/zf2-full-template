--
-- Data Definition Language
--

START TRANSACTION;

CREATE TABLE acl_modules
(
    id integer NOT NULL,
    module character varying(50) NOT NULL,
    CONSTRAINT acl_modules_pkey PRIMARY KEY (id),
    CONSTRAINT acl_modules_module_key UNIQUE (module)
)
WITH (
    OIDS=FALSE
);

CREATE TABLE acl_controllers
(
    id integer NOT NULL,
    controller character varying(50) NOT NULL,
    CONSTRAINT acl_controllers_pkey PRIMARY KEY (id),
    CONSTRAINT acl_controllers_controller_key UNIQUE (controller)
)
WITH (
    OIDS=FALSE
);

CREATE TABLE acl_actions
(
    id integer NOT NULL,
    action character varying(50) NOT NULL,
    CONSTRAINT acl_actions_pkey PRIMARY KEY (id),
    CONSTRAINT acl_actions_action_key UNIQUE (action)
)
WITH (
    OIDS=FALSE
);

CREATE TABLE acl_roles
(
    id integer NOT NULL,
    role character varying(50) NOT NULL,
    parent integer,
    CONSTRAINT acl_roles_pkey PRIMARY KEY (id),
    CONSTRAINT acl_roles_parent_fkey FOREIGN KEY (parent)
    REFERENCES acl_roles (id) MATCH SIMPLE
    ON UPDATE NO ACTION ON DELETE NO ACTION,
    CONSTRAINT acl_roles_role_key UNIQUE (role)
)
WITH (
    OIDS=FALSE
);

CREATE TABLE acl_resources
(
    id integer NOT NULL,
    module_id integer NOT NULL,
    controller_id integer NOT NULL,
    action_id integer NOT NULL,
    CONSTRAINT acl_resources_pkey PRIMARY KEY (id),
    CONSTRAINT acl_resources_action_id_fkey FOREIGN KEY (action_id)
    REFERENCES acl_actions (id) MATCH SIMPLE
    ON UPDATE NO ACTION ON DELETE NO ACTION,
    CONSTRAINT acl_resources_controller_id_fkey FOREIGN KEY (controller_id)
    REFERENCES acl_controllers (id) MATCH SIMPLE
    ON UPDATE NO ACTION ON DELETE NO ACTION,
    CONSTRAINT acl_resources_module_id_fkey FOREIGN KEY (module_id)
    REFERENCES acl_modules (id) MATCH SIMPLE
    ON UPDATE NO ACTION ON DELETE NO ACTION,
    CONSTRAINT acl_resources_module_id_controller_id_action_id_key UNIQUE (module_id, controller_id, action_id)
)
WITH (
    OIDS=FALSE
);

CREATE TABLE acl_privileges
(
    resource_id integer NOT NULL,
    role_id integer NOT NULL,
    allow boolean NOT NULL DEFAULT false,
    CONSTRAINT acl_privileges_pkey PRIMARY KEY (resource_id, role_id),
    CONSTRAINT acl_privileges_resource_id_fkey FOREIGN KEY (resource_id)
    REFERENCES acl_resources (id) MATCH SIMPLE
    ON UPDATE NO ACTION ON DELETE NO ACTION,
    CONSTRAINT acl_privileges_role_id_fkey FOREIGN KEY (role_id)
    REFERENCES acl_roles (id) MATCH SIMPLE
    ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
    OIDS=FALSE
);

CREATE TABLE usuario
(
    id bigint NOT NULL,
    login character varying(70) NOT NULL,
    senha character varying(100) NOT NULL,
    role_id integer NOT NULL,
    CONSTRAINT usuario_pk PRIMARY KEY (id),
    CONSTRAINT usuario_role_fkey FOREIGN KEY (role_id)
    REFERENCES acl_roles (id) MATCH SIMPLE
    ON UPDATE NO ACTION ON DELETE NO ACTION
)
WITH (
    OIDS=FALSE
);

COMMIT;
