<?php

require_once 'ConnectSQL.php';

$create_section = "create table section(
  sec_id int,
  year int,
  semester int,
  start_week int,
  end_week int,
  expect_people_number int,
  selected_people_number int,
  course_id varchar(4),
  primary key (sec_id, year, course_id, semester),
  foreign key (course_id) references course(course_id)
)";
$create_course = "create table course(
  course_id varchar(4),
  course_name varchar(100),
  course_credit int,
  class_hours int,
  primary key (course_id)
)";
$create_student = "create table student(
  student_id varchar(4),
  student_name varchar(100),
  total_credit int,
  gpa double,
  enroll_time time,
  graduate_time time,
  primary key(student_id)
)";
$create_instructor = "create table instructor(
  instructor_id varchar(4),
  instructor_name varchar(100),
  hire_time time,
  quit_time time,
  primary key (instructor_id)
)";
$create_exam = "create table exam(
  exam_id varchar(4),
  week int,
  sec_id int,
  course_id varchar(4),
  semester int,
  year int,
  foreign key (sec_id, year, course_id, semester) 
    references section(sec_id, year, course_id, semester),
  primary key (exam_id)
)";
$create_test = "create table test(
  exam_id varchar(4),
  style varchar(100),
  foreign key (exam_id) references exam(exam_id)
)";
$create_paper = "create table paper(
  exam_id varchar(4),
  demand varchar(100),
  foreign key (exam_id) references exam(exam_id)
)";
$create_time_slot = "create table time_slot(
  time_slot_id varchar(4),
  start_time time,
  end_time time,
  day_of_week int,
  primary key (time_slot_id)
)";
$create_classroom = "create table classroom(
  classroom_id varchar(4),
  capacity int,
  primary key (classroom_id)
)";
$create_application = "create table application(
  appli_id varchar(4),
  appii_status varchar(20),
  appli_content text,
  appli_time time,
  student_id varchar(4),
  sec_id int,
  course_id varchar(4),
  semester int,
  year int,
  primary key (appli_id),
  foreign key (student_id) references student(student_id),
  foreign key (sec_id, year, course_id, semester) 
    references section(sec_id, year, course_id, semester)
)";
$create_class_time_place = "create table class_time_place(
  time_slot_id varchar(4),
  classroom_id varchar(4),
  sec_id int,
  course_id varchar(4),
  semester int,
  year int,
  foreign key (sec_id, year, course_id, semester) 
    references section(sec_id, year, course_id, semester),
  foreign key (time_slot_id) references time_slot(time_slot_id),
  foreign key (classroom_id) references classroom(classroom_id)
)";
$create_exam_time_place = "create table exam_time_place(
  exam_id varchar(4),
  time_slot_id varchar(4),
  classroom_id varchar(4),
  foreign key (exam_id) references exam(exam_id),
  foreign key (time_slot_id) references time_slot(time_slot_id),
  foreign key (classroom_id) references classroom(classroom_id)
)";
$create_teaches = "create table teaches(
  instructor_id varchar(4),
  sec_id int,
  course_id varchar(4),
  semester int,
  year int,
  foreign key (sec_id, year, course_id, semester) 
    references section(sec_id, year, course_id, semester),
  foreign key (instructor_id) references instructor(instructor_id)
)";
$create_take_exam = "create table take_exam(
  student_id varchar(4),
  exam_id varchar(4),
  foreign key (student_id) references student(student_id),
  foreign key (exam_id) references exam(exam_id)
)";
$create_takes = "create table takes(
  student_id varchar(4),
  sec_id int,
  course_id varchar(4),
  semester int,
  year int,
  foreign key (sec_id, year, course_id, semester) 
    references section(sec_id, year, course_id, semester),
  foreign key (student_id) references student(student_id)
)";

$create_calls = [$create_course,$create_student,$create_classroom,$create_time_slot,$create_instructor,
    $create_section,$create_exam,$create_test,$create_paper,$create_application,
    $create_class_time_place,$create_exam_time_place,$create_takes,$create_take_exam,$create_teaches];

for($i = 0; $i < sizeof($create_calls); $i++){
    $call = $create_calls[$i];
    $res = $conn->query($call);
    if(!$res){
        echo $conn->error."<br>";
    }
}

