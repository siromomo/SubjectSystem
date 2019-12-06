create database course_select_system;
use course_select_system;
create user teacher identified with mysql_native_password by 'teacher';
create user student identified with mysql_native_password by 'student';
-- create user collegeadmin identified with mysql_native_password by 'collegeadmin';

create table course(
  course_id varchar(4),
  course_name varchar(100),
  credit int,
  class_hours int,
  primary key (course_id)
);
create table student(
  student_id varchar(4),
  student_name varchar(100),
  total_credit int,
  gpa double,
  enroll_time varchar(10),
  graduate_time varchar(10),
  primary key(student_id)
);
create table instructor(
  instructor_id varchar(4),
  instructor_name varchar(100),
  hire_time varchar(10),
  quit_time varchar(10),
  primary key (instructor_id)
);
create table exam(
  exam_id int auto_increment,
  week int,
  primary key (exam_id)
);
create table test(
  exam_id int unique not null,
  style varchar(100),
  foreign key (exam_id) references exam(exam_id)
);
create table paper(
  exam_id int unique not null,
  demand varchar(100),
  foreign key (exam_id) references exam(exam_id)
);
create table section(
  sec_id int,
  semester varchar(10),
  year int,
  start_week int,
  end_week int,
  number int,
  selected_num int,
  course_id varchar(4),
  exam_id int,
  primary key (sec_id, year, course_id, semester),
  foreign key (course_id) references course(course_id),
  foreign key (exam_id) references exam(exam_id)
);
create table time_slot(
  time_slot_id varchar(10),
  start_time varchar(8),
  end_time varchar(8),
  day_of_week int,
  primary key (time_slot_id)
);
create table classroom(
  classroom_id varchar(10),
  capacity int,
  primary key (classroom_id)
);
create table application(
  appli_id int auto_increment,
  appii_status varchar(20),
  appli_content text,
  appli_time time,
  student_id varchar(4),
  sec_id int,
  course_id varchar(4),
  semester varchar(10),
  year int,
  primary key (appli_id),
  foreign key (student_id) references student(student_id),
  foreign key (sec_id,year, course_id, semester) 
    references section(sec_id,year, course_id, semester)
);
create table class_time_place(
  time_slot_id varchar(10),
  classroom_id varchar(10),
  sec_id int,
  course_id varchar(4),
  semester varchar(10),
  year int,
  foreign key (sec_id,year, course_id, semester)
    references section(sec_id, year, course_id, semester),
  foreign key (time_slot_id) references time_slot(time_slot_id),
  foreign key (classroom_id) references classroom(classroom_id)
);
create table exam_time(
  exam_id int,
  time_slot_id varchar(10),
  primary key (exam_id,time_slot_id),
  foreign key (exam_id) references exam(exam_id),
  foreign key (time_slot_id) references time_slot(time_slot_id)
);
create table exam_time_place(
  specific_exam_id int auto_increment,
  exam_id int,
  time_slot_id varchar(10),
  classroom_id varchar(10),
  primary key(specific_exam_id),
  foreign key (exam_id) references test(exam_id),
  foreign key (time_slot_id) references time_slot(time_slot_id),
  foreign key (classroom_id) references classroom(classroom_id)
);
create table teaches(
  instructor_id varchar(4),
  sec_id int,
  course_id varchar(4),
  semester varchar(10),
  year int,
  foreign key (sec_id, year, course_id, semester)
    references section(sec_id, year, course_id, semester),
  foreign key (instructor_id) references instructor(instructor_id)
);
create table take_exam(
  student_id varchar(4),
  specific_exam_id int,
  foreign key (student_id) references student(student_id),
  foreign key (specific_exam_id) references exam_time_place(specific_exam_id)
);
create table takes(
  student_id varchar(4),
  sec_id int,
  course_id varchar(4),
  semester varchar(10),
  year int,
  grade varchar(2),
  foreign key (sec_id, year, course_id, semester)
    references section(sec_id, year, course_id, semester),
  foreign key (student_id) references student(student_id)
);
create table drops(
  student_id varchar(4),
  sec_id int,
  course_id varchar(4),
  semester varchar(10),
  year int,
  foreign key (sec_id, year, course_id, semester)
    references section(sec_id, year, course_id, semester),
  foreign key (student_id) references student(student_id)
);


grant all privileges on `course_select_system`.* to collegeadmin WITH GRANT OPTION;

-- grant select on `course_select_system`.* to student;
grant insert on `course_select_system`.`application` to student;
grant insert on `course_select_system`.`drops` to student;
grant insert on `course_select_system`.`takes` to student;
grant delete on `course_select_system`.`takes` to student;
grant update on `course_select_system`.`section` to student;

grant select on `course_select_system`.* to teacher;
grant update on `course_select_system`.`takes` to teacher;
grant update on `course_select_system`.`application` to teacher;
grant update on `course_select_system`.`section` to teacher;
grant update on `course_select_system`.`student` to teacher;

-- use mysql;
-- grant all privileges on user to collegeadmin;
-- grant create on user to collegeadmin;
flush privileges;

alter table takes add unique(student_id, sec_id, course_id, semester, year);
alter table exam_time_place add unique(time_slot_id, classroom_id);
alter table class_time_place add unique(time_slot_id, classroom_id, semester, year);
alter table time_slot add unique(start_time,end_time,day_of_week);
alter table application modify column `appli_time` datetime default CURRENT_TIMESTAMP;
alter table application add unique(student_id, sec_id, course_id, semester, year);
grant update on `course_select_system`.`section` to teacher;
grant insert on `course_select_system`.`takes` to teacher;
