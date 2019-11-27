create database course_select_system;
use course_select_system;
create user teacher identified with mysql_native_password by 'teacher';
create user student identified with mysql_native_password by 'student';
create user collegeadmin identified with mysql_native_password by 'collegeadmin';

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
  enroll_time time,
  graduate_time time,
  primary key(student_id)
);
create table instructor(
  instructor_id varchar(4),
  instructor_name varchar(100),
  hire_time time,
  quit_time time,
  primary key (instructor_id)
);
create table exam(
  exam_id varchar(4),
  week int,
  primary key (exam_id)
);
create table test(
  exam_id varchar(4) unique not null,
  style varchar(100),
  foreign key (exam_id) references exam(exam_id)
);
create table paper(
  exam_id varchar(4) unique not null,
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
  exam_id varchar(4),
  primary key (sec_id, year, course_id, semester),
  foreign key (course_id) references course(course_id),
  foreign key (exam_id) references exam(exam_id)
);
create table time_slot(
  time_slot_id varchar(10),
  start_time time,
  end_time time,
  day_of_week int,
  primary key (time_slot_id)
);
create table classroom(
  classroom_id varchar(10),
  capacity int,
  primary key (classroom_id)
);
create table application(
  appli_id int,
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
create table exam_time_place(
  specific_exam_id int auto_increment,
  exam_id varchar(4),
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


grant all privileges on `course_select_system`.* to collegeadmin;
grant select on `course_select_system`.* to student;
grant insert on `course_select_system`.`application` to student;
grant insert on `course_select_system`.`takes` to student;
grant delete on `course_select_system`.`takes` to student;
grant select on `course_select_system`.* to teacher;
grant update on `course_select_system`.`takes` to teacher;
grant update on `course_select_system`.`application` to teacher;
grant delete on `course_select_system`.`section` to teacher;
flush privileges;