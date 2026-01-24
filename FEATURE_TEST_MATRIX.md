# CAMS - Detailed Feature Test Matrix

This document provides a granular breakdown of every feature in the CAMS application with test status details.

---

## Legend

- ✅ **Fully Tested** - Feature has comprehensive test coverage
- ⚠️ **Partially Tested** - Feature has some tests but gaps remain
- ❌ **Not Tested** - Feature has no automated tests
- 🐛 **Bug Found** - Issue discovered during testing
- 🔒 **Security Critical** - Requires security testing

---

## Student Features

### SF-01: Browse Advisors
**Status:** ✅ Fully Tested  
**Route:** `GET /student/advisors`  
**Controller:** `StudentBookingController@index`  

**Functionality:**
- Display list of all advisors
- Search by name/email
- Filter by department
- Show advisor details (ID, email, department)

**Tests:**
- ✅ `test_authenticated_users_can_access_advisors_list`
- ✅ `test_advisors_are_listed_on_index_page`
- ✅ `test_advisors_can_be_searched_by_name`
- ✅ `test_advisors_can_be_filtered_by_department`

**Manual Testing:** ✅ Confirmed working - shows 11 advisors correctly

---

### SF-02: View Advisor Slots
**Status:** ⚠️ Partially Tested  
**Route:** `GET /student/advisors/{id}`  
**Controller:** `StudentBookingController@show`  

**Functionality:**
- View specific advisor details
- Display available time slots
- Show only active future slots
- Display slot status (open/booked)

**Tests:**
- ✅ `test_authenticated_users_can_view_advisor_slots`
- ✅ `test_only_active_future_slots_are_displayed` 🐛 **FAILING**
- ✅ `test_viewing_nonexistent_advisor_returns_404`

**Issues:**
- 🐛 Test expects 1 slot but shows 2 (possible blocked slot showing)

**Manual Testing:** ✅ Confirmed shows slots for Dr. Nabila Advisor

---

### SF-03: Book Appointment
**Status:** ✅ Fully Tested  
**Route:** `POST /student/book`  
**Controller:** `StudentBookingController@store`  
**Security:** 🔒 Rate limit: 10 requests/minute

**Functionality:**
- Submit booking request with slot_id and purpose
- Upload optional document (max 100MB)
- Generate unique appointment token
- Block slot when booked
- Create activity log entry
- Transaction locking to prevent race conditions

**Tests:**
- ✅ `test_student_can_book_available_slot`
- ✅ `test_appointment_token_is_generated`
- ✅ `test_booking_fails_when_slot_is_already_taken`
- ✅ `test_validation_requires_slot_id`
- ✅ `test_validation_requires_purpose`
- ✅ `test_validation_rejects_nonexistent_slot`
- ✅ `test_validation_enforces_purpose_max_length`
- ✅ `test_activity_log_is_created_when_booking_appointment`
- ✅ `test_no_activity_log_created_when_booking_fails`

**File Upload Tests:**
- ✅ `test_student_can_book_appointment_with_pdf_document`
- ✅ `test_student_can_book_appointment_with_docx_document`
- ✅ `test_student_can_book_appointment_with_pptx_presentation`
- ✅ `test_student_can_book_appointment_with_xlsx_spreadsheet`
- ✅ `test_student_can_book_appointment_with_jpg_image`
- ✅ `test_student_can_book_appointment_without_document`
- ✅ `test_invalid_file_types_are_rejected`
- ✅ `test_files_larger_than_100mb_are_rejected`

**Manual Testing:** ⚠️ Modal/form interaction unclear (JavaScript issue)

---

### SF-04: View My Appointments
**Status:** ✅ Fully Tested  
**Route:** `GET /student/my-appointments`  
**Controller:** `StudentBookingController@myAppointments`  

**Functionality:**
- Display all student's appointments
- Separate tabs: Upcoming vs Past
- Show appointment details (advisor, date, time, status, purpose)
- Display appointment token
- Show attached documents
- Feedback submission form for completed appointments

**Tests:**
- ✅ `test_students_can_access_appointments_page`
- ✅ `test_unauthenticated_users_cannot_access_appointments_page`
- ✅ `test_student_appointments_are_displayed`
- ✅ `test_students_only_see_their_own_appointments`
- ✅ `test_appointments_are_ordered_by_creation_date`
- ✅ `test_appointments_with_different_statuses_are_displayed`
- ✅ `test_empty_appointments_page_displays_correctly`
- ✅ `test_upcoming_tab_shows_only_future_pending_and_approved_appointments`
- ✅ `test_past_tab_shows_past_and_cancelled_appointments`

**Manual Testing:** ❌ Not tested manually

---

### SF-05: Cancel Appointment
**Status:** ⚠️ Partially Tested 🐛  
**Route:** `POST /student/appointments/{id}/cancel`  
**Controller:** `StudentBookingController@cancel`  

**Functionality:**
- Cancel pending/approved appointments
- Free up slot (set status to 'active')
- Trigger waitlist notification
- Create activity log
- Prevent cancellation of past/declined/completed appointments

**Tests:**
- ✅ `test_student_can_cancel_pending_appointment`
- ✅ `test_student_can_cancel_approved_appointment`
- ❌ `test_student_cannot_cancel_past_appointment` 🐛 **FAILING** (404 error)
- ❌ `test_student_cannot_cancel_declined_appointment` 🐛 **FAILING** (session error)
- ✅ `test_student_cannot_cancel_other_students_appointment`
- ✅ `test_waitlist_notified_when_appointment_cancelled`
- ✅ `test_unauthenticated_users_cannot_cancel_appointments`
- ✅ `test_advisors_cannot_cancel_via_student_route`

**Issues:**
- 🐛 Cancellation validation may not work for past/declined appointments
- 🐛 Route might return 404 instead of proper error handling

**Manual Testing:** ❌ Not tested manually

---

### SF-06: Join Waitlist
**Status:** ⚠️ Partially Tested 🐛  
**Route:** `POST /waitlist/{slot_id}`  
**Controller:** `StudentBookingController@joinWaitlist`  

**Functionality:**
- Join waitlist for blocked (booked) slots
- Prevent duplicate waitlist entries
- Remove from waitlist when student books slot
- FIFO notification when slot becomes available
- Send email notification to first student in queue

**Tests:**
- ✅ `test_student_can_join_waitlist_for_blocked_slot`
- ✅ `test_student_cannot_join_waitlist_for_active_slot`
- ✅ `test_student_cannot_join_waitlist_twice_for_same_slot`
- ✅ `test_waitlist_entry_removed_when_student_books_slot`
- ✅ `test_slot_freed_up_event_fired_when_appointment_declined`
- ❌ `test_first_student_in_waitlist_receives_notification_when_slot_freed` 🐛 **FAILING**
- ✅ `test_slot_becomes_active_when_appointment_declined`
- ✅ `test_no_notification_sent_if_waitlist_empty`
- ✅ `test_waitlist_respects_fifo_order`
- ✅ `test_guest_cannot_join_waitlist`
- ✅ `test_advisor_cannot_join_waitlist`

**Issues:**
- 🐛 Email notification not being queued for waitlisted students

**Manual Testing:** ❌ Not tested manually

---

### SF-07: Submit Feedback
**Status:** ❌ Not Tested 🔒  
**Route:** `POST /feedback`  
**Controller:** `FeedbackController@store`  

**Functionality:**
- Rate completed appointments (1-5 stars)
- Optional comment (max 1000 chars)
- Anonymous option
- Prevent duplicate feedback
- Only student who booked can rate

**Security Checks:**
- Student must own the appointment
- Appointment must exist
- Cannot rate twice

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Student can submit feedback for completed appointment
- [ ] Feedback requires valid rating (1-5)
- [ ] Anonymous feedback hides student identity
- [ ] Cannot submit feedback twice
- [ ] Cannot submit feedback for other student's appointment
- [ ] Cannot submit feedback for non-existent appointment
- [ ] Comment max length validation

**Manual Testing:** ❌ Not tested manually

---

### SF-08: Browse Resources
**Status:** ❌ Not Tested  
**Route:** `GET /student/resources`  
**Controller:** `ResourceController@index`  

**Functionality:**
- View resource library
- Filter by category (Academic, Mental Health, Wellness, Career, Other)
- Filter by advisor (uploader)
- Search by title/description
- Display resource details (title, description, category, uploader, upload date)
- Download button for each resource

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Students can access resource library
- [ ] Resources are displayed correctly
- [ ] Filter by category works
- [ ] Filter by advisor works
- [ ] Search functionality works
- [ ] Pagination works
- [ ] Empty state displays correctly

**Manual Testing:** ❌ Not tested manually

---

### SF-09: Download Resources
**Status:** ❌ Not Tested 🔒  
**Route:** `GET /resources/{resource}/download`  
**Controller:** `ResourceController@download`  

**Functionality:**
- Secure file download
- Check file exists
- Proper headers for download

**Security Checks:**
- Authentication required
- File path validation
- Prevent directory traversal

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Authenticated users can download resources
- [ ] Unauthenticated users cannot download
- [ ] Download non-existent resource returns 404
- [ ] File headers are correct
- [ ] File name is correct

**Manual Testing:** ❌ Not tested manually

---

## Advisor Features

### AF-01: Manage Availability Slots
**Status:** ✅ Fully Tested  
**Route:** `POST /advisor/slots`  
**Controller:** `AdvisorSlotController@store`  
**Security:** 🔒 Rate limit: 20 requests/minute

**Functionality:**
- Create single or recurring slots
- Configure duration (20/30/45/60 minutes)
- Set date range for recurring slots
- Set start and end time
- Validate no overlaps with existing slots
- Prevent creation in the past
- Validate time range is sufficient for slot duration

**Tests:**
- ✅ `test_slot_generation_creates_correct_number_of_slots`
- ✅ `test_validation_rejects_past_dates`
- ✅ `test_validation_rejects_end_time_before_start_time`
- ✅ `test_validation_rejects_invalid_duration`
- ⚠️ `test_returns_error_when_time_range_too_short_for_slots` 🐛 **FAILING**

**Overlap Detection Tests:**
- ⚠️ `test_overlapping_slots_are_not_created` 🐛 **FAILING**
- ✅ `test_adjacent_slots_are_created_correctly`
- ✅ `test_creating_multiple_slots_in_time_range`
- ✅ `test_creating_slots_with_20_minute_duration`
- ✅ `test_creating_slots_with_45_minute_duration`
- ✅ `test_creating_slots_with_60_minute_duration`
- ✅ `test_partial_overlap_at_start_is_handled`
- ✅ `test_partial_overlap_at_end_is_handled`
- ✅ `test_slots_for_different_advisors_dont_affect_each_other`
- ✅ `test_creating_slots_for_today_works`
- ✅ `test_blocked_slots_are_not_considered_for_overlap`
- ✅ `test_slot_times_are_correctly_stored`

**Issues:**
- 🐛 Session error handling in validation tests

**Manual Testing:** ❌ Not tested manually

---

### AF-02: View My Slots
**Status:** ✅ Fully Tested  
**Route:** `GET /advisor/slots`  
**Controller:** `AdvisorSlotController@index`  

**Functionality:**
- Display all advisor's slots
- Show slot details (date, time, duration, status)
- Filter future vs past slots
- Delete buttons for active slots

**Tests:**
- ✅ `test_advisors_can_access_slots_page`
- ✅ `test_students_cannot_access_advisor_slots_page`
- ✅ `test_unauthenticated_users_cannot_access_advisor_slots`

**Manual Testing:** ❌ Not tested manually

---

### AF-03: Delete Single Slot
**Status:** ✅ Fully Tested  
**Route:** `DELETE /advisor/slots/{slot}`  
**Controller:** `AdvisorSlotController@destroy`  

**Functionality:**
- Delete individual slot
- Only allow deletion of active (unbooked) slots
- Prevent deletion of blocked (booked) slots
- Advisor can only delete own slots

**Tests:**
- ✅ `test_advisors_can_delete_their_own_active_slots`
- ✅ `test_advisors_cannot_delete_other_advisors_slots`
- ✅ `test_advisors_cannot_delete_booked_slots`
- ✅ `test_students_cannot_delete_slots`

**Manual Testing:** ❌ Not tested manually

---

### AF-04: Bulk Delete Slots
**Status:** ❌ Not Tested  
**Route:** `DELETE /advisor/slots/bulk`  
**Controller:** `AdvisorSlotController@bulkDestroy`  

**Functionality:**
- Delete multiple slots at once
- Validate all slots belong to advisor
- Prevent deletion of booked slots
- Send JSON response with success/error counts

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Advisors can bulk delete their own active slots
- [ ] Bulk delete skips booked slots
- [ ] Bulk delete validates slot ownership
- [ ] Returns correct success/error counts
- [ ] Students cannot bulk delete slots

**Manual Testing:** ❌ Not tested manually

---

### AF-05: View Pending Requests
**Status:** ✅ Fully Tested  
**Route:** `GET /advisor/dashboard`  
**Controller:** `AdvisorAppointmentController@index`  

**Functionality:**
- Display pending appointment requests
- Show student details
- Show appointment purpose
- Show attached documents
- Approve/Decline buttons
- Filter/sort options

**Tests:**
- ✅ `test_advisors_can_access_dashboard`
- ✅ `test_students_cannot_access_advisor_dashboard`
- ✅ `test_unauthenticated_users_cannot_access_advisor_dashboard`
- ✅ `test_pending_appointments_are_displayed_on_dashboard`

**Manual Testing:** ❌ Not tested manually

---

### AF-06: Approve/Decline Requests
**Status:** ✅ Fully Tested  
**Route:** `PATCH /advisor/appointments/{id}`  
**Controller:** `AdvisorAppointmentController@updateStatus`  

**Functionality:**
- Approve appointment request (status → 'approved')
- Decline appointment request (status → 'declined')
- Free slot when declined (status → 'active')
- Trigger waitlist notification when declined
- Only advisor who owns the slot can update
- Validate status is 'approved' or 'declined'

**Tests:**
- ✅ `test_advisors_can_approve_pending_appointments`
- ✅ `test_advisors_can_decline_pending_appointments`
- ✅ `test_advisors_cannot_update_other_advisors_appointments`
- ✅ `test_validation_requires_valid_status`
- ✅ `test_updating_nonexistent_appointment_returns_404`
- ✅ `test_students_cannot_update_appointment_status`

**Manual Testing:** ❌ Not tested manually

---

### AF-07: View Schedule
**Status:** ❌ Not Tested  
**Route:** `GET /advisor/schedule`  
**Controller:** `AdvisorScheduleController@index`  

**Functionality:**
- Display upcoming approved appointments
- Display completed appointments history
- Show student details
- Show appointment purpose
- Link to create MOM notes for approved appointments

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Advisors can access schedule page
- [ ] Schedule shows upcoming approved appointments
- [ ] Schedule shows completed appointments
- [ ] Schedule only shows advisor's own appointments
- [ ] Students cannot access advisor schedule
- [ ] Appointments are ordered by date

**Manual Testing:** ❌ Not tested manually

---

### AF-08: Record Session Notes (MOM)
**Status:** ❌ Not Tested 🔒  
**Route:** `GET /advisor/appointments/{id}/note` (view form)  
**Route:** `POST /advisor/appointments/{id}/note` (save)  
**Controller:** `AdvisorMinuteController@create/store`  

**Functionality:**
- View form to write minutes of meeting
- Display previous 5 session notes for same student
- Save note (min 5 chars, max 5000 chars)
- Mark appointment as 'completed' when note saved
- Update existing note if already exists
- Only advisor who owns appointment can create note

**Security Checks:**
- Advisor must own the appointment slot
- Appointment must exist

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Advisor can view MOM creation form
- [ ] Advisor can save session note
- [ ] Note marks appointment as completed
- [ ] Previous notes for student are displayed
- [ ] Note validation (min/max length)
- [ ] Advisor cannot create note for other advisor's appointment
- [ ] Student cannot create session notes
- [ ] Updating existing note works

**Manual Testing:** ❌ Not tested manually

---

### AF-09: Download Student Documents
**Status:** ❌ Not Tested 🔒  
**Route:** `GET /advisor/documents/{documentId}/download`  
**Controller:** `AdvisorAppointmentController@downloadDocument`  

**Functionality:**
- Secure download of student-uploaded documents
- Verify advisor owns the appointment
- Check file exists
- Return file with proper headers

**Security Checks:**
- Advisor must own the appointment slot
- Document must belong to the appointment
- File path validation

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Advisor can download document from their appointment
- [ ] Advisor cannot download document from other advisor's appointment
- [ ] Student cannot download via advisor route
- [ ] Non-existent document returns 404
- [ ] Proper file headers are set
- [ ] File name is preserved

**Manual Testing:** ❌ Not tested manually

---

### AF-10: View Student History
**Status:** ❌ Not Tested  
**Route:** `GET /advisor/students/{id}/history`  
**Controller:** `AdvisorAppointmentController@getStudentHistory`  

**Functionality:**
- AJAX endpoint returning JSON
- Display completed appointments for specific student
- Show associated MOM notes
- Used in modal popup for context during sessions

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Advisor can fetch student history
- [ ] History shows completed appointments only
- [ ] History includes MOM notes
- [ ] History is ordered by date (newest first)
- [ ] Empty history returns empty array
- [ ] JSON response format is correct

**Manual Testing:** ❌ Not tested manually

---

### AF-11: Upload/Manage Resources
**Status:** ❌ Not Tested 🔒  
**Route:** `POST /advisor/resources` (upload)  
**Route:** `DELETE /advisor/resources/{resource}` (delete)  
**Route:** `GET /advisor/resources` (list)  
**Controller:** `ResourceController@store/destroy/index`  

**Functionality:**
- Upload study materials (PDF, DOC, PPT, etc.)
- Set title, description, category
- Delete own uploaded resources
- View all resources with filters

**Security Checks:**
- File type validation
- File size limit
- Only uploader can delete resource

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Advisor can upload resource
- [ ] File type validation works
- [ ] File size validation works
- [ ] Advisor can delete own resource
- [ ] Advisor cannot delete other advisor's resource
- [ ] Resource details are stored correctly
- [ ] Student cannot upload resources

**Manual Testing:** ❌ Not tested manually

---

## Admin Features

### AD-01: Dashboard Analytics
**Status:** ⚠️ Partially Tested 🐛  
**Route:** `GET /admin/dashboard`  
**Controller:** `AdminDashboardController@index`  

**Functionality:**
- Display analytics widgets:
  - Top advisor (most appointments)
  - Total counseling hours
  - Total appointments
  - Pending requests count
  - Total students count
  - Total faculty count
  - Total notices count
- Recent appointments table
- Quick action buttons

**Tests:**
- ❌ `test_admins_can_access_dashboard` 🐛 **FAILING**

**Issues:**
- 🐛 Test expects 200 OK but admin redirects to /admin/dashboard

**Recommended Tests:**
- [ ] Admin can access dashboard
- [ ] Analytics widgets display correct counts
- [ ] Top advisor calculation is correct
- [ ] Recent appointments are displayed
- [ ] Students/Advisors cannot access admin dashboard

**Manual Testing:** ❌ Not tested manually

---

### AD-02: Export Appointments
**Status:** ❌ Not Tested  
**Route:** `GET /admin/export`  
**Controller:** `AdminDashboardController@export`  

**Functionality:**
- Export all appointments to CSV
- Include columns: Student, Advisor, Department, Date, Time, Status, Purpose
- Generate downloadable file

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Admin can export appointments to CSV
- [ ] CSV contains all appointments
- [ ] CSV headers are correct
- [ ] CSV data format is correct
- [ ] File download headers are set
- [ ] Students/Advisors cannot export

**Manual Testing:** ❌ Not tested manually

---

### AD-03: Manage Faculty (CRUD)
**Status:** ❌ Not Tested 🔒  
**Routes:**
- `GET /admin/faculty` (list)
- `GET /admin/faculty/create` (create form)
- `POST /admin/faculty` (store)
- `GET /admin/faculty/{id}/edit` (edit form)
- `PUT /admin/faculty/{id}` (update)
- `DELETE /admin/faculty/{id}` (delete)  
**Controller:** `AdminFacultyController`

**Functionality:**
- List all advisors with search/filter
- Create new advisor accounts
- Edit advisor details (name, email, department, password)
- Delete advisor accounts
- Search by name/email
- Filter by department

**Security Checks:**
- Prevent creating duplicate emails
- Prevent creating duplicate university IDs
- Password hashing
- Role must be 'advisor'

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Admin can view faculty list
- [ ] Admin can create new faculty
- [ ] Admin can edit faculty details
- [ ] Admin can delete faculty
- [ ] Search functionality works
- [ ] Department filter works
- [ ] Email validation (unique, format)
- [ ] University ID validation (unique)
- [ ] Password is hashed
- [ ] Students/Advisors cannot manage faculty
- [ ] Cannot create faculty with role 'admin'

**Manual Testing:** ❌ Not tested manually

---

### AD-04: Manage Students (CRUD)
**Status:** ❌ Not Tested 🔒  
**Routes:**
- `GET /admin/students` (list)
- `GET /admin/students/create` (create form)
- `POST /admin/students` (store)
- `GET /admin/students/{student}` (view)
- `GET /admin/students/{student}/edit` (edit form)
- `PUT /admin/students/{student}` (update)
- `DELETE /admin/students/{student}` (delete)  
**Controller:** `AdminStudentController`

**Functionality:**
- List all students with search/filter
- Create new student accounts
- Edit student details
- Delete student accounts
- View student profile
- Search by name/email/university_id
- Filter by department

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Admin can view student list
- [ ] Admin can create new student
- [ ] Admin can edit student details
- [ ] Admin can delete student
- [ ] Admin can view student profile
- [ ] Search functionality works
- [ ] Department filter works
- [ ] Email validation (unique, format)
- [ ] University ID validation (unique)
- [ ] Students/Advisors cannot manage students
- [ ] Cannot create student with role 'admin'

**Manual Testing:** ❌ Not tested manually

---

### AD-05: Create Bookings
**Status:** ❌ Not Tested 🔒  
**Route:** `POST /admin/bookings`  
**Controller:** `AdminBookingController@store`  

**Functionality:**
- Book appointment on behalf of student
- Select student from dropdown
- Select advisor from dropdown
- Select available slot
- Enter purpose
- Appointment auto-approved
- Generate token
- Create activity log

**Security Checks:**
- Validate student exists
- Validate slot exists and is available
- Transaction locking to prevent race conditions

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Admin can create booking for student
- [ ] Booking auto-approves appointment
- [ ] Slot is blocked after booking
- [ ] Token is generated
- [ ] Activity log is created
- [ ] Cannot book unavailable slot
- [ ] Cannot book for non-existent student
- [ ] Students/Advisors cannot create bookings

**Manual Testing:** ❌ Not tested manually

---

### AD-06: Delete Bookings
**Status:** ❌ Not Tested  
**Route:** `DELETE /admin/bookings/{id}`  
**Controller:** `AdminBookingController@destroy`  

**Functionality:**
- Delete appointment
- Free up slot (set status to 'active')
- Used for corrections/cancellations

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Admin can delete booking
- [ ] Slot is freed after deletion
- [ ] Appointment is removed from database
- [ ] Students/Advisors cannot delete bookings

**Manual Testing:** ❌ Not tested manually

---

### AD-07: Get Available Slots (AJAX)
**Status:** ❌ Not Tested  
**Route:** `GET /admin/bookings/slots`  
**Controller:** `AdminBookingController@getSlots`  

**Functionality:**
- AJAX endpoint for booking form
- Fetch active future slots for selected advisor
- Return JSON with slot details
- Used in dropdown for slot selection

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Endpoint returns slots for advisor
- [ ] Only active slots are returned
- [ ] Only future slots are returned
- [ ] JSON format is correct
- [ ] Returns empty array if no slots
- [ ] Validates advisor_id parameter

**Manual Testing:** ❌ Not tested manually

---

### AD-08: Activity Logging
**Status:** ✅ Fully Tested  
**Route:** `GET /admin/activity-logs`  
**Controller:** `AdminActivityLogController@index`  

**Functionality:**
- View audit trail of all activities
- Filter by user (search name/email)
- Filter by action type (login, booking, cancellation)
- Filter by date range
- Filter by role
- Pagination
- Display: user, role, action, description, IP, timestamp

**Tests:**
- ✅ `test_admin_can_access_activity_logs_page`
- ✅ `test_activity_logs_are_displayed_correctly`
- ✅ `test_pagination_works_correctly`
- ✅ `test_search_filtering_works`
- ✅ `test_date_range_filtering_works`
- ✅ `test_action_type_filtering_works`
- ✅ `test_role_filtering_works`
- ✅ `test_non_admin_users_cannot_access_logs`
- ✅ `test_unauthenticated_users_cannot_access_logs`
- ✅ `test_logs_are_ordered_by_most_recent_first`

**Unit Tests:**
- ✅ `test_log_login_creates_activity_log_with_correct_fields`
- ✅ `test_log_booking_creates_activity_log_with_correct_fields`
- ✅ `test_log_cancellation_creates_activity_log_with_correct_fields`
- ✅ `test_log_method_with_custom_user_id`
- ✅ `test_log_method_uses_authenticated_user_when_user_id_not_provided`
- ✅ `test_log_method_captures_ip_address`

**Manual Testing:** ❌ Not tested manually

---

### AD-09: Manage Notices
**Status:** ❌ Not Tested  
**Routes:**
- `GET /admin/notices` (list)
- `GET /admin/notices/create` (create form)
- `POST /admin/notices` (store)
- `GET /admin/notices/{notice}` (view)
- `GET /admin/notices/{notice}/edit` (edit form)
- `PUT /admin/notices/{notice}` (update)
- `DELETE /admin/notices/{notice}` (delete)  
**Controller:** `AdminNoticeController`

**Functionality:**
- Create system-wide notices/announcements
- Target specific audience:
  - All users
  - All students
  - All advisors
  - Specific user
- Edit/update notices
- Delete notices
- View notice list with pagination

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Admin can create notice for all users
- [ ] Admin can create notice for students only
- [ ] Admin can create notice for advisors only
- [ ] Admin can create notice for specific user
- [ ] Admin can edit notice
- [ ] Admin can delete notice
- [ ] Admin can view notice list
- [ ] Validation works (title, content, user_role)
- [ ] Students/Advisors cannot manage notices

**Manual Testing:** ❌ Not tested manually

---

### AD-10: Manage Resource Library
**Status:** ❌ Not Tested  
**Route:** `GET /admin/resources`  
**Controller:** `ResourceController@index`  

**Functionality:**
- Same as advisor resource management
- Admin can upload/delete any resource
- Shared view with advisors

**Tests:** ❌ NO TESTS (see AF-11)

**Manual Testing:** ❌ Not tested manually

---

## Common/Shared Features

### CM-01: Main Dashboard
**Status:** ⚠️ Partially Tested 🐛  
**Route:** `GET /dashboard`  
**Built-in route handler in:** `routes/web.php`

**Functionality:**
- Role-based dashboard (admin redirects to /admin/dashboard)
- Display next approved appointment
- Display recent notices (filtered by role)
- Quick action cards
- Calendar widget
- Account status info

**Tests:**
- ✅ `test_authenticated_users_can_access_dashboard`
- ✅ `test_unauthenticated_users_redirected_from_dashboard`
- ✅ `test_root_route_redirects_to_login`
- ✅ `test_dashboard_displays_next_approved_appointment`
- ✅ `test_dashboard_shows_null_when_no_approved_appointments`
- ✅ `test_dashboard_only_shows_users_own_appointments`
- ✅ `test_dashboard_does_not_show_pending_appointments`
- ✅ `test_dashboard_does_not_show_declined_appointments`
- ✅ `test_students_can_access_dashboard`
- ✅ `test_advisors_can_access_dashboard`
- ❌ `test_admins_can_access_dashboard` 🐛 **FAILING**
- ✅ `test_unverified_users_can_access_dashboard`

**Manual Testing:** ✅ Confirmed student dashboard works

---

### CM-02: Edit Profile
**Status:** ✅ Fully Tested  
**Route:** `GET /profile` (view)  
**Route:** `PATCH /profile` (update)  
**Controller:** `ProfileController@edit/update`

**Functionality:**
- Update name
- Update email (triggers verification)
- Update password
- Display user info in sidebar
- Department display
- University ID display

**Tests:**
- ✅ `test_profile_page_is_displayed`
- ✅ `test_profile_information_can_be_updated`
- ✅ `test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged`
- ✅ `test_profile_sidebar_displays_user_information`
- ✅ `test_profile_sidebar_displays_na_fallback_for_missing_university_id`
- ✅ `test_profile_sidebar_displays_general_fallback_for_missing_department`

**Manual Testing:** ❌ Not tested manually

---

### CM-03: Delete Account
**Status:** ✅ Fully Tested  
**Route:** `DELETE /profile`  
**Controller:** `ProfileController@destroy`

**Functionality:**
- Self-delete account
- Require password confirmation
- Logout after deletion

**Tests:**
- ✅ `test_user_can_delete_their_account`
- ✅ `test_correct_password_must_be_provided_to_delete_account`

**Manual Testing:** ❌ Not tested manually

---

### CM-04: Personal Calendar
**Status:** ❌ Not Tested  
**Routes:**
- `GET /calendar/events` (fetch)
- `POST /calendar/events` (create)
- `DELETE /calendar/events/{id}` (delete)  
**Controller:** `CalendarController`

**Functionality:**
- Create personal notes/reminders
- View calendar events
- Delete calendar events
- Color-coded events
- Display appointments on calendar (read-only)
- Event types: note, reminder
- Full FullCalendar integration

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] User can fetch calendar events
- [ ] User can create calendar event
- [ ] User can delete own calendar event
- [ ] User cannot delete other user's event
- [ ] Student appointments appear on calendar
- [ ] Advisor appointments appear on calendar
- [ ] Event validation works (title, start_time, type)
- [ ] Color coding is correct

**Manual Testing:** ⚠️ FullCalendar JavaScript error found

---

### CM-05: View Notifications
**Status:** ❌ Not Tested  
**Route:** `GET /notifications`  
**Controller:** `NotificationController@index`

**Functionality:**
- AJAX endpoint returning JSON
- Fetch last 10 notifications
- Return unread count
- Display in dropdown

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] User can fetch notifications
- [ ] Returns latest 10 notifications
- [ ] Returns correct unread count
- [ ] JSON format is correct
- [ ] Empty state works correctly

**Manual Testing:** ❌ Not tested manually

---

### CM-06: Mark Notification as Read
**Status:** ❌ Not Tested  
**Route:** `POST /notifications/{id}/read`  
**Controller:** `NotificationController@markAsRead`

**Functionality:**
- AJAX endpoint
- Mark specific notification as read
- Update read_at timestamp
- Return JSON success

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] User can mark notification as read
- [ ] User cannot mark other user's notification
- [ ] Non-existent notification returns error
- [ ] Response format is correct

**Manual Testing:** ❌ Not tested manually

---

### CM-07: Mark All Notifications as Read
**Status:** ❌ Not Tested  
**Route:** `POST /notifications/mark-all`  
**Controller:** `NotificationController@markAllAsRead`

**Functionality:**
- Mark all unread notifications as read
- Redirect back with success message

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] User can mark all notifications as read
- [ ] All unread notifications are updated
- [ ] Already read notifications are unaffected
- [ ] Success message is displayed

**Manual Testing:** ❌ Not tested manually

---

### CM-08: System Notices Display
**Status:** ❌ Not Tested  
**Integration:** Dashboard view  
**Model:** `Notice`

**Functionality:**
- Display notices on dashboard
- Filter by user_role (all, student, advisor, specific)
- Show latest 3 notices
- Display title, content, created_at

**Tests:** ❌ NO TESTS

**Recommended Tests:**
- [ ] Notices filtered by 'all' appear for everyone
- [ ] Notices filtered by 'student' appear for students only
- [ ] Notices filtered by 'advisor' appear for advisors only
- [ ] Specific user notices appear for that user only
- [ ] Latest 3 notices are displayed
- [ ] Notices are ordered by newest first

**Manual Testing:** ❌ Not tested manually

---

## Authentication Features

### AU-01: Login
**Status:** ✅ Fully Tested  
**Route:** `POST /login`  
**Controller:** `Auth\AuthenticatedSessionController@store`

**Tests:**
- ✅ `test_login_screen_can_be_rendered`
- ✅ `test_users_can_authenticate_using_the_login_screen`
- ✅ `test_users_can_not_authenticate_with_invalid_password`
- ✅ `test_users_can_logout`
- ✅ `test_activity_log_is_created_on_successful_login`
- ✅ `test_no_activity_log_is_created_on_failed_login`

---

### AU-02: Registration
**Status:** ✅ Fully Tested  
**Route:** `POST /register`  
**Controller:** `Auth\RegisteredUserController@store`

**Tests:**
- ✅ `test_registration_screen_can_be_rendered`
- ✅ `test_new_users_can_register`
- ✅ Multiple validation tests (15 tests)

---

### AU-03: Email Verification
**Status:** ✅ Fully Tested  
**Tests:** 3 tests passing

---

### AU-04: Password Reset
**Status:** ✅ Fully Tested  
**Tests:** 4 tests passing

---

### AU-05: Password Update
**Status:** ✅ Fully Tested  
**Tests:** 2 tests passing

---

### AU-06: Password Confirmation
**Status:** ✅ Fully Tested  
**Tests:** 3 tests passing

---

## Middleware & Security

### MW-01: Role-Based Access Control
**Status:** ✅ Fully Tested  
**Tests:** 16 tests passing

**Covers:**
- Student middleware
- Advisor middleware
- Admin middleware
- Route protection for all HTTP methods

---

### MW-02: Rate Limiting
**Status:** ❌ Not Tested  

**Implemented:**
- General: 60 requests/minute
- Booking: 10 requests/minute
- Slot creation: 20 requests/minute

**Recommended Tests:**
- [ ] General rate limit enforced
- [ ] Booking rate limit enforced
- [ ] Slot creation rate limit enforced
- [ ] Rate limit resets after time window

---

## Background Jobs & Commands

### BG-01: Auto-Cancel Stale Appointments
**Status:** ✅ Fully Tested  
**Command:** `php artisan appointments:auto-cancel`

**Tests:**
- ✅ `test_stale_pending_appointments_are_cancelled`
- ✅ `test_recent_pending_appointments_are_not_cancelled`
- ✅ `test_approved_appointments_marked_as_no_show`
- ✅ `test_recent_approved_appointments_not_marked_as_no_show`
- ✅ `test_completed_appointments_are_not_affected`
- ✅ `test_multiple_appointments_processed_correctly`

---

## Summary Statistics

### Test Coverage by Category

| Category | Features | Fully Tested | Partially Tested | Not Tested | Coverage % |
|----------|----------|--------------|------------------|------------|------------|
| Student | 9 | 6 | 2 | 1 | 67% |
| Advisor | 11 | 6 | 0 | 5 | 55% |
| Admin | 10 | 1 | 1 | 8 | 10% |
| Common | 8 | 2 | 1 | 5 | 25% |
| Auth | 6 | 6 | 0 | 0 | 100% |
| Security | 2 | 1 | 0 | 1 | 50% |
| **TOTAL** | **46** | **22** | **4** | **20** | **48%** |

### Tests by Status

- ✅ **194 Passing Tests**
- ❌ **7 Failing Tests**
- 🚫 **20+ Features Without Tests**

### Priority Test Development

**🔴 Critical (Must Add):**
1. Admin Faculty CRUD (6 operations)
2. Admin Student CRUD (6 operations)
3. Advisor MOM Notes (2 operations)
4. Feedback System (1 operation)

**🟡 High Priority:**
5. Notification System (3 operations)
6. Calendar System (3 operations)
7. Resource Management (6 operations)
8. Admin Bookings (3 operations)

**🟢 Medium Priority:**
9. Admin Notices (5 operations)
10. Bulk Operations (1 operation)
11. Rate Limiting Tests (3 tests)

---

**Report Generated:** January 24, 2026  
**Total Features Documented:** 46  
**Total Test Recommendations:** 100+
