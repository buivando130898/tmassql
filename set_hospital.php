<?php
require 'restful_api.php';
require 'connect.php';
require 'setvip.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');
 
function tokenlogin($token) {
	include('connect.php');
	$sql = "SELECT manager, setting, clinic, acc FROM acc WHERE token = '$token'";
    mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row;            
		}
	}
	return false;
}

function update_time_id($clinic) {
        $time_id = strtotime(date('Y-m-d G:i:s'));
        include('connect.php');
        $sql = "UPDATE clinic SET time_id = $time_id WHERE identifier = '$clinic' ";
        // echo $sql;
        mysqli_set_charset($conn, 'UTF8');
        $result = $conn->query($sql);
        $conn->close();
}

function check_clinic_expected($day, $clinic, $service) {
	include('connect.php');
    $sql = "SELECT COUNT(clinic_expected) AS sum FROM medical_diary WHERE clinic_expected =  '$clinic' AND service = '$service' AND schedule= '$day'  ";
    //echo $sql;
    $result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {    
        return $row["sum"];
        
        }
    } else return 0 ;
}

/////////////////////////////////////////////////////////////
function activate_clinic_expected($id, $day, $service) {
   
	include('connect.php');
    $sql = "SELECT DISTINCT clinic FROM service WHERE identifier = '$service' AND (clinic IN (SELECT identifier FROM clinic_status WHERE day_activate = '$day'))";
    // echo "\n".$sql."\n";
    $result = $conn->query($sql);
	if ($result->num_rows == 1) {
		while($row = $result->fetch_assoc()) {
            $clinic = $row["clinic"];
            $sql2 = "UPDATE medical_diary SET clinic_expected = '$clinic' WHERE schedule = '$day' AND service = '$service'";
           // echo "\n".$sql2."\n";
            $conn->query($sql2);

        }
	} 
    
    else {
        if ($result->num_rows > 1) {
            $min = 1000000;
            $clinic = "";
            while($row = $result->fetch_assoc()) {

                if($min == 1000000) {
                    $clinic = $row["clinic"];
                    $min = check_clinic_expected($day,  $row["clinic"], $service);
                }

                if($min > check_clinic_expected($day,  $row["clinic"], $service)) {
                    $clinic = $row["clinic"];
                    $min = check_clinic_expected($day,  $row["clinic"], $service);
                }
            }
            $sql2 = "UPDATE medical_diary SET clinic_expected = '$clinic' WHERE schedule = '$day' AND service = '$service' AND customer_identifier='$id'";
            $conn->query($sql2);
        }
    }
}

// code
function run_clinic_expected($id, $day){
	include('connect.php');
    $sql = "SELECT * FROM `medical_diary` WHERE customer_identifier = '$id' AND schedule = '$day' AND clinic_expected IS NULL";
    // echo "\n".$sql."\n";
    $result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
            $service = $row["service"];
            activate_clinic_expected($id, $day, $service);   
        }
	}
}

function getCustomer_info($identifier){
	include('connect.php');
	$sql = "SELECT name, birthday, sex FROM customer WHERE identifier = '$identifier'";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$data[] = $row;
		}
	}
	$conn->close();
	return $data[0];
}

// Mã => info service
function get_service_info($id){
	include('connect.php');
	$sql = "SELECT * FROM service WHERE identifier = '$id'";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row;
		}
	} else {
        return false;
    }
	$conn->close();
	
}


function medical_diary_was_clinic_begin_check($customer_identifier) {
    $minTime = 10000000;
    $maxTime = 0;
    $th = 2;
    $data_name = "";
    include('connect.php');
    $time = date('Y-m-d');
    // 3. Tìm ra các dịch vụ cần khám tiếp theo
    $sql = "SELECT service, proviso FROM `medical_diary` WHERE customer_identifier = '$customer_identifier' AND schedule = '$time' AND status <> 'Đã khám' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time' AND status <> 'Đã khám')";
    //echo $sql;
    mysqli_set_charset($conn, 'UTF8');
    $result = $conn->query($sql);
    if ( !($result->num_rows > 0)) {
        //$noti = "Hoàn thành";
        return false;
    
    } else {
        // Lấy các thông tin dịch vụ khám tiếp theo
        $sql_add  = "";
        $clinic_dk = "";
        while($row = $result->fetch_assoc()) {
            if( !$row["proviso"] ) {
                if($sql_add != "" ){
                    $sql_add =  $sql_add." OR identifier = '".$row["service"]."'";
                } else {
                    $sql_add =  $sql_add." identifier = '".$row["service"]."'";
                }
            } else {
                if(check_service_dk($row["proviso"], $customer_identifier)){
                    if($sql_add != "" ){
                        $sql_add =  $sql_add." OR identifier = '".$row["service"]."'";
                    } else {
                        $sql_add =  $sql_add." identifier = '".$row["service"]."'";
                    }
                } else {
                    if($clinic_dk != "" ){
                        $clinic_dk =  $clinic_dk." AND service != '".$row["service"]."'";
                    } else {
                        $clinic_dk =  $clinic_dk." service != '".$row["service"]."'";
                    }
                }
            }
        }
        //echo "\n   ".$sql_add."\n";
        // 4. Lấy thông tin các phòng có dịch vụ đấy
        $sql = "SELECT DISTINCT clinic, intendTime FROM `service` WHERE status='Hoạt động' AND ( ".$sql_add." ) AND clinic IN (SELECT identifier FROM clinic WHERE status = 'Hoạt động') ORDER BY intendTime ASC";
        //  echo "\n".$sql."\n";
        mysqli_set_charset($conn, 'UTF8');
        $result = $conn->query($sql);
        if ( !($result->num_rows > 0)) {
            $noti = "Không có phòng hoạt động";
            return false;
        } else {
            while($row = $result->fetch_assoc()) {
            // 5. Tính thời gian chờ của phòng 
                // Thời gian sẽ khám ở phòng đấy
                $time_are = time_all_clinic_check( $row["clinic"],  $customer_identifier, $clinic_dk);
                // Thời gian chờ khám ở phòng đấy
                $time_wait = countClinic_waite($row["clinic"],  $customer_identifier);
                // echo "\n Phòng: khám". $row["clinic"]."  :  ". $time_are."\n";
                // echo "\n Phòng: chờ". $row["clinic"]."  :  ". $time_wait."\n" ;
				// echo "\n ============================================ \n";

                // Tìm ra phòng cần đến.
                    //1, Có các phòng có thời gian chờ = 0   => chọn phòng có thời gian làm lâu nhất
                    //2, Tất cả các phòng có thời gian chờ != 0   => chọn phòng có thời gian chờ ngắn nhất

                if($time_wait == 0) {
                    $th = 1;
                    if($time_are > $maxTime ) {
                        $maxTime = $time_are;
                        $data = $row["clinic"];
                        $minTime = 10000000;

                    }
                }

                if($th == 2   &&  $time_wait < $minTime ) {
                    $minTime = $time_wait;
                    $data = $row["clinic"];
                }
            }

			// echo $data;

            //  6. Cập nhật chờ khám
            //  Cập nhật toàn bộ các dịch vụ trong phòng đấy  ???
            $time_input = date("G:i:s");
            // //echo $time;                            
            // if(!$clinic_dk) {
            //     $sql = "UPDATE medical_diary SET clinic = '$data', status = 'Chờ khám', time_input = '$time_input' WHERE schedule = '$time'  AND status='Chưa khám' AND customer_identifier = '$customer_identifier' AND service IN (SELECT identifier FROM `service` WHERE clinic = '$data')   AND service IN (SELECT service FROM `medical_diary` WHERE customer_identifier = '$customer_identifier' AND schedule = '$time' AND status = 'Chưa khám' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time' AND status = 'Chưa khám'))";
            // } else {
            //     $sql = "UPDATE medical_diary SET clinic = '$data', status = 'Chờ khám', time_input = '$time_input' WHERE $clinic_dk AND schedule = '$time'  AND status='Chưa khám' AND customer_identifier = '$customer_identifier' AND service IN (SELECT identifier FROM `service` WHERE clinic = '$data')   AND service IN (SELECT service FROM `medical_diary` WHERE customer_identifier = '$customer_identifier' AND schedule = '$time' AND status = 'Chưa khám' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time' AND status = 'Chưa khám'))";

            // }

            if(!$clinic_dk) {
                $sql = "UPDATE medical_diary SET clinic = '$data', status = 'Chờ khám', time_input = '$time_input' WHERE schedule = '$time'  AND status='Chưa khám' AND customer_identifier = '$customer_identifier' AND service IN (SELECT identifier FROM `service` WHERE clinic = '$data')  AND location = (SELECT * FROM (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time' AND status = 'Chưa khám') AS x)";
            } else {
            $sql = "UPDATE medical_diary SET clinic = '$data', status = 'Chờ khám', time_input = '$time_input' WHERE $clinic_dk AND schedule = '$time'  AND status='Chưa khám' AND customer_identifier = '$customer_identifier' AND service IN (SELECT identifier FROM `service` WHERE clinic = '$data')  AND location = (SELECT * FROM (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time' AND status = 'Chưa khám') AS x)";
            }
            
            //$sql = "UPDATE medical_diary SET clinic = '$data', status = 'Chờ khám', time_input = '$time_input' WHERE schedule = '$time'  AND status='Chưa khám' AND customer_identifier = '$customer_identifier' AND service IN (SELECT identifier FROM `service` WHERE clinic = '$data')";
            //echo $sql."\n";
            $maxTime = time_all_clinic( $data,  $customer_identifier, $clinic_dk);
            
            // mysqli_set_charset($conn, 'UTF8');
            // $result = $conn->query($sql);

            $sql = "SELECT * FROM clinic WHERE identifier = '$data'";
            mysqli_set_charset($conn, 'UTF8');
            $result = $conn->query($sql);
            if (($result->num_rows > 0)) {
                while($row = $result->fetch_assoc()) {
                    $data_name = $row["name"];
                }
            }
            $conn->close();

            if($minTime == 10000000) {
                $minTime = 0;
            }
            //$noti =  "Phòng:  ".$data." - ".$data_name."\nChờ:      ".$minTime." phút"."\nKhám:   ".$maxTime." phút";
            $noti = [];
            $noti["clinic"] = $data;
            $noti["time_wait_new"] = $minTime;
            $noti["time_are_new"] = $maxTime;
        }
    }
    return $noti;
}


function countClinic_waite_check($clinic, $customer_id, $time_input){
    $timeClinic_are = check_clinic_are_time_info($clinic);
	include('connect.php');
    $time = date('Y-m-d');
    $sql = "SELECT SUM(intendTime) AS count_time FROM medical_diary WHERE  ((time_input < '$time_input' AND vip = (SELECT DISTINCT  vip FROM medical_diary WHERE customer_identifier = '$customer_id' AND schedule ='$time'  LIMIT 1) OR  vip < (SELECT DISTINCT  vip FROM medical_diary WHERE customer_identifier = '$customer_id' AND schedule ='$time'  LIMIT 1)) ) AND	customer_identifier <> '$customer_id' AND schedule = '$time' AND clinic = '$clinic' AND status = 'Chờ khám' ";
    // echo "\n".$sql."\n";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
            //echo "\n Giá trị".$row["count_time"];
            if($row["count_time"] == NULL || $row["count_time"] == "NULL" ) {
                return 0 + $timeClinic_are;
            }
			return $row["count_time"] + $timeClinic_are;
		}
	}
	$conn->close();
}


function get_clinic_name($id) {
    include('connect.php');
    $sql = "SELECT name FROM clinic WHERE identifier = '$id'";
    mysqli_set_charset($conn, 'UTF8');
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            return $row["name"];
        }
    } else {
        return false;
    }

}


// 2. Tìm phòng đấy
function get_clinic_check($service, $time_date) {
    include('connect.php');
    $sql = "SELECT identifier FROM clinic_status WHERE day_activate = '$time_date' AND  (time_test < time_all) AND identifier IN (SELECT  clinic FROM service WHERE identifier = '$service' AND status = 'Hoạt động')";
    mysqli_set_charset($conn, 'UTF8');
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            return $row["identifier"];
        }
    } else {
        return false;
    }
}

function add_time_clinic($clinic, $time_date, $time) {
    include('connect.php');
    $sql = "UPDATE clinic_status SET time_test = time_test + $time WHERE day_activate='$time_date'AND identifier='$clinic'";
    //echo $sql;
    mysqli_set_charset($conn, 'UTF8');
    $result = $conn->query($sql);    
}


function check_service_dk($dk , $customer_id) {
    include('connect.php');
    $time = date('Y-m-d');
    $dk_array =  explode(' ', $dk);
    $max = count($dk_array);

    $dk_json = trim(json_encode($dk_array), "[");
    $dk_json = trim($dk_json, "]");
	$sql = "SELECT COUNT(*) AS max  FROM medical_diary WHERE customer_identifier = '$customer_id' AND   schedule = '$time' AND status = 'Đã khám'  AND service IN ($dk_json)";
    //echo $sql;
    mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
			if($max == $row["max"]){
                return true;  
            }
            else return false;
			      
		}
        
	}
	$conn->close();
	return false;

}

function check_clinic_location($clinic_was , $clicic ) {
	include('connect.php');
	$sql = "SELECT * FROM clinic WHERE identifier = '$clicic' AND location IN (SELECT location FROM clinic WHERE identifier = '$clinic_was') ";
    // echo $sql."\n";
    mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return true;
		}
	}
	$conn->close();
	return false;
}

function time_all_clinic($clicic_id, $customer_id, $clinic_dk ) {
	include('connect.php');
    $time = date('Y-m-d');
	//$sql = " SELECT SUM(intendTime) AS allTime FROM medical_diary WHERE (service IN (SELECT identifier FROM `service` WHERE clinic = '$clicic_id')) AND customer_identifier = '$customer_id' AND schedule = '$time' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_id'  AND schedule = '$time' AND status = 'Chưa khám'); ";
    if(!$clinic_dk) {
    	$sql = " SELECT SUM(intendTime) AS allTime FROM medical_diary WHERE (service IN (SELECT identifier FROM `service` WHERE clinic = '$clicic_id')) AND customer_identifier = '$customer_id' AND schedule = '$time' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_id'  AND schedule = '$time' AND status = 'Chưa khám'); ";
    } else {
	    $sql = " SELECT SUM(intendTime) AS allTime FROM medical_diary WHERE  $clinic_dk AND (service IN (SELECT identifier FROM `service` WHERE clinic = '$clicic_id')) AND customer_identifier = '$customer_id' AND schedule = '$time' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_id'  AND schedule = '$time' AND status = 'Chưa khám'); ";
    }

    // echo $sql."\n";
    mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row["allTime"];
		}
	}
	$conn->close();
}

function time_all_clinic_check($clicic_id, $customer_id, $clinic_dk ) {
	include('connect.php');
    $time = date('Y-m-d');
	//$sql = " SELECT SUM(intendTime) AS allTime FROM medical_diary WHERE (service IN (SELECT identifier FROM `service` WHERE clinic = '$clicic_id')) AND customer_identifier = '$customer_id' AND schedule = '$time' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_id'  AND schedule = '$time' AND status = 'Chưa khám'); ";
    if(!$clinic_dk) {
    	$sql = " SELECT SUM(intendTime) AS allTime FROM medical_diary WHERE (service IN (SELECT identifier FROM `service` WHERE clinic = '$clicic_id')) AND customer_identifier = '$customer_id' AND schedule = '$time' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_id'  AND schedule = '$time' AND status = 'Chờ khám'); ";
    } else {
	    $sql = " SELECT SUM(intendTime) AS allTime FROM medical_diary WHERE  $clinic_dk AND (service IN (SELECT identifier FROM `service` WHERE clinic = '$clicic_id')) AND customer_identifier = '$customer_id' AND schedule = '$time' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_id'  AND schedule = '$time' AND status = 'Chờ khám'); ";
    }
    mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row["allTime"];
		}
	}
	$conn->close();
}

function getServiceId_identifier($stt ) {
	include('connect.php');
	$sql = "SELECT identifier FROM service WHERE stt = $stt";
    mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row["identifier"];
		}
	}
	$conn->close();
	return false;
}


function countClinic($clinic, $count){
	include('connect.php');
    $time = date('Y-m-d');
    $count_time = 0;
	$sql = "SELECT COUNT(*) AS count_time FROM medical_diary WHERE schedule = '$time' AND clinic = '$clinic' AND (status = 'Chờ khám' OR status = 'Đang khám' )";
    //echo "\n".$sql.$count."\n";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$count_time = $row["count_time"];
		}
	}
	$conn->close();
	return $count_time*$count;
}

function check_clinic_are_time_info( $clinic) {
	include('connect.php');
	$time = date('Y-m-d');
    $time_house =  strtotime(date('G:i:s'));
	$sql = "SELECT  MIN(time_input) AS time_input FROM medical_diary  Where clinic  = '$clinic' AND status = 'Đang khám' AND schedule='$time'";
    mysqli_set_charset($conn, 'UTF8');
	//echo $sql;
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
            if($row["time_input"] == NULL || $row["time_input"] == "NULL" ) {
                return 0;
            }
			$time_begin = date($row["time_input"]);            
		}
	}
	$sql = "SELECT   SUM(intendTime) AS time_sum FROM medical_diary  Where clinic  = '$clinic' AND status = 'Đang khám' AND schedule='$time'";
    mysqli_set_charset($conn, 'UTF8');
	// echo $sql;
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$time_sum = $row["time_sum"];    
			$time_begin = strtotime($time_begin) + $time_sum*60;        
		}
	}

    if($time_begin - $time_house < 0) {
        return 0 ;
    }
	return ceil(($time_begin - $time_house)/60) ;
}

function countClinic_waite($clinic, $customer_id){
    $timeClinic_are = check_clinic_are_time_info($clinic);
	include('connect.php');
    $time = date('Y-m-d');
    $sql = "SELECT SUM(intendTime) AS count_time FROM medical_diary WHERE schedule = '$time' AND clinic = '$clinic' AND status = 'Chờ khám'  AND vip <= (SELECT DISTINCT  vip FROM medical_diary WHERE customer_identifier = '$customer_id' AND schedule ='$time'  LIMIT 1)";
    // echo "\n".$sql."\n";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
            //echo "\n Giá trị".$row["count_time"];
            if($row["count_time"] == NULL || $row["count_time"] == "NULL" ) {
                return 0 + $timeClinic_are;
            }
			return $row["count_time"] + $timeClinic_are;
		}
	}

	$conn->close();
}

class api extends restful_api {

	function __construct(){
		parent::__construct();
	}

	function add_doctor(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if(isset( $_GET["name"])  && isset($_GET["birthday"]) && isset($_GET["sex"]) && isset($_GET["phone"]) && isset($_GET["position_type"]))
            {
                include('connect.php');
                $name = $_GET["name"];
                $birthday = $_GET["birthday"];
                $sex = $_GET["sex"];
                $phone = $_GET["phone"];
                $position_type = $_GET["position_type"];
                if(isset($_GET["note"]))
                {
                    $note = $_GET["note"];
                } else {
                    $note = "";
                }
                $time = date('Y-m-d H:i:s ');
                $sql = "INSERT INTO doctor(name, birthday, sex, phone, position_type, note, time_input) VALUE ('$name', '$birthday', '$sex', '$phone', '$position_type', '$note', '$time' )";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Thêm thông tin bác sĩ thành công";
                } else $data = "Erro: Mysql";
                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu";
            $this->response(200, $data);
        }
    }


    function update_doctor(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if(isset( $_GET["name"])  && isset($_GET["birthday"]) && isset($_GET["sex"]) && isset($_GET["phone"]) && isset($_GET["position_type"]))
            {
                include('connect.php');
                $stt  = $_GET["stt"];
                $name = $_GET["name"];
                $birthday = $_GET["birthday"];
                $sex = $_GET["sex"];
                $phone = $_GET["phone"];
                $position_type = $_GET["position_type"];
                if(isset($_GET["note"]))
                {
                    $note = $_GET["note"];
                } else {
                    $note = "";
                }
                $time = date('Y-m-d H:i:s ');
                $sql = "UPDATE doctor SET name = '$name', birthday = '$birthday', sex = '$sex', phone = '$phone', position_type = '$position_type', note = '$note', time_input = '$time' WHERE stt = $stt";
                // echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Cập nhật dữ liệu thành công!";
                } else $data = "Erro: mysql";
                $conn->close();
            } else $data = "Thiếu dữ liệu";
            $this->response(200, $data);
        }
    }

    function add_clinic(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if( isset( $_GET["name"])  && isset( $_GET["identifier"]) && isset( $_GET["location"])   )
            {
                include('connect.php');
                $name = $_GET["name"];
                $identifier = $_GET["identifier"];
                $location = $_GET["location"];
                $note = $_GET["note"];
                $time = date('Y-m-d H:i:s ');
                $sql = "INSERT INTO clinic(name, identifier, location, note, time_input, status) VALUE ('$name', '$identifier', '$location', '$note', '$time', 'Hoạt động' )";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);

                if($result) {
                    $data = "Thêm dữ liệu phòng khám thành công.";
                } else {
                    $data = "Erro: Mysql.";

                }

                $conn->close();
                $data = "Success";
            } else $data = "Erro: Thiếu dữ liệu.";

            $this->response(200, $data);
        }
    }


    function add_service(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else 
            if(isset( $_GET["name"]) && isset( $_GET["clinic"])  && isset($_GET["identifier"]) && isset($_GET["intendTime"]) )
            {
                include('connect.php');
                $name = $_GET["name"];
                $identifier = $_GET["identifier"];
                $intendTime = $_GET["intendTime"];
                $group_service = $_GET["group_service"];
                //$resultTime = $_GET["resultTime"];
                $clinic = $_GET["clinic"];

                if(isset($_GET["note"]))
                {
                    $note = $_GET["note"];
                } else {
                    $note = "";
                }

                $time = date('Y-m-d H:i:s ');
                $sql = "INSERT INTO service(name, identifier, clinic, intendTime, note, time_input, status, group_service, location) VALUE ('$name', '$identifier', '$clinic' ,$intendTime , '$note', '$time', 'Hoạt động', '$group_service', 2)";
                // echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Thêm dữ liệu dịch vụ thành công";
                } else $data = "Erro2";
                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu.";
            $this->response(200, $data);
        }
    }

    function add_clinic_status(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit" )){
                $data = "Bạn không đủ quyền.";
            } else
            if( isset($_GET["identifier"]) && isset($_GET["date_active"]) )
            {
                include('connect.php');
                $identifier = $_GET["identifier"];
                $name = get_clinic_name($identifier);
                $doctor = $_GET["doctor"];
                $date_active = $_GET["date_active"];
                $timeAll = $_GET["time_all"];
                $note = $_GET["note"];
                if($identifier && $date_active && $name) {
                    $sql = "INSERT INTO clinic_status(identifier, name, doctor, day_activate, time_all, note) VALUE ('$identifier', '$name', '$doctor' , '$date_active', $timeAll, '$note' )";
                    //echo $sql;
                    mysqli_set_charset($conn, 'UTF8');
                    $result = $conn->query($sql);
                    if($result) {
                        $data = "Thêm dữ liệu phòng khám thành công";
                    } else $data = "Erro: Mysql";
                    $conn->close();
                    
                } else $data = "Erro: Vui lòng kiểm tra lại dữ liệu.";  
            } else $data = "Erro: Thiếu dữ liệu"; 
            $this->response(200, $data);
        }
    }

    function update_clinic(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if( isset($_GET["identifier"]) && isset($_GET["stt"]) && isset($_GET["name"]) )
            {
                include('connect.php');
                $stt = $_GET["stt"];
                $identifier = $_GET["identifier"];
                $name = $_GET["name"];
                $note = $_GET["note"];
                $location = $_GET["location"];
                $sql = "UPDATE clinic set identifier ='$identifier' , name ='$name' , note='$note' , location = $location  WHERE stt = $stt";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Update dữ liệu phòng khám thành công.";
                } else {
                    $data = "Erro: mysql";
                }
                $conn->close();
                
            } else $data = "Erro: Thiếu dữ liệu";
            $this->response(200, $data);
        }
    }

    function update_clinic_status2(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if( isset($_GET["identifier"]) && isset($_GET["date_active"]) )
            {
                include('connect.php');
                $stt = $_GET["stt"];
                $identifier = $_GET["identifier"];
                $name = $_GET["name"];
                $doctor = $_GET["doctor"];
                $date_active = $_GET["date_active"];
                $time_all = $_GET["time_all"];
                $note = $_GET["note"];

                if($identifier && $date_active) {
                    //$sql = "INSERT INTO clinic_status(identifier, name, doctor, day_activate) VALUE ('$identifier', '$name', '$doctor' , '$date_active' )";
                    $sql = "UPDATE clinic_status set identifier ='$identifier' , name ='$name' , doctor = '$doctor' , day_activate ='$date_active', time_all = $time_all, note='$note'  WHERE stt = $stt";
                    //echo $sql;
                    mysqli_set_charset($conn, 'UTF8');
                    $result = $conn->query($sql);
                    if($result) {
                        $data = "Cập nhật dữ liệu thành công";
                    } else $data = "Erro: Mysql";
                    $conn->close();
                }else $data = "Erro: Lỗi định dạng dữ liệu.";
            } else $data = "Erro: Thiếu dữ liệu";
            $this->response(200, $data);
        }
    }

    function update_service(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $data = "Erro";
            if(isset( $_GET["name"])  && isset($_GET["stt"]) && isset($_GET["intendTime"])  && isset($_GET["resultTime"]) )
            {
                include('connect.php');
                $name = $_GET["name"];
                $stt = $_GET["stt"];
                $intendTime = $_GET["intendTime"];
                $resultTime = $_GET["resultTime"];
                $note = $_GET["note"];
                $time = date('Y-m-d H:i:s ');
                $sql = "UPDATE service SET name = '$name', intendTime = '$intendTime', resultTime = '$resultTime', note = '$note', time_input = '$time' WHERE stt = $stt";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $conn->close();
                $data = "Success";
            } 
            $this->response(200, $data);
        }
    }

    function update_service2(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if(isset( $_GET["name"])  && isset($_GET["identifier"]) && isset($_GET["intendTime"]) )
            {
                include('connect.php');
                $stt = $_GET["stt"];
                $identifier = $_GET["identifier"];
                $clinic = $_GET["clinic"];
                $name = $_GET["name"];
                $intendTime = $_GET["intendTime"];
                $note = $_GET["note"];
                $time = date('Y-m-d H:i:s ');
                $group_service = $_GET["group_service"];
                $sql = "UPDATE service SET name = '$name', intendTime = '$intendTime', note = '$note', time_input = '$time', identifier = '$identifier', clinic = '$clinic', group_service = '$group_service' WHERE stt = $stt";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Cập nhật dữ liệu dịch vụ thành công";
                } else $data = "Erro: Mysql";
                $conn->close();
            }  else $data = "Thiếu dữ liệu";
            $this->response(200, $data);
        }
    }

    // Cập nhật dịch vụ gói khám
    function update_service_type(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if(isset($_GET["stt"]) && isset($_GET["service"]) && isset($_GET["service_name"]) && isset($_GET["day"])  && isset($_GET["intendTime"])    )
            {
                include('connect.php');
                $stt = $_GET["stt"];
                $service = $_GET["service"];
                $service_name = $_GET["service_name"];
                $intendTime = $_GET["intendTime"];
                $day = $_GET["day"];
                $location = $_GET["location"];
                $proviso = $_GET["proviso"];
                $together = $_GET["together"];
                $time = date('Y-m-d H:i:s ');
                $sql = "UPDATE service_type SET service = '$service', service_name = '$service_name', intendTime = $intendTime, day = $day, location = $location, proviso = '$proviso', together = '$together', time_input = '$time'  WHERE stt = $stt";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result > 0) {
                    $data = "Cập nhật dữ liệu gói khám thành công.";
                } else $data = "Erro: mysql";
                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu.";
            $this->response(200, $data);
        }
    }
 
    // Thêm gói khám
    function add_service_type2(){
        
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if(isset($_GET["identifier"]) &&  isset($_GET["name"])  &&  isset($_GET["service"])   &&  isset($_GET["day"]) ) {
                $data = "Erro";
                include('connect.php');
                $identifier = $_GET["identifier"];
                $name = $_GET["name"];
                $service = $_GET["service"];
                $day = $_GET["day"];
                $service_all = get_service_info($service);
                if($service_all) {
                    $service_name = $service_all["name"];
                    $intendTime = $_GET["intendTime"];
                    $location = $_GET["location"];
                    $proviso = $_GET["proviso"];
                    $together = $_GET["together"];
                    if(!$intendTime) {
                        $intendTime = $service_all["intendTime"];
                    }
                    if(!$location) {
                        $location = $service_all["location"];
                    }
                    if(!$proviso) {
                        $proviso = $service_all["proviso"];
                    }
                    if(!$together) {
                        $together = $service_all["together"];
                    }

                    $time = date('Y-m-d H:i:s ');
                    $sql = "INSERT INTO service_type(identifier, name,  service, service_name, intendTime, day, location,  proviso, together, time_input) VALUE ('$identifier', '$name', '$service', '$service_name', $intendTime, $day, $location, '$proviso', '$together', '$time' )";
                    //echo $sql;
                    mysqli_set_charset($conn, 'UTF8');
                    $result = $conn->query($sql);
                    if ($result) {
                        $data = "Success";
                    }
                    $conn->close();
                } else $data = "Không tồn tại mã dịch vụ: ".$service;

            } else {
                $data = "Erro: Thiếu dữ liệu.";
            }
            $this->response(200, $data);
        }
    }

    function add_diary_service(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!(($check_acc["manager"] == "admin"  || $check_acc["manager"] == "clinic"   ) && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if(isset($_GET["vip"]) && isset($_GET["service"]) && isset($_GET["service_name"]) && isset($_GET["schedule"]) && isset($_GET["status"])   && isset($_GET["clinic"])  && isset($_GET["customer_identifier"])  && isset($_GET["intendTime"])  && isset($_GET["location_setting"]) && isset($_GET["proviso"]) && isset($_GET["together"]) ) {
                include('connect.php');
                $vip = $_GET["vip"];
                $service = $_GET["service"];
                $service_name = $_GET["service_name"];
                $schedule =  $_GET["schedule"];
                $status = $_GET["status"];
                $clinic = $_GET["clinic"];
                $customer_identifier = $_GET["customer_identifier"];
                $intendTime = $_GET["intendTime"];
                $location = $_GET["location_setting"];
                $proviso = $_GET["proviso"];
                $together = $_GET["together"];
                $time = date('Y-m-d H:i:s ');
                $sql = "INSERT INTO medical_diary(vip, customer_identifier, service, service_name, schedule, status, intendTime, location,  proviso, together, service_type) VALUE ($vip, '$customer_identifier', '$service', '$service_name', '$schedule', 'Chưa khám', $intendTime, $location, '$proviso', '$together', 'Khám thêm' )";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Thêm dữ liệu dịch vụ khám thành công";
                } else $data = "Erro: Mysql";
                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu";            
            $this->response(200, $data);
        }
    }

    function add_diary_service_vip(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $data = "Erro";
            include('connect.php');
            $vip = $_GET["vip"];
            $service = $_GET["service"];
            $service_name = $_GET["service_name"];
            $schedule =  $_GET["schedule"];
            $status = $_GET["status"];
            $clinic = $_GET["clinic"];
            $customer_identifier = $_GET["customer_identifier"];

            $intendTime = $_GET["intendTime"];
            $location = $_GET["location_setting"];
            $proviso = $_GET["proviso"];
            $together = $_GET["together"];
            $time = date('Y-m-d H:i:s ');

            $sql = "INSERT INTO medical_diary_vip(vip, customer_identifier, service, service_name, schedule, status, intendTime, location,  proviso, together, time_input) VALUE ($vip, '$customer_identifier', '$service', '$service_name', '$schedule', '$status', $intendTime, $location, '$proviso', '$together', '$time' )";
            mysqli_set_charset($conn, 'UTF8');
            $result = $conn->query($sql);

            $conn->close();
            $data = "Success";
            
            $this->response(200, $data);
        }
    }

    function update_diary_service2(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!(($check_acc["manager"] == "admin" || $check_acc["manager"] == "clinic" )&& $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if(isset($_GET["vip"]) && isset($_GET["service"]) && isset($_GET["service_name"]) && isset($_GET["schedule"]) && isset($_GET["status"])   && isset($_GET["clinic"])  && isset($_GET["customer_identifier"])  && isset($_GET["intendTime"])  && isset($_GET["location_setting"]) && isset($_GET["proviso"]) && isset($_GET["together"]) ) {
                include('connect.php');
                $stt = $_GET["stt"];
                $vip = $_GET["vip"];
                $service = $_GET["service"];
                $service_name = $_GET["service_name"];
                $schedule =  $_GET["schedule"];
                $status = $_GET["status"];
                $clinic = $_GET["clinic"];
                $customer_identifier = $_GET["customer_identifier"];
                $intendTime = $_GET["intendTime"];
                $location = $_GET["location_setting"];
                $proviso = $_GET["proviso"];
                $together = $_GET["together"];
                $time = date('Y-m-d H:i:s ');
                $sql = "UPDATE medical_diary SET vip = $vip, service = '$service', service_name = '$service_name', schedule = '$schedule', status = '$status', intendTime = $intendTime, location = $location,  proviso =  '$proviso', together =  '$together' WHERE stt = $stt";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Cập nhật thành công!";
                } else $data = "Erro: Mysql";

                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu";
            
            $this->response(200, $data);
        }
    }

    function update_diary_service2_vip(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $data = "Erro";
            include('connect.php');
            $stt = $_GET["stt"];
            $vip = $_GET["vip"];
            $service = $_GET["service"];
            $service_name = $_GET["service_name"];
            $schedule =  $_GET["schedule"];
            $status = $_GET["status"];
            $clinic = $_GET["clinic"];
            $customer_identifier = $_GET["customer_identifier"];

            $intendTime = $_GET["intendTime"];
            $location = $_GET["location_setting"];
            $proviso = $_GET["proviso"];
            $together = $_GET["together"];
            $time = date('Y-m-d H:i:s ');
            $sql = "UPDATE medical_diary_vip SET vip = $vip, service = '$service', service_name = '$service_name', schedule = '$schedule', status = '$status', intendTime = $intendTime, location = $location,  proviso =  '$proviso', together =  '$together' WHERE stt = $stt";
            //echo $sql;
            mysqli_set_charset($conn, 'UTF8');
            $result = $conn->query($sql);
            $conn->close();
            $data = "Success";
            
            $this->response(200, $data);
        }
    }

    function add_service_type(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $data = "Erro";
            if(isset($_GET["name"]) && isset($_GET["identifier"]) && isset($_GET["service"]) && isset($_GET["location"]) )
            {
                include('connect.php');
                $name = $_GET["name"];
                $identifier = $_GET["identifier"];
                $service = $_GET["service"];
                $location = $_GET["location"];

                if(isset($_GET["note"]))
                {
                    $note = $_GET["note"];
                } else {
                    $note = "";
                }
                $time = date('Y-m-d H:i:s ');
                $sql = "INSERT INTO service_type(name, identifier, service, location,  note, time_input) VALUE ('$name', '$identifier', '$service', $location, '$note', '$time' )";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $data = "Success";
                $conn->close();
            } 
            $this->response(200, $data);
        }
    }


    function add_medical_diary() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $data = "Erro";
            if( isset($_GET["schedule"])  && isset($_GET["identifier"]) && isset($_GET["service"]) && isset($_GET["location"]) )
            {
                include('connect.php');
                $schedule = $_GET["schedule"];
                $identifier = $_GET["identifier"];
                //$service = getServiceId_identifier($_GET["service"]);
                $service = $_GET["service"];
                $location = $_GET["location"];

                if(isset($_GET["note"]))
                {
                    $note = $_GET["note"];
                } else {
                    $note = "";
                }
                $time = date('Y-m-d H:i:s ');
                $sql = "INSERT INTO medical_diary( schedule, customer_identifier, service, location, status,  note, time_input) VALUE ( '$schedule', '$identifier',  '$service', $location, 'Chưa khám', '$note', '$time' )";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $data = "Success";
                $conn->close();
            } 
            $this->response(200, $data);
        }
    }

    function add_medical_diary_row() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $data = "Erro";
            if( isset($_GET["schedule"])  && isset($_GET["identifier"]) && isset($_GET["service"]) && isset($_GET["location"]) )
            {
                include('connect.php');
                $schedule = $_GET["schedule"];
                $identifier = $_GET["identifier"];
                //$service = getServiceId_identifier($_GET["service"]);
                $service = $_GET["service"];
                $location = $_GET["location"];
                if(isset($_GET["note"]))
                {
                    $note = $_GET["note"];
                } else {
                    $note = "";
                }
                $time = date('Y-m-d H:i:s ');
                $sql = "UPDATE medical_diary SET location = location + 1 WHERE (location >= $location) AND customer_identifier = '$identifier' AND schedule = '$schedule'  ";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $sql = "INSERT INTO medical_diary( schedule, customer_identifier, service, location, status,  note, time_input) VALUE ( '$schedule', '$identifier',  '$service', $location, 'Chưa khám', '$note', '$time' )";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $data = "Success";
                $conn->close();
            } 
            $this->response(200, $data);
        }
    }

    function update_schedule() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if( isset($_GET["date_old"])  && isset($_GET["identifier"]) && isset($_GET["date_new"])  )
            {
                include('connect.php');
                $date_old = $_GET["date_old"];
                $identifier = $_GET["identifier"];
                $date_new = $_GET["date_new"];
                $time = date('Y-m-d H:i:s ');
                
                $sql = "UPDATE medical_diary SET schedule = '$date_new', status = 'Chưa khám', time_input = NULL WHERE schedule = '$date_old' AND customer_identifier = '$identifier' AND status <> 'Đã khám' ";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                If($result) {
                    $data = "Cập nhật thành công";
                } else $data = "Erro: Mysql";
                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu."; 
            $this->response(200, $data);
        } 
    }

    function update_schedule_vip() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $data = "Erro";
            if( isset($_GET["date_old"])  && isset($_GET["identifier"]) && isset($_GET["date_new"])  )
            {
                include('connect.php');
                $date_old = $_GET["date_old"];
                $identifier = $_GET["identifier"];
                $date_new = $_GET["date_new"];
                $time = date('Y-m-d H:i:s ');
                
                $sql = "UPDATE medical_diary_vip SET schedule = '$date_new' WHERE schedule = '$date_old' AND customer_identifier = '$identifier'";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);

                $data = "Success";
                $conn->close();
            } 
            $this->response(200, $data);
        } 
    }

    function update_customer() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if( isset($_GET["stt"]) &&  isset($_GET["name"]) &&  isset($_GET["birthday"]) &&  isset($_GET["sex"]) )
            {
                include('connect.php');
                $stt = $_GET["stt"];
                $name = $_GET["name"];
                $birthday = $_GET["birthday"];
                $sex = $_GET["sex"];
                $time = date('Y-m-d H:i:s ');
                $sql = "UPDATE customer SET name = '$name', birthday = '$birthday', sex = '$sex' WHERE  stt = $stt";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Cập nhật dữ liệu khách hàng thành công.";
                } else $data = "Erro: Mysql";
                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu";
            $this->response(200, $data);
        } 
    }


    function delete_medical_diary_day() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else 
            if( isset($_GET["schedule"]) )
            {
                include('connect.php');
                $schedule = $_GET["schedule"];
                $sql = "DELETE FROM medical_diary WHERE schedule = '$schedule' OR schedule = '0000-00-00'";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);

                $sql = "DELETE FROM medical_diary_vip WHERE schedule = '$schedule'  OR schedule = '0000-00-00'";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Xóa dữ liệu ngày: ".$schedule." Thành công!";
                } else $data = "Erro: Mysql";
                $conn->close();
            } else $data = "Thiếu dữ liệu";
            $this->response(200, $data);
        } 
    }

        function delete_medical_diary_service() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!( ($check_acc["manager"] == "admin" || $check_acc["manager"] == "clinic" )&& $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if( isset($_GET["stt"]) )
            {
                include('connect.php');
                $stt = $_GET["stt"];
                $sql = "DELETE FROM medical_diary WHERE stt = $stt";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Xóa dữ liệu thành công";
                } else $data = "Erro: Mysql";
                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu";
            $this->response(200, $data);
        } 
    }

    function delete_service_type() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if( isset($_GET["id"]) )
            {
                include('connect.php');
                $id = $_GET["id"];
                $sql = "DELETE FROM service_type WHERE 	identifier = '$id'";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $data = "Xóa gói khám thành công.";
                $conn->close();
            } else {
                $data = "Erro: Thiếu dữ liệu";
            }
            $this->response(200, $data);
        } 
    }



    function delete_service_clinic() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if( isset($_GET["stt"]) )
            {
                include('connect.php');
                $stt = $_GET["stt"];
                $sql = "DELETE FROM service WHERE stt = '$stt'";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Xóa dịch vụ thành công";
                } else $data = "Erro: mysql";
                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu";
            $this->response(200, $data);
        } 
    }

    function delete_medical_diary_service_vip() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if( isset($_GET["stt"]) )
            {
                include('connect.php');
                $stt = $_GET["stt"];
                $sql = "DELETE FROM medical_diary_vip WHERE stt = $stt";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Xóa dữ liệu thành công!!!";
                } else $data = "Erro: Mysql";
                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu";
            $this->response(200, $data);
        } 
    }

    // Xóa dịch vụ trong gói khám
    function delete_service_type2() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if( isset($_GET["stt"]) )
            {
                include('connect.php');
                $stt = $_GET["stt"];
                $sql = "DELETE FROM service_type WHERE stt = $stt";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result > 0) {
                    $data = "Xóa dịch vụ gói khám thành công.";
                } else $data = "Erro: mysql";
                $conn->close();
            } else {
                $data = "Erro: Không đủ dữ liệu";
            }
            $this->response(200, $data);
        } 
    }

    function delete_doctor() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if( isset($_GET["stt"]) )
            {
                include('connect.php');
                $stt = $_GET["stt"];
                $sql = "DELETE FROM doctor WHERE stt = $stt";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Xóa dữ liệu thành công!";
                } else $data = "Erro2: mysql";
                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu";
            $this->response(200, $data);
        } 
    }


    // Xóa tài khoản
    function delete_acc() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit" && $check_acc["acc"] == "admin")){
                $data = "Bạn không đủ quyền.";
            } else
            if( isset($_GET["stt"]) )
            {
                include('connect.php');
                $stt = $_GET["stt"];
                $sql = "DELETE FROM acc WHERE stt = $stt AND acc <> 'admin'";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Xóa thành công tài khoản!!!";
                } else {
                    $data = "Erro: mysql";
                }
                $conn->close();
            } else {
                $data = "Erro: Thiếu dữ liệu";
            }
            $this->response(200, $data);
        } 
    }

    function delete_clinic_status() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if( isset($_GET["stt"]) )
            {
                include('connect.php');
                $stt = $_GET["stt"];
                $sql = "DELETE FROM clinic_status WHERE stt = $stt";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Xóa dữ liệu thành công";
                } else $data= "Erro: mysql";
                $data = "Success";
                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu"; 
            $this->response(200, $data);
        } 
    }


    function delete_clinic() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if( isset($_GET["stt"]) )
            {
                include('connect.php');
                $stt = $_GET["stt"];
                $sql = "DELETE FROM clinic WHERE stt = $stt";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Xóa phòng khám thành công." ;
                } else $data = "Erro: mysql";
                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu";
            $this->response(200, $data);
        } 
    }



    function add_customer(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if(isset( $_GET["name"])  && isset($_GET["birthday"]) && isset($_GET["sex"]))
            {
                include('connect.php');
                $name = $_GET["name"];
                $birthday = $_GET["birthday"];
                $sex = $_GET["sex"];
                $phone = "";
                $dk = true;
                if(isset($_GET["phone"])) {
                    $_GET["phone"];
                }
                $identifier = $_GET["identifier"];
                if($identifier) {
                    $identifier = $_GET["identifier"];
                    $sql = "SELECT * FROM `customer` WHERE identifier = '$identifier'";
                    mysqli_set_charset($conn, 'UTF8');
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        $dk = false;
                    }
                } else {
                    $identifier = date('dmy');
                    if ($sex == "nam") {
                        $identifier = $identifier."1";
                    } else {
                        $identifier = $identifier."0";
                    }
                }
                $note = $_GET["note"];
                $time = date('Y-m-d H:i:s ');
                if($dk) {
                    $sql = "INSERT INTO customer( name, identifier , birthday, sex, phone, note, time_input) VALUE ('$name','$identifier' , '$birthday', '$sex', '$phone', '$note', '$time' )";
                    mysqli_set_charset($conn, 'UTF8');
                    $result = $conn->query($sql);    
                    $sql = "SELECT  MAX(stt) FROM  customer";
                    mysqli_set_charset($conn, 'UTF8');
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $stt = $row["MAX(stt)"];
                            $identifier = $identifier.$stt;
                        }
                    }
    
                    $sql = "UPDATE customer SET identifier = '$identifier' WHERE stt = $stt";
                    mysqli_set_charset($conn, 'UTF8');
                    $result = $conn->query($sql);
                    $data = $identifier;
                    $conn->close();
                }
            } else $data = "Erro: Thiếu dữ liệu."; 
            $this->response(200, $data);
        }
    }

    // function import_check_customer() {
    //     if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
    //         if(isset($_GET["service_type"])  )
    //         {
    //             $data = [];
    //             $service_type = $_GET["service_type"];
    //             $time_date =  $_GET["time_date"];
    //             // $time_date = "2022-12-06";
    //             // $service_type = "NIN.JP.FE";
    //             include('connect.php');
    //             // 1. Tìm tất cả dịch vụ trong gói đấy
    //             $sql = "SELECT * FROM service_type WHERE identifier = '$service_type'";
    //             //echo $sql;
    //             mysqli_set_charset($conn, 'UTF8');
    //             $result = $conn->query($sql);
    //             if ($result->num_rows > 0) {
    //                 while($row = $result->fetch_assoc()) {
    //                     $check_service = get_clinic_check($row["service"], $time_date);
    //                     //echo $check_service;
    //                     if($check_service) {
    //                         add_time_clinic($check_service, $time_date, $row["intendTime"]);
    //                     } else {
    //                         $data[] = $row["service"];
    //                     }
    //                 }
    //             }
    //         }
    //         $this->response(200, $data);
    //     }
    // }

    function import_check_customer() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            if(isset($_GET["service_type"])  )
            {
                $data = [];
                $service_type = $_GET["service_type"];
                $time_date =  $_GET["time_date"];
				$day = $_GET["day"];
                // $time_date = "2022-12-06";
                // $service_type = "NIN.JP.FE";
                include('connect.php');
                // 1. Tìm tất cả dịch vụ trong gói đấy
                $sql = "SELECT * FROM service_type WHERE identifier = '$service_type' AND day= $day";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $check_service = get_clinic_check($row["service"], $time_date);
                        //echo $check_service;
                        if($check_service) {
                            add_time_clinic($check_service, $time_date, $row["intendTime"]);
                        } else {
                            $data[] = $row["service"];
                        }
                    }
                }
            }
            $this->response(200, $data);
        }
    }

    function import_customer(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if(isset( $_GET["name"])  && isset($_GET["birthday"]) && isset($_GET["sex"]) && isset( $_GET["identifier"])   )
            {
                include('connect.php');
                $identifier = $_GET["identifier"];
                $name = $_GET["name"];
                $birthday = $_GET["birthday"];
                $sex = $_GET["sex"];
                $time = date('Y-m-d H:i:s ');
                $dk = true;
                $time_go = $_GET["time_go"];
                $group_info = $_GET["group_info"];
            

                $sql = "SELECT * FROM `customer` WHERE identifier = '$identifier'";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    $dk = false;
                }
                if($dk) {
                    $sql = "INSERT INTO customer( name, identifier , birthday, sex, time_input, note) VALUE ('$name','$identifier', '$birthday', '$sex', '$time', '' )";
                    mysqli_set_charset($conn, 'UTF8');
                    $result = $conn->query($sql);    
                    $data = "Success";
                }
                $vip = $_GET["vip"];
                $day1 = $_GET["day1"];
                $day2 = $_GET["day2"];

                if(isset($_GET["service_type"])) {
                    $service_type = $_GET["service_type"];
                    $sql = "SELECT * FROM `service_type` WHERE identifier = '$service_type' AND day = 1 ";
				    mysqli_set_charset($conn, 'UTF8');
	                $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $service = $row["service"];
                            $service_name = $row["service_name"];
                            $schedule = $day1;
                            $status = "Chưa khám";
                            $intendTime = $row["intendTime"];
                            $location = $row["location"];
                            $proviso = $row["proviso"];
                            $together = $row["together"];
                            $sql2 = "INSERT INTO medical_diary(vip, customer_identifier, service, service_name, schedule, status, intendTime, location,  proviso, together, group_info, time_go, service_type) VALUE ($vip, '$identifier', '$service', '$service_name', '$schedule', '$status', $intendTime, $location, '$proviso', '$together', '$group_info', '$time_go', '$service_type' )";
                            mysqli_set_charset($conn, 'UTF8');
                            $result2 = $conn->query($sql2);
                            if($vip < 5) {
                                $sql2 = "INSERT INTO medical_diary_vip(vip, customer_identifier, service, service_name, schedule, status, intendTime, location,  proviso, together, group_info, time_go, service_type) VALUE ($vip, '$identifier', '$service', '$service_name', '$schedule', '$status', $intendTime, $location, '$proviso', '$together', '$group_info', '$time_go', '$service_type' )";
                                mysqli_set_charset($conn, 'UTF8');
                                $result2 = $conn->query($sql2);
                            }
                        }
                    }
 
                    if($day2 != "") {
                        $sql = "SELECT * FROM `service_type` WHERE identifier = '$service_type' AND day = 2 ";
				        mysqli_set_charset($conn, 'UTF8');

                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $service = $row["service"];
                                $service_name = $row["service_name"];
                                $schedule = $day2;
                                $status = "Chưa khám";
                                $intendTime = $row["intendTime"];
                                $location = $row["location"];
                                $proviso = $row["proviso"];
                                $together = $row["together"];
                                $sql2 = "INSERT INTO medical_diary(vip, customer_identifier, service, service_name, schedule, status, intendTime, location,  proviso, together, group_info, time_go, service_type) VALUE ($vip, '$identifier', '$service', '$service_name', '$schedule', '$status', $intendTime, $location, '$proviso', '$together', '$group_info', '$time_go', '$service_type'  )";
                                mysqli_set_charset($conn, 'UTF8');
                                $result2 = $conn->query($sql2);

                                if($vip < 5) {
                                    $sql2 = "INSERT INTO medical_diary_vip(vip, customer_identifier, service, service_name, schedule, status, intendTime, location,  proviso, together, group_info, time_go, service_type) VALUE ($vip, '$identifier', '$service', '$service_name', '$schedule', '$status', $intendTime, $location, '$proviso', '$together', '$group_info', '$time_go', '$service_type'  )";
                                    mysqli_set_charset($conn, 'UTF8');
                                    $result2 = $conn->query($sql2);
                                }

                            }
                        }   
                    }
                    if($result2) {
                        $data = "Success";
                    } else $data = "Erro: Mysql.";
                }

                $conn->close();
            } else $data = "Erro: thiếu dữ liệu";
            $this->response(200, $data);
        }
    }

    function delete_medical_diary_date() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if(isset( $_GET["schedule"]) && isset($_GET["identifier"])) {
                include('connect.php');
                $schedule = $_GET["schedule"]; 
                $customer_identifier = $_GET["identifier"];
                $sql = "DELETE FROM medical_diary WHERE schedule = '$schedule' AND status = 'Chưa khám' AND customer_identifier = '$customer_identifier'";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Xóa dữ liệu ngày khám thành công";
                } else $data = "Erro: Mysql";
                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu";
            $this->response(200, $data);
        }
    }

    function medical_diary_enter_clinic() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            if(isset( $_GET["stt"]) && isset( $_GET["clinic_identifier"])) {
                include('connect.php');
                $stt = $_GET["stt"]; 
                $clinic = $_GET["clinic_identifier"];
                $sql = "UPDATE medical_diary SET status = 'Đang khám' WHERE clinic = '$clinic' AND customer_identifier = (SELECT customer_identifier FROM medical_diary WHERE stt = $stt) ";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $data = "Success";
                $conn->close();
            }

            $data = "Success";
            $this->response(200, $data);
        }
    }

    function setting_clinic() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            if(isset( $_GET["stt"]) && isset( $_GET["clinic_identifier"]) && isset( $_GET["status"])  ) {
                $check_acc =  tokenlogin($_GET["tokenlogin"]);
                $identifier = $_GET["clinic_identifier"];
                if(!($check_acc["manager"] == "clinic" && $check_acc["setting"] == "edit"  && strpos($check_acc["clinic"], $identifier) !== false )){
                    $data = "Bạn không đủ quyền.";
                } else {
                    include('connect.php');
                    $stt = $_GET["stt"]; 
                    $clinic = $_GET["clinic_identifier"];
                    $status = $_GET["status"];
                    $time_input = date("G:i:s");

                    if($status == "Chưa khám") {
                        $sql = "UPDATE medical_diary SET status = 'Chưa khám', clinic = NULL, time_input = '$time_input' WHERE stt = $stt ";
                    //echo $sql;
                    } else {
                        $sql = "UPDATE medical_diary SET status = '$status', clinic = '$clinic' , time_input = '$time_input'  WHERE stt = $stt ";
                    //   echo $sql;
                    }
                    mysqli_set_charset($conn, 'UTF8');
                    $result = $conn->query($sql);
                    if($result) {
                        $data = "Thiết lập lại trạng thái khách khám thành công";
                        update_time_id($clinic);
                    } else $data = "Erro: mysql";
                    $conn->close();
                }
            } else $data = "Erro";

            $this->response(200, $data);
        }
    }



    function medical_diary_enter_clinic2() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            if(isset( $_GET["customer_identifier"]) && isset( $_GET["clinic_identifier"])) {
				$check_acc =  tokenlogin($_GET["tokenlogin"]);
                $identifier = $_GET["clinic_identifier"];
                if(!($check_acc["manager"] == "clinic" && $check_acc["setting"] == "edit"  && strpos($check_acc["clinic"], $identifier) !== false )){
                    $data = "Bạn không đủ quyền.";
                } else {
					include('connect.php');
					$customer_identifier = $_GET["customer_identifier"]; 
					$clinic = $_GET["clinic_identifier"];
					$time_input = date("G:i:s");
					$sql = "UPDATE medical_diary SET status = 'Đang khám', time_input = '$time_input' WHERE clinic = '$clinic' AND customer_identifier = '$customer_identifier' ";
					//echo $sql;
					mysqli_set_charset($conn, 'UTF8');
					$result = $conn->query($sql);
					if($result) {
						$data = "Khách hàng ".$customer_identifier." đã được mời vào phòng.";
                        update_time_id($identifier);
					} else $data = "Erro: mysql";
					$conn->close();
				}
            } else $data = "Erro: Thiếu dữ liệu";
            $this->response(200, $data);
        }
    }

    // code
    function clinic_expected_check() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            if(isset( $_GET["day"]) ) {
                include('connect.php');
                $schedule =  $_GET["day"];
                $sql2 = "UPDATE medical_diary SET clinic_expected = NULL WHERE schedule = '$schedule'";
                $conn->query($sql2);
                $sql = "SELECT DISTINCT customer_identifier FROM medical_diary WHERE  schedule = '$schedule'";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        run_clinic_expected($row["customer_identifier"], $schedule);
                    }
                }

            }

            $data = "Success";
            $this->response(200, $data);
        }
    }



/********************************************* 
+ Tính phòng có thời gian chờ thấp nhất
+ Ưu tiên cùng tầng (ưu tiên bao nhiêu thời gian)
1. Cập nhật đã khám xong
2. Kiểm tra trạng thái xem có đg chờ khám  hay đang khám ở phòng nào không => mời về các phòng đấy đấy
3. Tìm ra các dịch vụ cần khám tiếp theo. (Tìm các dịch vụ chưa khám có thứ tự khám nhỏ nhất )
4. Lấy thông tin các phòng có dịch vụ đấy
5. Tính thời gian chờ của phòng
6. Cập nhật chờ khám


******************************************** */
    function medical_diary_was_clinic() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            if(isset( $_GET["clinic"])  && isset( $_GET["customer_identifier"]) ) {
				$check_acc =  tokenlogin($_GET["tokenlogin"]);
                $identifier = $_GET["clinic"];
                if(!($check_acc["manager"] == "clinic" && $check_acc["setting"] == "edit"  && strpos($check_acc["clinic"], $identifier) !== false )){
                    $data = "Bạn không đủ quyền.";
                } else {

					$minTime = 10000000;
					$maxTime = 0;
					$th = 2;
					$data_name = "";
					include('connect.php');
					$time = date('Y-m-d');

					$customer_identifier = $_GET["customer_identifier"];
					$clinic_identifier = $_GET["clinic"];

					// 0. Kiểm tra xem có dịch vụ nào khám sau không?
					$sql_add  = "";
					$clinic_dk = "";

					$sql = "SELECT proviso FROM `medical_diary` WHERE customer_identifier = '$customer_identifier' AND clinic = '$clinic_identifier' AND status = 'Đang khám' AND schedule = '$time'";
					mysqli_set_charset($conn, 'UTF8');
					//echo "\n".$sql;
					$result = $conn->query($sql);
					if ($result->num_rows > 0) {
						while($row = $result->fetch_assoc()) {
							if($row["proviso"]) {
								if($sql_add != "" ){
									$sql_add =  $sql_add." OR identifier = '".$row["proviso"]."'";
								} else {
									$sql_add =  $sql_add." identifier = '".$row["proviso"]."'";
								}
							}

						}
					}

					//echo "\n".$sql_add."\n";
					// 1. Cập nhật đã khám xong
					$time_input = date("G:i:s");
					$sql = "UPDATE medical_diary SET status = 'Đã khám', time_out = '$time_input' WHERE schedule = '$time' AND  clinic = '$clinic_identifier' AND customer_identifier = '$customer_identifier' AND  status = 'Đang khám'";
					//echo $sql;
					mysqli_set_charset($conn, 'UTF8');
					$result = $conn->query($sql);

					// 2. Kiểm tra trạng thái xem có đg chờ khám  hay đang khám ở phòng nào không => mời về các phòng đấy đấy
					$sql = "SELECT DISTINCT clinic FROM medical_diary WHERE  customer_identifier = '$customer_identifier '  AND schedule = '$time' AND (status = 'Đang khám' OR status = 'Chờ khám')";
					mysqli_set_charset($conn, 'UTF8');
					$result = $conn->query($sql);
					$data = "";
					if (($result->num_rows > 0)) {
						while($row = $result->fetch_assoc()) {
							$data_name = $data_name.$row["clinic"]."  ";
						}
						$noti = "Khách hàng vẫn đang chờ khám tại phòng: "." ".$data_name;
					}
					// Nếu khồng có
					else {

						// 3. Tìm ra các dịch vụ cần khám tiếp theo
						$sql = "SELECT service, proviso FROM `medical_diary` WHERE customer_identifier = '$customer_identifier' AND schedule = '$time' AND status = 'Chưa khám' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time' AND status = 'Chưa khám')";
						//echo $sql."\n";
						mysqli_set_charset($conn, 'UTF8');
						$result = $conn->query($sql);
						if ( !($result->num_rows > 0)) {
							$noti = "Hoàn thành";
						
						} else {
							// Lấy các thông tin dịch vụ khám tiếp theo
							if(!$sql_add) { 
								while($row = $result->fetch_assoc()) {

									if( !$row["proviso"] ) {
										if($sql_add != "" ){
											$sql_add =  $sql_add." OR identifier = '".$row["service"]."'";
										} else {
											$sql_add =  $sql_add." identifier = '".$row["service"]."'";
										}
									} else {
										if(check_service_dk($row["proviso"], $customer_identifier)){
											if($sql_add != "" ){
												$sql_add =  $sql_add." OR identifier = '".$row["service"]."'";
											} else {
												$sql_add =  $sql_add." identifier = '".$row["service"]."'";
											}
										} else {
											if($clinic_dk != "" ){
												$clinic_dk =  $clinic_dk." AND service != '".$row["service"]."'";
											} else {
												$clinic_dk =  $clinic_dk." service != '".$row["service"]."'";
											}
										}
									}
								}
							}

							//echo "\n    ".$sql_add."    \n";
							// 4. Lấy thông tin các phòng có dịch vụ đấy
							$sql = "SELECT DISTINCT clinic, intendTime FROM `service` WHERE  status='Hoạt động' AND ( ".$sql_add." ) AND clinic IN (SELECT identifier FROM clinic WHERE status = 'Hoạt động') ORDER BY intendTime ASC";
							// echo $sql."\n \n";
							mysqli_set_charset($conn, 'UTF8');
							$result = $conn->query($sql);
							if ( !($result->num_rows > 0)) {
								$noti = "Không có phòng hoạt động";
							} else {
								while($row = $result->fetch_assoc()) {
									// 5. Tính thời gian chờ của phòng 
									// Thời gian sẽ khám ở phòng đấy
									$time_are = time_all_clinic( $row["clinic"],  $customer_identifier, $clinic_dk);
									// Thời gian chờ khám ở phòng đấy
									$time_wait = countClinic_waite($row["clinic"],  $customer_identifier);
									// echo "\n". $row["clinic"]."  :  ". $time_wait ;
									// echo "\n". $row["clinic"]."  :  ". $time_are ;

									// Tìm ra phòng cần đến.
										//1, Có các phòng có thời gian chờ = 0   => chọn phòng có thời gian làm lâu nhất
										//2, Tất cả các phòng có thời gian chờ != 0   => chọn phòng có thời gian chờ ngắn nhất

									if($time_wait == 0) {
										$th = 1;
										if(check_clinic_location($clinic_identifier, $row["clinic"])) {
											$time_are = $time_are + 20;
										}

										if($time_are > $maxTime ) {
											$maxTime = $time_are;
											$data = $row["clinic"];
											$minTime = 10000000;
										}
									}

									if($th == 2   &&  $time_wait < $minTime ) {
										$minTime = $time_wait;
										$data = $row["clinic"];

									}
								}
								
								//  6. Cập nhật chờ khám
								//  Cập nhật toàn bộ các dịch vụ trong phòng đấy  ???
								$time_input = date("G:i:s");
								//echo $time;
								//echo $sql;
								// if(!$clinic_dk) {
								// 	$sql = "UPDATE medical_diary SET clinic = '$data', status = 'Chờ khám', time_input = '$time_input' WHERE schedule = '$time'  AND status='Chưa khám' AND customer_identifier = '$customer_identifier' AND service IN (SELECT identifier FROM `service` WHERE clinic = '$data')   AND service IN (SELECT service FROM `medical_diary` WHERE customer_identifier = '$customer_identifier' AND schedule = '$time' AND status = 'Chưa khám' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time' AND status = 'Chưa khám'))";
								// } else {
								// 	$sql = "UPDATE medical_diary SET clinic = '$data', status = 'Chờ khám', time_input = '$time_input' WHERE $clinic_dk AND schedule = '$time'  AND status='Chưa khám' AND customer_identifier = '$customer_identifier' AND service IN (SELECT identifier FROM `service` WHERE clinic = '$data')   AND service IN (SELECT service FROM `medical_diary` WHERE customer_identifier = '$customer_identifier' AND schedule = '$time' AND status = 'Chưa khám' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time' AND status = 'Chưa khám'))";
								// }

                                if(!$clinic_dk) {
                                    $sql = "UPDATE medical_diary SET clinic = '$data', status = 'Chờ khám', time_input = '$time_input' WHERE schedule = '$time'  AND status='Chưa khám' AND customer_identifier = '$customer_identifier' AND service IN (SELECT identifier FROM `service` WHERE clinic = '$data')  AND location = (SELECT * FROM (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time' AND status = 'Chưa khám') AS x)";
                                } else {
                                $sql = "UPDATE medical_diary SET clinic = '$data', status = 'Chờ khám', time_input = '$time_input' WHERE $clinic_dk AND schedule = '$time'  AND status='Chưa khám' AND customer_identifier = '$customer_identifier' AND service IN (SELECT identifier FROM `service` WHERE clinic = '$data')  AND location = (SELECT * FROM (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time' AND status = 'Chưa khám') AS x)";
                                }

								$maxTime = time_all_clinic( $data,  $customer_identifier, $clinic_dk);
								mysqli_set_charset($conn, 'UTF8');
								$result = $conn->query($sql);

								$sql = "SELECT * FROM clinic WHERE identifier = '$data'";
								mysqli_set_charset($conn, 'UTF8');
								$result = $conn->query($sql);
								if (($result->num_rows > 0)) {
									while($row = $result->fetch_assoc()) {
										$data_name = $row["name"];
									}
								}
								$conn->close();
				
								if($minTime == 10000000) {
									$minTime = 0;
								}
								$noti =  "Phòng:  ".$data." - ".$data_name."\nChờ:      ".$minTime." phút"."\nKhám:   ".$maxTime." phút";
                                update_time_id($data);

							}
						}
					}
				
				}
            } else $noti = "Erro: Thiếu dữ liệu";
            
            $this->response(200, $noti);
        }
    }


    function medical_diary_was_clinic_begin() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!(( $check_acc["manager"] == "admin" ||  $check_acc["manager"] == "cskh" ) && $check_acc["setting"] == "edit" )){
                $noti = "Bạn không đủ quyền.";
            } else
            if( isset( $_GET["customer_identifier"]) ) {
                $minTime = 10000000;
                $maxTime = 0;
                $th = 2;
                $data_name = "";
                include('connect.php');
                $time = date('Y-m-d');
                $customer_identifier = $_GET["customer_identifier"];
                // 2. Kiểm tra trạng thái xem có đg chờ khám  hay đang khám ở phòng nào không => mời về các phòng đấy đấy
                $sql = "SELECT DISTINCT clinic FROM medical_diary WHERE  customer_identifier = '$customer_identifier '  AND schedule = '$time' AND (status = 'Đang khám' OR status = 'Chờ khám')";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $data = "";
                $noti = "Erro!";
                if (($result->num_rows > 0)) {
                    while($row = $result->fetch_assoc()) {
                        $data_name = $data_name.$row["clinic"]."  ";
                    }
                    $noti = "Khách hàng vẫn đg khám, chờ khám tại phòng khác: "."  ".$data_name;
                }
                // Nếu khồng có
                else {
                    // 3. Tìm ra các dịch vụ cần khám tiếp theo
                    $sql = "SELECT service, proviso FROM `medical_diary` WHERE customer_identifier = '$customer_identifier' AND schedule = '$time' AND status = 'Chưa khám' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time' AND status = 'Chưa khám')";
                    //echo $sql;
                    mysqli_set_charset($conn, 'UTF8');
                    $result = $conn->query($sql);
                    if ( !($result->num_rows > 0)) {
                        $noti = "Hoàn thành";
                    
                    } else {
                        // Lấy các thông tin dịch vụ khám tiếp theo
                        $sql_add  = "";
                        $clinic_dk = "";
                        while($row = $result->fetch_assoc()) {
                            if( !$row["proviso"] ) {
                                if($sql_add != "" ){
                                    $sql_add =  $sql_add." OR identifier = '".$row["service"]."'";
                                } else {
                                    $sql_add =  $sql_add." identifier = '".$row["service"]."'";
                                }
                            } else {
                                if(check_service_dk($row["proviso"], $customer_identifier)){
                                    if($sql_add != "" ){
                                        $sql_add =  $sql_add." OR identifier = '".$row["service"]."'";
                                    } else {
                                        $sql_add =  $sql_add." identifier = '".$row["service"]."'";
                                    }
                                } else {
                                    if($clinic_dk != "" ){
                                        $clinic_dk =  $clinic_dk." AND service != '".$row["service"]."'";
                                    } else {
                                        $clinic_dk =  $clinic_dk." service != '".$row["service"]."'";
                                    }
                                }
                            }
                        }
                        //echo "\n   ".$sql_add."\n";
                        // 4. Lấy thông tin các phòng có dịch vụ đấy
                        $sql = "SELECT DISTINCT clinic, intendTime FROM `service` WHERE status='Hoạt động' AND ( ".$sql_add." ) AND clinic IN (SELECT identifier FROM clinic WHERE status = 'Hoạt động') ORDER BY intendTime ASC";
                        // echo "\n".$sql."\n";
                        mysqli_set_charset($conn, 'UTF8');
                        $result = $conn->query($sql);
                        if ( !($result->num_rows > 0)) {
                            $noti = "Không có phòng hoạt động";
                        } else {
                            while($row = $result->fetch_assoc()) {
                            // 5. Tính thời gian chờ của phòng 
                                // Thời gian sẽ khám ở phòng đấy
                                $time_are = time_all_clinic( $row["clinic"],  $customer_identifier, $clinic_dk);
                                // Thời gian chờ khám ở phòng đấy
                                $time_wait = countClinic_waite($row["clinic"],  $customer_identifier);
                                // echo "\n". $row["clinic"]."  :  ". $time_are ;
                                // echo "\n". $row["clinic"]."  :  ". $time_wait ;

                                // Tìm ra phòng cần đến.
                                    //1, Có các phòng có thời gian chờ = 0   => chọn phòng có thời gian làm lâu nhất
                                    //2, Tất cả các phòng có thời gian chờ != 0   => chọn phòng có thời gian chờ ngắn nhất

                                    
                                if($time_wait == 0) {
                                    $th = 1;
                                    if($time_are > $maxTime ) {
                                        $maxTime = $time_are;
                                        $data = $row["clinic"];
                                        $minTime = 10000000;
                                    }
                                }

                                if($th == 2   &&  $time_wait < $minTime ) {
                                    $minTime = $time_wait;
                                    $data = $row["clinic"];

                                }
                            }

                            //  6. Cập nhật chờ khám
                            //  Cập nhật toàn bộ các dịch vụ trong phòng đấy  ???
                            $time_input = date("G:i:s");
                            //echo $time;                            
                            // if(!$clinic_dk) {
                            //     $sql = "UPDATE medical_diary SET clinic = '$data', status = 'Chờ khám', time_input = '$time_input' WHERE schedule = '$time'  AND status='Chưa khám' AND customer_identifier = '$customer_identifier' AND service IN (SELECT identifier FROM `service` WHERE clinic = '$data')   AND service IN (SELECT service FROM `medical_diary` WHERE customer_identifier = '$customer_identifier' AND schedule = '$time' AND status = 'Chưa khám' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time' AND status = 'Chưa khám'))";
                            // } else {
                            // $sql = "UPDATE medical_diary SET clinic = '$data', status = 'Chờ khám', time_input = '$time_input' WHERE $clinic_dk AND schedule = '$time'  AND status='Chưa khám' AND customer_identifier = '$customer_identifier' AND service IN (SELECT identifier FROM `service` WHERE clinic = '$data')   AND service IN (SELECT service FROM `medical_diary` WHERE customer_identifier = '$customer_identifier' AND schedule = '$time' AND status = 'Chưa khám' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time' AND status = 'Chưa khám'))";
                            // }

                            if(!$clinic_dk) {
                                $sql = "UPDATE medical_diary SET clinic = '$data', status = 'Chờ khám', time_input = '$time_input' WHERE schedule = '$time'  AND status='Chưa khám' AND customer_identifier = '$customer_identifier' AND service IN (SELECT identifier FROM `service` WHERE clinic = '$data')  AND location = (SELECT * FROM (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time' AND status = 'Chưa khám') AS x)";
                            } else {
                            $sql = "UPDATE medical_diary SET clinic = '$data', status = 'Chờ khám', time_input = '$time_input' WHERE $clinic_dk AND schedule = '$time'  AND status='Chưa khám' AND customer_identifier = '$customer_identifier' AND service IN (SELECT identifier FROM `service` WHERE clinic = '$data')  AND location = (SELECT * FROM (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time' AND status = 'Chưa khám') AS x)";
                            }
                            // echo $sql;
                            
                            $maxTime = time_all_clinic( $data,  $customer_identifier, $clinic_dk);
                            
                            mysqli_set_charset($conn, 'UTF8');
                            $result = $conn->query($sql);

                            $sql = "SELECT * FROM clinic WHERE identifier = '$data'";
                            mysqli_set_charset($conn, 'UTF8');
                            $result = $conn->query($sql);
                            if (($result->num_rows > 0)) {
                                while($row = $result->fetch_assoc()) {
                                    $data_name = $row["name"];
                                }
                            }
                            $conn->close();
            
                            if($minTime == 10000000) {
                                $minTime = 0;
                            }
                            $noti =  "Phòng:  ".$data." - ".$data_name."\nChờ:      ".$minTime." phút"."\nKhám:   ".$maxTime." phút";
                            update_time_id($data);

                        }
                    }
                }
            } else $noti = "Erro: Thiếu dữ liệu";
            $this->response(200, $noti);

        }
    }

    function check_clinic_new() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            if(isset($_GET["clinic_id"])){
               // $data = $_GET["clinic_id"];
                $data = [];
                $clinic_id = $_GET["clinic_id"];
            	include('connect.php');
				$time = date('Y-m-d');
                $sql = "SELECT DISTINCT customer_identifier, time_input, vip FROM `medical_diary` WHERE clinic = '$clinic_id' AND status = 'Chờ khám' AND schedule = '$time'  ORDER BY vip, time_input ASC ";
                // echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        //  
                        $time_wait = countClinic_waite_check($clinic_id,  $row["customer_identifier"] , $row["time_input"]);

                        $row["time_wait"] = $time_wait;
                        //  $data[] = $row;
                        $check = medical_diary_was_clinic_begin_check( $row["customer_identifier"]);

                        if($check) {
                            if($time_wait >= $check["time_wait_new"]) {
                                $data[] = $row + $check + getCustomer_info( $row["customer_identifier"]);
                            } else {
                                $data[] = $row + getCustomer_info( $row["customer_identifier"]);
                            }
                        } 
                        // echo "<br>".json_encode($test);
                    }
                }
            }
        
        $this->response(200, $data);
        }

    }


    function send_clinic_customer() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!(($check_acc["manager"] == "admin" || $check_acc["manager"] == "cskh" )&& $check_acc["setting"] == "edit")){
                $noti = "Bạn không đủ quyền.";
            } else
            if(isset( $_GET["clinic"]) && isset($_GET["customer_identifier"])) {
                $data =  $_GET["clinic"];
                $customer_identifier = $_GET["customer_identifier"];
                $time = date('Y-m-d');
                $time_input = date("G:i:s");
                //echo $time;
                include('connect.php');
                $check = false;
                $sql5 = "SELECT clinic FROM service WHERE status = 'Hoạt động' AND (identifier IN( SELECT service FROM medical_diary WHERE customer_identifier='$customer_identifier' AND schedule='$time')) AND (clinic IN (SELECT identifier FROM `clinic` WHERE status = 'Hoạt động'))";
                // echo $sql5;
                mysqli_set_charset($conn, 'UTF8');
                $result5 = $conn->query($sql5);
                if ($result5->num_rows > 0) {
                    while($row = $result5->fetch_assoc()) {
                        if($data == $row["clinic"]) {
                            $check = true;
                        }
                    }
                }
                if($check) {
                    $sql = "UPDATE medical_diary SET status = 'Chưa khám', clinic = NULL, time_input = NULL WHERE customer_identifier = '$customer_identifier' AND status = 'Chờ khám' AND schedule = '$time'";
                    mysqli_set_charset($conn, 'UTF8');
                    $result = $conn->query($sql);

                    $minTime = countClinic_waite($data,  $customer_identifier);

                    // Erro:
                    //$maxTime = time_all_clinic( $data,  $customer_identifier);

                    $sql = "UPDATE medical_diary SET clinic = '$data', status = 'Chờ khám', time_input = '$time_input' WHERE schedule = '$time'  AND customer_identifier = '$customer_identifier' AND service IN (SELECT identifier FROM `service` WHERE clinic = '$data')   AND service IN (SELECT service FROM `medical_diary` WHERE customer_identifier = '$customer_identifier' AND schedule = '$time' AND location = (SELECT MIN(location) FROM medical_diary WHERE customer_identifier = '$customer_identifier'  AND schedule = '$time'  AND service IN (SELECT identifier FROM service WHERE clinic = '$data')))";
                    //$sql = "UPDATE medical_diary SET clinic = '$data', status = 'Chờ khám', time_input = '$time_input' WHERE schedule = '$time'  AND status='Chưa khám' AND customer_identifier = '$customer_identifier' AND service IN (SELECT identifier FROM `service` WHERE clinic = '$data')";
                    //echo $sql."\n";

                    mysqli_set_charset($conn, 'UTF8');
                    $result = $conn->query($sql);

                    $sql = "SELECT * FROM clinic WHERE identifier = '$data'";
                    mysqli_set_charset($conn, 'UTF8');
                    $result = $conn->query($sql);
                    if (($result->num_rows > 0)) {
                        while($row = $result->fetch_assoc()) {
                            $data_name = $row["name"];
                        }
                    }
                    $conn->close();
                    // $noti =  "Phòng:  ".$data." - ".$data_name."\nChờ:      ".$minTime." phút"."\nKhám:   ".$maxTime." phút";
                    $noti =  "Phòng:  ".$data." - ".$data_name."\nChờ:      ".$minTime." phút";
                    update_time_id($data);
                } else {
                    $noti = "Không có dịch vụ nào ở phòng khám: ".$data;
                }
            } else {
                $noti = "Bạn chưa nhập mã phòng!";
            }
            $this->response(200, $noti);
        }
    }


    function setClinic_intendTime() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            if(isset( $_GET["acc"]) && isset($_GET["intendTime"])) {

                include('connect.php');
                $acc = $_GET["acc"]; 
                $intendTime = $_GET["intendTime"];
                $sql = "UPDATE clinic SET intendTime = $intendTime WHERE acc = '$acc'";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $data = "Success";
                $conn->close();
            }
            $data = "Success";
            $this->response(200, $data);
        }
    }
 
    function setClinic_resultTime() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            if(isset( $_GET["acc"]) && isset($_GET["resultTime"])) {

                include('connect.php');
                $acc = $_GET["acc"]; 
                $resultTime = $_GET["resultTime"];
                $sql = "UPDATE clinic SET resultTime = $resultTime WHERE acc = '$acc'";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $data = "Success";
                $conn->close();
            }
            $data = "Success";
            $this->response(200, $data);
        }
    }

    function update_doctor_clinic() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            if(isset( $_GET["doctor"]) && isset($_GET["clinic_identifier"])) {
                $check_acc =  tokenlogin($_GET["tokenlogin"]);
                $identifier = $_GET["clinic_identifier"];
                if(!($check_acc["manager"] == "clinic" && $check_acc["setting"] == "edit"  && strpos($check_acc["clinic"], $identifier) !== false )){
                    $data = "Bạn không đủ quyền.";
                } else {
                    include('connect.php');
                    $doctor = $_GET["doctor"]; 
                    $sql = "UPDATE clinic SET doctor = '$doctor' WHERE identifier = '$identifier'";
                    mysqli_set_charset($conn, 'UTF8');
                    $result = $conn->query($sql);
                    if($result) {
                        $data = "Cập nhật thông tin bác sĩ thành công";
                    } else $data = "Erro: mysql";
                    $conn->close();
                }
            } else $data = "Erro: Thiếu dữ liệu";
            $this->response(200, $data);
        } 
    }

    function setClinic_status() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            if(isset( $_GET["status"]) && isset($_GET["clinic"])) {
				$check_acc =  tokenlogin($_GET["tokenlogin"]);
                $identifier = $_GET["clinic"];
                if(!($check_acc["manager"] == "clinic" && $check_acc["setting"] == "edit"  && strpos($check_acc["clinic"], $identifier) !== false )){
                    $data = "Bạn không đủ quyền.";
                } else {
                include('connect.php');
					$status = $_GET["status"];
					$clinic = $_GET["clinic"];
					$sql = "UPDATE clinic SET status = '$status'  WHERE identifier = '$clinic'";
					mysqli_set_charset($conn, 'UTF8');
					$result = $conn->query($sql);

					if($status !="Hoạt động") {
						$time = date('Y-m-d');
						$time_input = date("G:i:s");
						$sql = "UPDATE medical_diary SET clinic = NULL, status = 'Chưa khám', time_input = '$time_input' WHERE schedule = '$time' AND clinic = '$clinic' AND status!= 'Đã khám' ";
						//echo $sql;
						mysqli_set_charset($conn, 'UTF8');
						$result = $conn->query($sql);
					}
					if($result) {
						$data = "Phòng khám đã được cập nhật trạng thái: ".$status;
					} else $data = "Erro: mysql";
					$conn->close();
				}
            } else $data = "Erro: Thiếu dữ liệu";
            $this->response(200, $data);
        }
    }

    function setClinic_status2() {
        $check_acc =  tokenlogin($_GET["tokenlogin"]);
        if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
            $data = "Bạn không đủ quyền.";
        } else
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            if(isset( $_GET["status"]) && isset($_GET["clinic"])) {
                include('connect.php');
                $status = $_GET["status"];
                $clinic = $_GET["clinic"];
                $sql = "UPDATE clinic SET status = '$status'  WHERE identifier = '$clinic'";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Cập nhật trạng thái phòng thành công.";
                } else {
                    $data = "Erro: Mysql";
                }
                $conn->close();
            }
            $this->response(200, $data);
        } else $data = "Erro: Thiếu dữ liệu.";
    }

    function set_service_status() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!( ($check_acc["manager"] == "admin" ||  $check_acc["manager"] == "clinic" ) && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if(isset( $_GET["status"]) && isset($_GET["stt"])) {
                include('connect.php');
                $status = $_GET["status"];
                $stt = $_GET["stt"];
                $sql = "UPDATE service SET status = '$status'  WHERE stt = '$stt'";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Success";
                } else $data = "Erro: Mysql.";
                $conn->close();
            }
            $this->response(200, $data);
        }
    }

    function reset_mysql() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            include('connect.php');
            $sql = "UPDATE medical_diary SET clinic = NULL, status = 'Chưa khám', time_input = NULL";
            mysqli_set_charset($conn, 'UTF8');
            $result = $conn->query($sql);
            $data = "Success";
            $conn->close();
            $this->response(200, $data);
        }
    }

    function reset_mysql_customer() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit" && $check_acc["acc"] == "admin")){
                $data = "Bạn không đủ quyền.";
            } else {
                include('connect.php');
                $sql = "TRUNCATE TABLE medical_diary";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
    
                $sql = "TRUNCATE TABLE medical_diary_vip";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Xóa toàn bộ dữ liệu khách hàng thành công!";
                } else $data = "Erro: mysql";
                $conn->close();
            }

            $this->response(200, $data);
        }
    }

    function reset_mysql_clinic_check() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            include('connect.php');
            $sql = "UPDATE  clinic_status SET time_test = 0";
            //echo $sql;
            mysqli_set_charset($conn, 'UTF8');
            $result = $conn->query($sql);
            $data = "Success";
            $conn->close();
            $this->response(200, $data);
        }
    }

    function reset_mysql_customer_vip() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            include('connect.php');
            $sql = "UPDATE medical_diary_vip SET clinic = NULL, status = 'Chưa khám', time_input = NULL, time_out = NULL";
            mysqli_set_charset($conn, 'UTF8');
            $result = $conn->query($sql);
            $data = "Success";
            $conn->close();
            $this->response(200, $data);
        }
    }

    function update_time_id() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            include('connect.php');
            if(isset($_GET["clinic"]) && isset($_GET["clinic"]) ) {
                $clinic = $_GET["clinic"];
                $time_id = $_GET["clinic"];
                $sql = "UPDATE clinic SET time_id = $time_id WHERE $identifier = '$clinic' ";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $data = 1;
                $conn->close();
            }  else $data = 0;

            $this->response(200, $data);
        }
    }

    function delete_clinic_status_day() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit")){
                $data = "Bạn không đủ quyền.";
            } else
            if(isset($_GET["day"])) {
                include('connect.php');
                $day = $_GET["day"];
                $sql = "DELETE FROM clinic_status WHERE day_activate = '$day'";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Xóa dữ liệu ngày thành công";
                } else $data = "Thiếu dữ liệu";
                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu";
            $this->response(200, $data);
        }
    }

    // Xóa Toàn bộ dũ liệu gói khám  dùng test
    function reset_mysql_service_type() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit" && $check_acc["acc"] == "admin")){
                $data = "Bạn không đủ quyền.";
            } else {
                include('connect.php');
                $sql = "TRUNCATE TABLE service_type";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result > 0) {
                    $data = "Xóa tất cả dữ liệu gói khám thành công.";
                } else $data = "Erro: mysql";
                $conn->close();
            }
            $this->response(200, $data);
        }
    }

    function reset_mysql_clinic() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit" && $check_acc["acc"] == "admin")){
                $data = "Bạn không đủ quyền.";
            } else {
                include('connect.php');
                $sql = "TRUNCATE TABLE clinic_status";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Xóa dữ lệu gói khám thành công";
                } else $data = "Erro: mysql";
                $conn->close();
            }
            $this->response(200, $data);
        }
    }

    function run() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            if(isset($_GET["customer_id"]) && isset($_GET["schedule"])) {
                $data = "";
                $customer_id  =  $_GET["customer_id"];
                $schedule = $_GET["schedule"];
                $kq = setupvip($customer_id, $schedule);
                if($kq) {
                    $data = $data.$customer_id." : "."Xếp lịch thành công!!!\n";
                } else {
                    $data = $data.$customer_id." : "."Xếp lịch lỗi ... !!!\n";

                }
            }
            $this->response(200, $data);
        }
    }

    function runAll() {
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            if(isset($_GET["schedule"])) {
            	include('connect.php');
                $data = "";
                $schedule = $_GET["schedule"];

                $sql = "SELECT DISTINCT customer_identifier FROM medical_diary_vip WHERE schedule = '$schedule'";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $customer_id  =  $row["customer_identifier"];
                
                        $kq = setupvip($customer_id, $schedule);
                        if($kq) {
                            $data = $data.$customer_id." : "."Xếp lịch thành công!!!<br>";
                        } else {
                            $data = $data.$customer_id." : "."Xếp lịch lỗi ... !!!<br>";
        
                        }
                    }
                }
                $conn->close();
            }
            $this->response(200, $data);
        }
    }

// thêm acc
    function add_acc(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit" && $check_acc["acc"] == "admin")){
                $data = "Bạn không đủ quyền.";
            } else

            if(isset( $_GET["acc"])  && isset($_GET["pass"]) && isset($_GET["name"]) && isset($_GET["manager"]) && isset($_GET["setting"]))
            {
                include('connect.php');
                $acc = $_GET["acc"];
                $pass = $_GET["pass"];
                $token = md5($acc.$pass);
                $name = $_GET["name"];
                $manager = $_GET["manager"];
                $setting = $_GET["setting"];
                
                if(isset($_GET["clinic"]))
                {
                    $clinic = $_GET["clinic"];
                } else {
                    $clinic = "";
                }

                if(isset($_GET["note"]))
                {
                    $note = $_GET["note"];
                } else {
                    $note = "";
                }
                $time = date('Y-m-d H:i:s ');

                $sql = "SELECT acc FROM acc WHERE acc = '$acc'";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result->num_rows > 0  ) {
                    $data = "Erro: Tài khoản đã tồn tại!!!";
                } else {
                    $sql = "INSERT INTO acc(acc, pass, token, name, manager, setting, clinic, note, time_input) VALUE ('$acc', '$pass', '$token', '$name', '$manager', '$setting', '$clinic', '$note', '$time' )";
                    //echo $sql;
                    mysqli_set_charset($conn, 'UTF8');
                    $result = $conn->query($sql);
                    //echo $result;
                    if ($result > 0) {
                        $data = "Thêm tài khoản thành công";
                    } else {
                        $data = "Erro: mysql";
                    }
                }
                $conn->close();
            } else {
                $data = "Erro: Thiếu dữ liệu";
            }
            $this->response(200, $data);
        }
    }
// update acc
    function update_acc(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
            $check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit" && $check_acc["acc"] == "admin" )){
                $data = "Bạn không đủ quyền.";
            } else
            if(isset($_GET["stt"]) && isset( $_GET["acc"]) && isset($_GET["name"]) && isset($_GET["manager"]) && isset($_GET["setting"]))
            {
                include('connect.php');
                $stt = $_GET["stt"];
                $acc = $_GET["acc"];
                $pass = $_GET["pass"];
                $token = md5($acc.$pass);
                $name = $_GET["name"];
                $manager = $_GET["manager"];
                $setting = $_GET["setting"];
                
                if(isset($_GET["clinic"]))
                {
                    $clinic = $_GET["clinic"];
                } else {
                    $clinic = "";
                }

                if(isset($_GET["note"]))
                {
                    $note = $_GET["note"];
                } else {
                    $note = "";
                }
                $time = date('Y-m-d H:i:s ');
                if($pass) {
                    $sql = "UPDATE acc SET acc='$acc', pass='$pass', token='$token', name='$name', manager='$manager', setting='$setting', clinic='$clinic', note='$note', time_input='$time' WHERE stt = $stt";

                } else {
                $sql = "UPDATE acc SET acc='$acc', name='$name', manager='$manager', setting='$setting', clinic='$clinic', note='$note', time_input='$time' WHERE stt = $stt";

                }
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                //echo $result;
                if ($result > 0) {
                    $data = "Cập nhật tài khoản thành công";
                } else {
                    $data = "Erro: mysql";
                }
                
                $conn->close();
            } else {
                $data = "Erro: Thiếu dữ liệu";
            }
            $this->response(200, $data);
        }
    }


}
$user_api = new api();

?>
