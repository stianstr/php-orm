drop database if exists eonbit_orm_test;
create database eonbit_orm_test default charset utf8;
use eonbit_orm_test;

create table user (
 _id int unsigned primary key auto_increment,
 _created datetime, _created_by int unsigned,
 email varchar(255)
) engine=innodb;

insert into user values (1, now(), 2, 'alice@example.com');
insert into user values (2, now(), 2, 'bob@example.com');
