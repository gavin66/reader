create table comic_follow
(
	id int auto_increment
		primary key,
	member_id int not null,
	comic varchar(128) null,
	comic_id varchar(255) null,
	chapter varchar(128) null,
	chapter_id varchar(255) null comment '章节id',
	platform varchar(32) null comment '平台,如:qq(腾讯漫画)',
	created_at timestamp null,
	updated_at timestamp null,
	constraint comics_store_member_id_comics_id_uindex
		unique (member_id, comic_id, platform)
)
comment '喜欢的漫画';

create table member
(
	id int auto_increment
		primary key,
	email varchar(64) null comment '邮箱',
	password varchar(255) null,
	roles varchar(255) null,
	name varchar(255) null,
	avatar varchar(255) null,
	introduction varchar(255) null,
	created_at timestamp null,
	updated_at timestamp null,
	constraint member_email_uindex
		unique (email)
);

create table novel_follow
(
	id int auto_increment
		primary key,
	member_id int not null,
	novel varchar(128) null,
	catalog_url varchar(255) null,
	chapter varchar(128) null,
	chapter_url varchar(255) null comment '当前章节 URL',
	created_at timestamp null,
	updated_at timestamp null,
	constraint novel_store_member_id_catalog_url_uindex
		unique (member_id, catalog_url)
)
comment '喜欢的小说';

