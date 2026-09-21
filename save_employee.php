<?php
// save_employee.php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add-employee.php");
    exit;
}

// ========== 1. Personal Details ==========
$emp_no              = trim($_POST['emp_no'] ?? '');
$gender              = $_POST['gender'] ?? '';
$title               = $_POST['title'] ?? '';
$initials            = $_POST['initials'] ?? '';
$denoted_by_initial  = $_POST['denoted_by_initial'] ?? '';
$first_name          = $_POST['first_name'] ?? '';
$middle_name         = $_POST['middle_name'] ?? '';
$last_name           = $_POST['last_name'] ?? '';
$name_with_initials  = $_POST['name_with_initials'] ?? '';
$full_name           = $_POST['full_name'] ?? '';
$office_use_name     = $_POST['office_use_name'] ?? '';
$language            = $_POST['language'] ?? '';
$passport_no         = $_POST['passport_no'] ?? '';
$marital_status      = $_POST['marital_status'] ?? '';
$date_of_birth       = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
$nationality         = $_POST['nationality'] ?? '';
$age                 = $_POST['age'] ?? null;
$race                = $_POST['race'] ?? '';
$birth_place         = $_POST['birth_place'] ?? '';
$religion            = $_POST['religion'] ?? '';
$nic                 = $_POST['nic'] ?? '';
$blood_group         = $_POST['blood_group'] ?? '';
$employee_id_issue_date = !empty($_POST['employee_id_issue_date']) ? $_POST['employee_id_issue_date'] : null;
$distance_from_work  = $_POST['distance_from_work'] ?? '';

// Photo upload
$photo = null;
$new_photo_uploaded = false;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $photo = $uploadDir . uniqid('emp_') . '.' . $ext;
    move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    $new_photo_uploaded = true;
}

// Check if employee already exists
$check = $conn->prepare("SELECT id, photo FROM employees WHERE emp_no = ?");
$check->bind_param("s", $emp_no);
$check->execute();
$result = $check->get_result();
$existing = $result->fetch_assoc();
$check->close();

$is_update = ($existing !== null);
$employee_id = $is_update ? (int)$existing['id'] : 0;

if ($is_update) {
    // ========== UPDATE employees ==========
    if ($new_photo_uploaded) {
        $sql = "UPDATE employees SET 
            gender=?, title=?, initials=?, denoted_by_initial=?, first_name=?, middle_name=?, last_name=?, 
            name_with_initials=?, full_name=?, office_use_name=?, language=?, passport_no=?, marital_status=?, 
            date_of_birth=?, nationality=?, age=?, race=?, birth_place=?, religion=?, nic=?, blood_group=?, 
            employee_id_issue_date=?, distance_from_work=?, photo=? 
            WHERE emp_no=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssssssssisssssssss",
            $gender, $title, $initials, $denoted_by_initial, $first_name, $middle_name, $last_name,
            $name_with_initials, $full_name, $office_use_name, $language, $passport_no, $marital_status,
            $date_of_birth, $nationality, $age, $race, $birth_place, $religion, $nic, $blood_group,
            $employee_id_issue_date, $distance_from_work, $photo, $emp_no
        );
    } else {
        $sql = "UPDATE employees SET 
            gender=?, title=?, initials=?, denoted_by_initial=?, first_name=?, middle_name=?, last_name=?, 
            name_with_initials=?, full_name=?, office_use_name=?, language=?, passport_no=?, marital_status=?, 
            date_of_birth=?, nationality=?, age=?, race=?, birth_place=?, religion=?, nic=?, blood_group=?, 
            employee_id_issue_date=?, distance_from_work=? 
            WHERE emp_no=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssssssssisssssss",
            $gender, $title, $initials, $denoted_by_initial, $first_name, $middle_name, $last_name,
            $name_with_initials, $full_name, $office_use_name, $language, $passport_no, $marital_status,
            $date_of_birth, $nationality, $age, $race, $birth_place, $religion, $nic, $blood_group,
            $employee_id_issue_date, $distance_from_work, $emp_no
        );
    }

    if (!$stmt->execute()) {
        die("Error updating employee: " . $stmt->error);
    }
    $stmt->close();

} else {
    // ========== INSERT employees ==========
    $sql = "INSERT INTO employees 
        (emp_no, gender, title, initials, denoted_by_initial, first_name, middle_name, last_name, 
         name_with_initials, full_name, office_use_name, language, passport_no, marital_status, 
         date_of_birth, nationality, age, race, birth_place, religion, nic, blood_group, 
         employee_id_issue_date, distance_from_work, photo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssssssssssissssssss",
        $emp_no, $gender, $title, $initials, $denoted_by_initial, $first_name, $middle_name, $last_name,
        $name_with_initials, $full_name, $office_use_name, $language, $passport_no, $marital_status,
        $date_of_birth, $nationality, $age, $race, $birth_place, $religion, $nic, $blood_group,
        $employee_id_issue_date, $distance_from_work, $photo
    );

    if (!$stmt->execute()) {
        die("Error saving employee: " . $stmt->error);
    }
    $employee_id = $conn->insert_id;
    $stmt->close();
}

// ========== 2. Official Details ==========
$employee_office_id     = $_POST['employee_office_id'] ?? '';
$tax_payee_no           = $_POST['tax_payee_no'] ?? '';
$company_name           = $_POST['company_name'] ?? '';
$proximity_card_no      = $_POST['proximity_card_no'] ?? '';
$job_source             = $_POST['job_source'] ?? '';
$division_name          = $_POST['division_name'] ?? '';
$attendance_account_no  = $_POST['attendance_account_no'] ?? '';
$manpower_company       = $_POST['manpower_company'] ?? '';
$sub_division_name      = $_POST['sub_division_name'] ?? '';
$is_active_employee     = $_POST['is_active_employee'] ?? 0;
$salary_process         = $_POST['salary_process'] ?? 0;
$department_name        = $_POST['department_name'] ?? '';
$ppf_epf_no             = $_POST['ppf_epf_no'] ?? '';
$salary_period          = $_POST['salary_period'] ?? 'Monthly';
$sub_department_name    = $_POST['sub_department_name'] ?? '';
$employee_fund          = $_POST['employee_fund'] ?? '';
$basic_salary           = $_POST['basic_salary'] ?? 0;
$section_name           = $_POST['section_name'] ?? '';
$etf_no                 = $_POST['etf_no'] ?? '';
$statute_name           = $_POST['statute_name'] ?? '';
$sub_section_name       = $_POST['sub_section_name'] ?? '';
$is_tax_payee           = $_POST['is_tax_payee'] ?? 0;
$next_salary_increment_date = !empty($_POST['next_salary_increment_date']) ? $_POST['next_salary_increment_date'] : null;
$base_customer_site     = $_POST['base_customer_site'] ?? '';
$erp_code               = $_POST['erp_code'] ?? '';

if ($is_update) {
    // Check if official record exists
    $chk = $conn->prepare("SELECT id FROM employee_official WHERE employee_id = ?");
    $chk->bind_param("i", $employee_id);
    $chk->execute();
    $has_official = $chk->get_result()->num_rows > 0;
    $chk->close();

    if ($has_official) {
        $sql2 = "UPDATE employee_official SET 
            employee_office_id=?, tax_payee_no=?, company_name=?, proximity_card_no=?, job_source=?, 
            division_name=?, attendance_account_no=?, manpower_company=?, sub_division_name=?, 
            is_active_employee=?, salary_process=?, department_name=?, ppf_epf_no=?, salary_period=?, 
            sub_department_name=?, employee_fund=?, basic_salary=?, section_name=?, etf_no=?, 
            statute_name=?, sub_section_name=?, is_tax_payee=?, next_salary_increment_date=?, 
            base_customer_site=?, erp_code=? 
            WHERE employee_id=?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("sssssssssiiissssdssssisssi",
            $employee_office_id, $tax_payee_no, $company_name, $proximity_card_no, $job_source,
            $division_name, $attendance_account_no, $manpower_company, $sub_division_name, 
            $is_active_employee, $salary_process, $department_name, $ppf_epf_no, $salary_period, 
            $sub_department_name, $employee_fund, $basic_salary, $section_name, $etf_no, 
            $statute_name, $sub_section_name, $is_tax_payee, $next_salary_increment_date, 
            $base_customer_site, $erp_code, $employee_id
        );
    } else {
        $sql2 = "INSERT INTO employee_official 
            (employee_id, employee_office_id, tax_payee_no, company_name, proximity_card_no, job_source, 
             division_name, attendance_account_no, manpower_company, sub_division_name, is_active_employee, 
             salary_process, department_name, ppf_epf_no, salary_period, sub_department_name, employee_fund, 
             basic_salary, section_name, etf_no, statute_name, sub_section_name, is_tax_payee, 
             next_salary_increment_date, base_customer_site, erp_code) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("isssssssssiiissssdssssisss",
            $employee_id, $employee_office_id, $tax_payee_no, $company_name, $proximity_card_no, $job_source,
            $division_name, $attendance_account_no, $manpower_company, $sub_division_name, $is_active_employee,
            $salary_process, $department_name, $ppf_epf_no, $salary_period, $sub_department_name, $employee_fund,
            $basic_salary, $section_name, $etf_no, $statute_name, $sub_section_name, $is_tax_payee,
            $next_salary_increment_date, $base_customer_site, $erp_code
        );
    }
} else {
    $sql2 = "INSERT INTO employee_official 
        (employee_id, employee_office_id, tax_payee_no, company_name, proximity_card_no, job_source, 
         division_name, attendance_account_no, manpower_company, sub_division_name, is_active_employee, 
         salary_process, department_name, ppf_epf_no, salary_period, sub_department_name, employee_fund, 
         basic_salary, section_name, etf_no, statute_name, sub_section_name, is_tax_payee, 
         next_salary_increment_date, base_customer_site, erp_code) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("isssssssssiiissssdssssisss",
        $employee_id, $employee_office_id, $tax_payee_no, $company_name, $proximity_card_no, $job_source,
        $division_name, $attendance_account_no, $manpower_company, $sub_division_name, $is_active_employee,
        $salary_process, $department_name, $ppf_epf_no, $salary_period, $sub_department_name, $employee_fund,
        $basic_salary, $section_name, $etf_no, $statute_name, $sub_section_name, $is_tax_payee,
        $next_salary_increment_date, $base_customer_site, $erp_code
    );
}
$stmt2->execute();
$stmt2->close();

// ========== 3. Address Details ==========
$address_no     = $_POST['address_no'] ?? '';
$contact_type   = $_POST['contact_type'] ?? '';
$postal_code    = $_POST['postal_code'] ?? '';
$work_tel       = $_POST['work_tel'] ?? '';
$city           = $_POST['city'] ?? '';
$country        = $_POST['country'] ?? 'Sri Lanka';
$office_email   = $_POST['office_email'] ?? '';
$district       = $_POST['district'] ?? '';
$tel_no         = $_POST['tel_no'] ?? '';
$private_email  = $_POST['private_email'] ?? '';
$province       = $_POST['province'] ?? '';
$mobile_no      = $_POST['mobile_no'] ?? '';
$comments       = $_POST['comments'] ?? '';

if ($is_update) {
    $chk = $conn->prepare("SELECT id FROM employee_address WHERE employee_id = ?");
    $chk->bind_param("i", $employee_id);
    $chk->execute();
    $has_address = $chk->get_result()->num_rows > 0;
    $chk->close();

    if ($has_address) {
        $sql3 = "UPDATE employee_address SET 
            address_no=?, contact_type=?, postal_code=?, work_tel=?, city=?, country=?, office_email=?, 
            district=?, tel_no=?, private_email=?, province=?, mobile_no=?, comments=? 
            WHERE employee_id=?";
        $stmt3 = $conn->prepare($sql3);
        $stmt3->bind_param("sssssssssssssi",
            $address_no, $contact_type, $postal_code, $work_tel, $city, $country, $office_email,
            $district, $tel_no, $private_email, $province, $mobile_no, $comments, $employee_id
        );
    } else {
        $sql3 = "INSERT INTO employee_address 
            (employee_id, address_no, contact_type, postal_code, work_tel, city, country, office_email, 
             district, tel_no, private_email, province, mobile_no, comments) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt3 = $conn->prepare($sql3);
        $stmt3->bind_param("isssssssssssss",
            $employee_id, $address_no, $contact_type, $postal_code, $work_tel, $city, $country, $office_email,
            $district, $tel_no, $private_email, $province, $mobile_no, $comments
        );
    }
} else {
    $sql3 = "INSERT INTO employee_address 
        (employee_id, address_no, contact_type, postal_code, work_tel, city, country, office_email, 
         district, tel_no, private_email, province, mobile_no, comments) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("isssssssssssss",
        $employee_id, $address_no, $contact_type, $postal_code, $work_tel, $city, $country, $office_email,
        $district, $tel_no, $private_email, $province, $mobile_no, $comments
    );
}
$stmt3->execute();
$stmt3->close();

// ========== 4. Job Details ==========
$designation            = $_POST['designation'] ?? '';
$resign_date            = !empty($_POST['resign_date']) ? $_POST['resign_date'] : null;
$reporting_supervisor   = $_POST['reporting_supervisor'] ?? '';
$join_date              = !empty($_POST['join_date']) ? $_POST['join_date'] : null;
$leaving_type           = $_POST['leaving_type'] ?? '';
$job_level              = $_POST['job_level'] ?? '';
$appointment_date       = !empty($_POST['appointment_date']) ? $_POST['appointment_date'] : null;
$retirement_date        = !empty($_POST['retirement_date']) ? $_POST['retirement_date'] : null;
$rejoin_date            = !empty($_POST['rejoin_date']) ? $_POST['rejoin_date'] : null;
$work_category          = $_POST['work_category'] ?? '';
$grade                  = $_POST['grade'] ?? '';
$confirmation_date      = !empty($_POST['confirmation_date']) ? $_POST['confirmation_date'] : null;
$job_category           = $_POST['job_category'] ?? '';
$labor_cost_type        = $_POST['labor_cost_type'] ?? '';
$contract_period_months = $_POST['contract_period_months'] ?? null;
$probation_period_months= $_POST['probation_period_months'] ?? null;
$job_comment            = $_POST['job_comment'] ?? '';
$job_specification      = $_POST['job_specification'] ?? '';
$job_description        = $_POST['job_description'] ?? '';

if ($is_update) {
    $chk = $conn->prepare("SELECT id FROM employee_job WHERE employee_id = ?");
    $chk->bind_param("i", $employee_id);
    $chk->execute();
    $has_job = $chk->get_result()->num_rows > 0;
    $chk->close();

    if ($has_job) {
        $sql4 = "UPDATE employee_job SET 
            designation=?, resign_date=?, reporting_supervisor=?, join_date=?, leaving_type=?, job_level=?, 
            appointment_date=?, retirement_date=?, rejoin_date=?, work_category=?, grade=?, confirmation_date=?, 
            job_category=?, labor_cost_type=?, contract_period_months=?, probation_period_months=?, comment=?, 
            job_specification=?, job_description=? 
            WHERE employee_id=?";
        $stmt4 = $conn->prepare($sql4);
        $stmt4->bind_param("sssssssssssssiiisssi",
            $designation, $resign_date, $reporting_supervisor, $join_date, $leaving_type, $job_level,
            $appointment_date, $retirement_date, $rejoin_date, $work_category, $grade, $confirmation_date,
            $job_category, $labor_cost_type, $contract_period_months, $probation_period_months, $job_comment,
            $job_specification, $job_description, $employee_id
        );
    } else {
        $sql4 = "INSERT INTO employee_job 
            (employee_id, designation, resign_date, reporting_supervisor, join_date, leaving_type, job_level, 
             appointment_date, retirement_date, rejoin_date, work_category, grade, confirmation_date, 
             job_category, labor_cost_type, contract_period_months, probation_period_months, comment, 
             job_specification, job_description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt4 = $conn->prepare($sql4);
        $stmt4->bind_param("isssssssssssssiiisss",
            $employee_id, $designation, $resign_date, $reporting_supervisor, $join_date, $leaving_type, $job_level,
            $appointment_date, $retirement_date, $rejoin_date, $work_category, $grade, $confirmation_date,
            $job_category, $labor_cost_type, $contract_period_months, $probation_period_months, $job_comment,
            $job_specification, $job_description
        );
    }
} else {
    $sql4 = "INSERT INTO employee_job 
        (employee_id, designation, resign_date, reporting_supervisor, join_date, leaving_type, job_level, 
         appointment_date, retirement_date, rejoin_date, work_category, grade, confirmation_date, 
         job_category, labor_cost_type, contract_period_months, probation_period_months, comment, 
         job_specification, job_description) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt4 = $conn->prepare($sql4);
    $stmt4->bind_param("isssssssssssssiiisss",
        $employee_id, $designation, $resign_date, $reporting_supervisor, $join_date, $leaving_type, $job_level,
        $appointment_date, $retirement_date, $rejoin_date, $work_category, $grade, $confirmation_date,
        $job_category, $labor_cost_type, $contract_period_months, $probation_period_months, $job_comment,
        $job_specification, $job_description
    );
}
$stmt4->execute();
$stmt4->close();

// ========== 5. Attendance Rules ==========
$attendance_group       = $_POST['attendance_group'] ?? '';
$from_date              = !empty($_POST['from_date']) ? $_POST['from_date'] : null;
$to_date                = !empty($_POST['to_date']) ? $_POST['to_date'] : null;
$is_permanent           = isset($_POST['is_permanent']) ? 1 : 0;
$work_schedule          = $_POST['work_schedule'] ?? '';
$check_by_timezone      = $_POST['check_by_timezone'] ?? 0;
$allow_temporary_shifts = $_POST['allow_temporary_shifts'] ?? 0;
$checkin_is_must        = $_POST['checkin_is_must'] ?? 0;
$allow_flexy_attendance = $_POST['allow_flexy_attendance'] ?? 0;
$checkout_is_must       = $_POST['checkout_is_must'] ?? 0;
$ot_applicable          = $_POST['ot_applicable'] ?? 0;

if ($is_update) {
    $chk = $conn->prepare("SELECT id FROM employee_attendance_rules WHERE employee_id = ?");
    $chk->bind_param("i", $employee_id);
    $chk->execute();
    $has_att = $chk->get_result()->num_rows > 0;
    $chk->close();

    if ($has_att) {
        $sql5 = "UPDATE employee_attendance_rules SET 
            attendance_group=?, from_date=?, to_date=?, is_permanent=?, work_schedule=?, 
            check_by_timezone=?, allow_temporary_shifts=?, checkin_is_must=?, allow_flexy_attendance=?, 
            checkout_is_must=?, ot_applicable=? 
            WHERE employee_id=?";
        $stmt5 = $conn->prepare($sql5);
        $stmt5->bind_param("sssisiiiiiii",
            $attendance_group, $from_date, $to_date, $is_permanent, $work_schedule,
            $check_by_timezone, $allow_temporary_shifts, $checkin_is_must, $allow_flexy_attendance,
            $checkout_is_must, $ot_applicable, $employee_id
        );
    } else {
        $sql5 = "INSERT INTO employee_attendance_rules 
            (employee_id, attendance_group, from_date, to_date, is_permanent, work_schedule, 
             check_by_timezone, allow_temporary_shifts, checkin_is_must, allow_flexy_attendance, 
             checkout_is_must, ot_applicable) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt5 = $conn->prepare($sql5);
        $stmt5->bind_param("isssisiiiiii",
            $employee_id, $attendance_group, $from_date, $to_date, $is_permanent, $work_schedule,
            $check_by_timezone, $allow_temporary_shifts, $checkin_is_must, $allow_flexy_attendance,
            $checkout_is_must, $ot_applicable
        );
    }
} else {
    $sql5 = "INSERT INTO employee_attendance_rules 
        (employee_id, attendance_group, from_date, to_date, is_permanent, work_schedule, 
         check_by_timezone, allow_temporary_shifts, checkin_is_must, allow_flexy_attendance, 
         checkout_is_must, ot_applicable) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt5 = $conn->prepare($sql5);
    $stmt5->bind_param("isssisiiiiii",
        $employee_id, $attendance_group, $from_date, $to_date, $is_permanent, $work_schedule,
        $check_by_timezone, $allow_temporary_shifts, $checkin_is_must, $allow_flexy_attendance,
        $checkout_is_must, $ot_applicable
    );
}
$stmt5->execute();
$stmt5->close();

// Success message
$msg = $is_update ? "Employee updated successfully!" : "Employee saved successfully! Employee ID: $employee_id";
echo "<script>
    alert('$msg');
    window.location.href = 'add-employee.php';
</script>";

$conn->close();
?>