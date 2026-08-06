<?php
include 'dbConnect.php';
echo "Creating Tables";


function runquery($connection, $query, $tablename){
  echo "<br>Creating " . $tablename . " table...<br>";
  try {
    $stmt = $connection->query($query);
    echo "  Succesfully<br>";
  } catch (mysqli_sql_exception $e) {
    echo "  Error creating " . $tablename . " table..." . $e->getMessage() . "<br>";
  }
}
function AddClassrooms($connection,$classroom){
  foreach($classroom as $room){
    $query = "INSERT INTO CLASSROOM (FLOOR_NUMBER,ROOM_NUMBER) VALUES('{$room[0]}',{$room[1]})";
    $result = $connection -> query($query);
    if($connection->affected_rows==1){
      echo "Added classroom as ". $room[0] .' - ' .$room[1] .'<br>';
    }
    else{
      echo "Couldn't add classroom as ". $room[0] .' - ' .$room[1] .'<br>';
    }
  }
}
function AddWeekdays($connection,$weekdays){
  foreach($weekdays as $weekday){
    $query = "INSERT INTO WEEKDAY (WEEKDAY) VALUES('{$weekday}')";
    $result = $connection -> query($query);
    if($connection->affected_rows==1){
      echo "Added Weekday as ". $weekday .'<br>';
    }
    else{
      echo "Couldn't add Weekday as ". $weekday .'<br>';
    }
  }
}
function AddYears($connection,$years){
  foreach($years as $year){
    $query = "INSERT INTO YEAR (NAME) VALUES('{$year}')";
    $result = $connection -> query($query);
    if($connection->affected_rows==1){
      echo "Added Year as ". $year .'<br>';
    }
    else{
      echo "Couldn't add Year as ". $year .'<br>';
    }
  }
}

runquery($conn, "
      CREATE TABLE IF NOT EXISTS CLASSROOM(
        CLASSROOM_ID INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
        FLOOR_NUMBER VARCHAR(15) NOT NULL,
        ROOM_NUMBER VARCHAR(15) NOT NULL UNIQUE,
        CAPACITY INT
      );
    ", "Classroom");

runquery($conn, "
      CREATE TABLE IF NOT EXISTS WEEKDAY(
        WEEKDAY ENUM('MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY') PRIMARY KEY
      );
    ", "Weekday");

runquery($conn, "
      CREATE TABLE IF NOT EXISTS TIMESLOT(
        SLOT_ID INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
        START_TIME TIME NOT NULL,
        END_TIME TIME NOT NULL,
        SLOT_TYPE ENUM('BREAK', 'LECTURE', 'PRACTICAL') NOT NULL DEFAULT 'LECTURE'
      );
    ", "Timeslot");

runquery($conn, "
      CREATE TABLE IF NOT EXISTS USER(
        USERNAME VARCHAR(31) NOT NULL PRIMARY KEY,
        PASSWORD VARCHAR(255) NOT NULL,
        LOGIN_TIME DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        LOGIN_IP VARCHAR(15)
      );
    ", "User");

runquery($conn, "
      CREATE TABLE IF NOT EXISTS DEPARTMENT(
        DEPARTMENT_ID INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
        USERNAME VARCHAR(31),
        LONG_NAME VARCHAR(63) NOT NULL,
        SHORT_NAME VARCHAR(31) NOT NULL,

        CONSTRAINT fk_usrnm_deptTbl
        FOREIGN KEY (USERNAME) 
        REFERENCES USER(USERNAME)
        ON DELETE CASCADE
        ON UPDATE CASCADE
      );
    ", "Department");

runquery($conn, "
      CREATE TABLE IF NOT EXISTS TEACHER(
        TEACHER_ID INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
        DEPARTMENT_ID INT  NOT NULL,
        FIRST_NAME VARCHAR(15) NOT NULL,
        LAST_NAME VARCHAR(15) NOT NULL,
        ISPARTTIME BOOLEAN NOT NULL DEFAULT(0),

        CONSTRAINT fk_deptId_tchrTbl
        FOREIGN KEY (DEPARTMENT_ID) 
        REFERENCES DEPARTMENT(DEPARTMENT_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE
      );
    ", "Teacher");

runquery($conn, "
      CREATE TABLE IF NOT EXISTS AVAILABILITY(
        TEACHER_ID INT NOT NULL,
        SLOT_ID INT NOT NULL,
        WEEKDAY ENUM('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY') NOT NULL,
        STATUS ENUM('AVAILABLE','ALLOTED') DEFAULT 'AVAILABLE',

        PRIMARY KEY(TEACHER_ID, SLOT_ID),

        CONSTRAINT fk_tchrId_avlbtTbl
        FOREIGN KEY (TEACHER_ID) 
        REFERENCES TEACHER(TEACHER_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

        CONSTRAINT fk_slotId_avlbtTbl
        FOREIGN KEY (SLOT_ID) 
        REFERENCES TIMESLOT(SLOT_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

        CONSTRAINT fk_wkdy_avlbtTbl
        FOREIGN KEY (WEEKDAY) 
        REFERENCES WEEKDAY(WEEKDAY)
        ON DELETE CASCADE
        ON UPDATE CASCADE
      );
    ", "Availability");

runquery($conn, "
      CREATE TABLE IF NOT EXISTS PROGRAMME(
        PROGRAMME_ID INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
        DEPARTMENT_ID INT,
        LONG_NAME VARCHAR(63) NOT NULL,
        SHORT_NAME VARCHAR(15) NOT NULL,
        DIVISION_COUNT INT NOT NULL DEFAULT(0),

        CONSTRAINT fk_deptId_pgrmTbl 
        FOREIGN KEY (DEPARTMENT_ID) 
        REFERENCES DEPARTMENT(DEPARTMENT_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE
      );
    ", "Programme");

runquery($conn, "
      CREATE TABLE IF NOT EXISTS YEAR(
        YEAR_ID INT PRIMARY KEY AUTO_INCREMENT,
        PROGRAMME_ID INT,
        NAME ENUM('FIRST YEAR', 'SECOND YEAR', 'THIRD YEAR'),

        CONSTRAINT fk_pgrmId_yrTbl 
        FOREIGN KEY (PROGRAMME_ID) 
        REFERENCES PROGRAMME(PROGRAMME_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE
      );
    ", "Year");

runquery($conn, "
      CREATE TABLE IF NOT EXISTS DIVISION(
        DIVISION_ID INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
        YEAR_ID INT,
        NAME CHAR NOT NULL,
        STUDENT_COUNT INT NOT NULL DEFAULT(0),

        CONSTRAINT fk_yrId_dvsnTbl 
        FOREIGN KEY (YEAR_ID) 
        REFERENCES YEAR(YEAR_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE
      );
    ", "Division");

runquery($conn, "
      CREATE TABLE IF NOT EXISTS COURSE(
        COURSE_ID INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
        PROGRAMME_ID INT,
        LONG_NAME VARCHAR(63) NOT NULL,
        SHORT_NAME VARCHAR(15) NOT NULL,
        WEEKLY_LECTURES INT NOT NULL,
        ISPRACTICAL BOOLEAN DEFAULT 0,

        CONSTRAINT fk_pgrmId_crseTbl 
        FOREIGN KEY (PROGRAMME_ID) 
        REFERENCES PROGRAMME(PROGRAMME_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE
      );
    ", "Course");

runquery($conn, "
      CREATE TABLE IF NOT EXISTS TAUGHT_TO(
        COURSE_ID INT,
        DIVISION_ID INT,

        PRIMARY KEY(COURSE_ID, DIVISION_ID),

        CONSTRAINT fk_crseId_tghtToTbl 
        FOREIGN KEY (COURSE_ID) 
        REFERENCES COURSE(COURSE_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
        
        CONSTRAINT fk_dvsnId_tghtToTbl 
        FOREIGN KEY (DIVISION_ID) 
        REFERENCES DIVISION(DIVISION_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE
      );
    ", "Taught_To");

runquery($conn, "
      CREATE TABLE IF NOT EXISTS GIVEN(
        SLOT_ID INT,
        WEEKDAY ENUM('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY'),
        CLASSROOM_ID INT,
        DIVISION_ID INT,

        PRIMARY KEY(SLOT_ID, CLASSROOM_ID, DIVISION_ID),

        CONSTRAINT fk_slotId_gvenTbl
        FOREIGN KEY (SLOT_ID) 
        REFERENCES TIMESLOT(SLOT_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

        CONSTRAINT fk_clsrmId_gvenTbl
        FOREIGN KEY (CLASSROOM_ID) 
        REFERENCES CLASSROOM(CLASSROOM_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

        CONSTRAINT fk_dvsnId_gvenTbl
        FOREIGN KEY (DIVISION_ID) 
        REFERENCES DIVISION(DIVISION_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

        CONSTRAINT fk_wkdy_gvenTbl
        FOREIGN KEY (WEEKDAY) 
        REFERENCES WEEKDAY(WEEKDAY)
        ON DELETE CASCADE
        ON UPDATE CASCADE
      );
    ", "Given");

runquery($conn, "
      CREATE TABLE IF NOT EXISTS TEACHES(
        TEACHER_ID INT,
        COURSE_ID INT,
        DIVISION_ID INT,
        LECTURE_COUNT INT NOT NULL DEFAULT(0),
        
        PRIMARY KEY(TEACHER_ID,COURSE_ID,DIVISION_ID),

        CONSTRAINT fk_tchrId_tchsTbl 
        FOREIGN KEY (TEACHER_ID) 
        REFERENCES TEACHER(TEACHER_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

        CONSTRAINT fk_crseId_tchsTbl 
        FOREIGN KEY (COURSE_ID) 
        REFERENCES COURSE(COURSE_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

        CONSTRAINT fk_dvsn_tchsTbl 
        FOREIGN KEY (DIVISION_ID) 
        REFERENCES DIVISION(DIVISION_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE

      );
    ", "Teaches");

runquery($conn, "
      CREATE TABLE IF NOT EXISTS TIMETABLE(
        COURSE_ID INT,
        DIVISION_ID INT,
        CLASSROOM_ID INT,
        SLOT_ID INT,
        WEEKDAY ENUM('MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY'),
        TEACHER_ID INT,
        ACADEMIC_YEAR VARCHAR(7) NOT NULL,
        SEMESTER VARCHAR(4) NOT NULL,

        PRIMARY KEY(COURSE_ID, DIVISION_ID, CLASSROOM_ID, SLOT_ID, TEACHER_ID, ACADEMIC_YEAR, SEMESTER),

        CONSTRAINT fk_crseId_tTbl 
        FOREIGN KEY (COURSE_ID) 
        REFERENCES COURSE(COURSE_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

        CONSTRAINT fk_dvsnId_tTbl 
        FOREIGN KEY (DIVISION_ID) 
        REFERENCES DIVISION(DIVISION_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

        CONSTRAINT fk_clsrmId_tTbl 
        FOREIGN KEY (CLASSROOM_ID) 
        REFERENCES CLASSROOM(CLASSROOM_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

        CONSTRAINT fk_slotId_tTbl 
        FOREIGN KEY (SLOT_ID) 
        REFERENCES TIMESLOT(SLOT_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

        CONSTRAINT fk_wkdy_tTbl
        FOREIGN KEY (WEEKDAY) 
        REFERENCES WEEKDAY(WEEKDAY)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

        CONSTRAINT fk_tchrId_tTbl 
        FOREIGN KEY (TEACHER_ID) 
        REFERENCES TEACHER(TEACHER_ID)
        ON DELETE CASCADE
        ON UPDATE CASCADE
      );
    ", "Timetable");




$rooms = [
  ['Ground Floor',001],
  ['Ground Floor',002],
  ['First Floor',101],
  ['First Floor',102],
  ['Second Floor',201],
  ['Second Floor',202]
];
$weekdays = [
  'MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY'
];
$years = [
  'FIRST YEAR', 'SECOND YEAR', 'THIRD YEAR'
];

AddClassrooms($conn,$rooms);
AddWeekdays($conn,$weekdays);
AddYears($conn,$years);



