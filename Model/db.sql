/*==============================================================*/
/* nom de sgbd :  mysql 5.0                                     */
/* date de création :  26/05/2014 15:03:57                      */
/*==============================================================*/


drop table if exists groups;

drop table if exists project;

drop table if exists role;

drop table if exists subtest;

drop table if exists test;

drop table if exists users;

drop table if exists users_groups;

drop table if exists users_test;

/*==============================================================*/
/* table : groups                                                */
/*==============================================================*/
create table groups
(
   name                 varchar(20) not null,
   primary key (name)
);

/*==============================================================*/
/* table : project                                              */
/*==============================================================*/
create table project
(
   id                   int not null auto_increment,
   username             varchar(20) not null,
   name                 varchar(20) not null,
   enabled              bool not null,
   due_date             datetime,
   target_group			varchar(20) not null,
   primary key (id)
);

/*==============================================================*/
/* table : role                                                 */
/*==============================================================*/
create table role
(
   role                 varchar(20) not null,
   primary key (role)
);

/*==============================================================*/
/* table : subtest                                              */
/*==============================================================*/
create table subtest
(
   project_id           int not null,
   test_name            varchar(20) not null,
   name                 varchar(20) not null,
   weight               int not null,
   kind                 varchar(50) not null,
   primary key (project_id, test_name, name)
);

/*==============================================================*/
/* table : test                                                 */
/*==============================================================*/
create table test
(
   project_id           int not null,
   name                 varchar(20) not null,
   description          varchar(140),
   primary key (project_id, name)
);

/*==============================================================*/
/* table : users                                                 */
/*==============================================================*/
create table users
(
   username             varchar(20) not null,
   hash                 varchar(100) not null,
   firstname            varchar(20) not null,
   lastname             varchar(20) not null,
   mail                 varchar(30) not null,
   role                 varchar(20) not null,
   primary key (username)
);

/*==============================================================*/
/* table : users_groups                                           */
/*==============================================================*/
create table users_groups
(
   group_name           varchar(20) not null,
   username             varchar(20) not null,
   primary key (group_name, username)
);

/*==============================================================*/
/* table : users_test                                            */
/*==============================================================*/
create table users_test
(
   project_id           int not null,
   test_name            varchar(20) not null,
   subtest_name         varchar(20) not null,
   username             varchar(20) not null,
   status               int,
   errors               text,
   primary key (project_id, test_name, subtest_name, username)
);

alter table project add constraint fk_users_project foreign key (username)
      references users (username) on delete restrict on update restrict;

alter table project add constraint fk_groups_project foreign key (target_group)
      references groups (name) on delete restrict on update restrict;

alter table users add constraint fk_role_users foreign key (role)
      references role (role) on delete restrict on update restrict;

alter table subtest add constraint fk_test_sub_test foreign key (project_id, test_name)
      references test (project_id, name) on delete restrict on update restrict;

alter table test add constraint fk_project_test foreign key (project_id)
      references project (id) on delete restrict on update restrict;

alter table users_groups add constraint fk_users_groups foreign key (group_name)
      references groups (name) on delete restrict on update restrict;

alter table users_groups add constraint fk_users_groups2 foreign key (username)
      references users (username) on delete restrict on update restrict;

alter table users_test add constraint fk_users_test foreign key (project_id, test_name, subtest_name)
      references subtest (project_id, test_name, name) on delete restrict on update restrict;

alter table users_test add constraint fk_users_test2 foreign key (username)
      references users (username) on delete restrict on update restrict;

