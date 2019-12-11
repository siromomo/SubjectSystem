alter table time_slot modify column start_time varchar(8) not null;
alter table time_slot modify column end_time varchar(8) not null;
alter table time_slot modify column day_of_week int(11) not null;
alter table application modify column student_id varchar(4) not null;
alter table application modify column course_id varchar(4) not null;
alter table course modify column course_name varchar(100) not null;
alter table student modify column student_name varchar(100) not null;
alter table instructor modify column instructor_name varchar(100) not null;