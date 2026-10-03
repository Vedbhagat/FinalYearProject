1) Get all the data for Timetable generation
    Teacher => teacherID,teacherAvailabilityMatrix containing weekday wise slots that are either marked as available or unavailable or alloted
    classroom => classroomId, capacity, type, classroomAvailabilityMatrix containing weekday wise slots that are either marked as available or not available or alloted
    course => courseId, minimum Weekly lectures, is optional is practical, optional with id, pending lectuers to be alloted
    workload => teacherId, courseId, assigned lectures, pending lectues, divison id for allotment
    division => divisionId, student count, perference classroom and perference time (start or end).
    timeslots => slotId, starttime, endtime, type

2) prepare for allotment
    sort the teachers by their total availability hours accross the entire week. minimum time first.Get
    expand the workload like if a teacher is going to teach the entire year then the workload must be division wise and if a course is optional then the workload is to be made for that division only. sort the workload division wise.
    if a division contains practical courses then mark that division for the practical allotment.

3) Fix Practical slots
    a) gathering the data:
        get all the divisions that are marked for the practical allotment
        get all the classrooms that are labs
        get all the practical courses
        get all the practical slots
    b) fixing slots:
        take a division from the gathered data, get the preference if the time (start, end)
        cartecian product the practical slots with the classroom (not weekdays) for example, for IT LABs, 7-9:lab 1, 9:30-11:30:lab1, 7-9:lab 2, 9:30-11:30:lab 2 and same for the other types of labs donot mix the cartecian product of differeent lab types.
        take the first practical slots possible (if start is mentioned then the next possible slot and if end is mentioned then the previous possible slot) and according to the type of practical course and classroom capacity and division studnet count fix that slot with that lab for the entire week.
        for example for fy bscit division a, IT lab 01 is fixed for slot 1 to 3. all the other labs are free for that timeslot.

4) allot practical course:
    get the division, its fixed practical slot, practical lab, practical teachers and practical workload.
    start the allocation by the teacher with the minimum availability hours across the entire week.
    if a practical course is having the data as lectuer count as 1 then 1 practical slot is to be allocated to that course.

5)preprocess the slots for theory allotment:
    get the total hours of the workload for a division and divide them by 6 working days to get the daily division quota. 
    create a slot matrix of the allocatable slots for each division the allocatable slots for all the week days are the slots from the start time towards practical or end time towards practical.
    also create a fallback slots matrix containing 2 more slots for each week day that are not there in the allocatable slots but adjecent to it.
    
6) preprocess the classrooms sort all the classrooms by the capacity.Fix
7) Begin allotment of the theory courses.
        for a division, get the first slot from the allocatable slot that is available.
        for that slot, get all the pending workload for that division.
        for that slot get all the available teachers that are in the pending workload.
        select one workload at random and make the allocation for that worklaod in that slot.
        mark that teacher allocated for that slot and mark that allocatable slot as alloted. repeat for the entire division and then for all divisions.
8) check for complete allotment
        get the total division workload and the total allocated workload for that division. if there exists any workload that is not assigned yet then use the fallback slots to make the allotment.
9) the classroom allotment must be as follows
        get the classroom capacity and divisions student count and classroom availability
        chose the classroom whos capacity is equal or little more than the division student count and allocate that for that slot for that weekday fopr that division
        and for the next slot check the previous slot s assigned classroom and try to allocate that. if it is not avaialble then allocate another classroom.


make sure that a course of a year under a programme is allocated in that boundries only a course of some year of a programme must not get allocated inthe different places.
if a practical cource is allocated to a practical slot then cancel all the lectuer slots that overlap with this practical slot and make sure that 7-9 practicalis alloted then the 7-8 an 8-9 must be canceled. 
keep the track of pending workloads and make sure that the course is neithert alloted more lectures nor alloted less lecture than specified
all the workload must get allocated to their respective divisions and if required use the fall back slots also fore complete allocation