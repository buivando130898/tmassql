<?php
require 'restful_api.php';
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

function countClinic_waite_check($clinic, $customer_id, $time_input){
	// echo $customer_id."\n";
    $timeClinic_are = strtotime(check_clinic_are_time_info($clinic)) ;
	$time_today = strtotime(date('G:i:s'));
	$kq = ceil(($timeClinic_are - $time_today)/60);
	if($kq < 0) {
		$kq = 0;
	}
	// echo $timeClinic_are."\n";
	// echo $time_today."\n";
	// echo $kq."\n";
	$time = date('Y-m-d');
	include('connect.php');
    $sql = "SELECT SUM(intendTime) AS count_time FROM medical_diary WHERE  ((time_input < '$time_input' AND vip = (SELECT DISTINCT  vip FROM medical_diary WHERE customer_identifier = '$customer_id' AND schedule ='$time'  LIMIT 1) OR  vip < (SELECT DISTINCT  vip FROM medical_diary WHERE customer_identifier = '$customer_id' AND schedule ='$time'  LIMIT 1)) ) AND	customer_identifier <> '$customer_id' AND schedule = '$time' AND clinic = '$clinic' AND status = 'Chờ khám' ";
    //  echo "\n".$sql."\n";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
            //echo "\n Giá trị".$row["count_time"];
            if($row["count_time"] == NULL || $row["count_time"] == "NULL" ) {
                return 0 + $kq;
            }
			return $row["count_time"] + $kq;
		}
	}
	$conn->close();
}

function check_clinic_info( $clinic) {
	include('connect.php');
	$time = date('Y-m-d');
	$sql = "SELECT  COUNT(DISTINCT customer_identifier) AS sl FROM medical_diary  Where clinic  = '$clinic' AND status <> 'Đã khám' AND schedule='$time'";
	mysqli_set_charset($conn, 'UTF8');
	//echo "\n".$sql;
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row["sl"];            
		}
	} else {
		return 1000;
	}
}

function count_customer_inClinic($date_statistics, $clinic) {
	include('connect.php');
	$sql = "SELECT COUNT(*) AS co FROM medical_diary WHERE schedule = '$date_statistics' AND clinic = '$clinic'";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row["co"];
		}
	}
	$conn->close();
}

function count_customer_inClinic_expected($date_statistics, $clinic) {
	include('connect.php');
	$sql = "SELECT COUNT(DISTINCT customer_identifier) AS co FROM medical_diary WHERE schedule = '$date_statistics' AND clinic_expected = '$clinic'";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row["co"];
		}
	}
	$conn->close();
}

function count_customer_inService($date_statistics, $service) {
	include('connect.php');
	$sql = "SELECT COUNT(*) AS co FROM medical_diary WHERE schedule = '$date_statistics' AND service = '$service'";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row["co"];
		}
	}
	$conn->close();
}

function check_clinic_are_info( $clinic) {
	include('connect.php');
	$time = date('Y-m-d');
	$sql = "SELECT  COUNT(DISTINCT customer_identifier) AS sl FROM medical_diary  Where clinic  = '$clinic' AND status = 'Đang khám' AND schedule='$time'";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row["sl"];            
		}
	}
}


function check_clinic_had_info( $clinic) {
	include('connect.php');
	$time = date('Y-m-d');
	$sql = "SELECT  COUNT(DISTINCT customer_identifier) AS has FROM medical_diary  Where clinic  = '$clinic' AND status = 'Đã khám' AND schedule='$time'";
	//echo $sql;
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row["has"];            
		}
	}
}



function check_clinic_are_time_info( $clinic) {
	include('connect.php');
	$time = date('Y-m-d');
	$sql = "SELECT  MIN(time_input) AS time_input FROM medical_diary  Where clinic  = '$clinic' AND status = 'Đang khám' AND schedule='$time'";
	mysqli_set_charset($conn, 'UTF8');
	// echo $sql;
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
	//echo $sql;
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$time_sum = $row["time_sum"];    
			$time_begin = strtotime($time_begin) + $time_sum*60;        
		}
	}
	return date("G:i:s", $time_begin) ;
}


function check_token($tokenlogin){
	include('connect.php');
	$sql = "SELECT  * FROM acc  Where token = '$tokenlogin'";
	mysqli_set_charset($conn, 'UTF8');
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return TRUE;            
		}
	}
	else {
		$sql = "SELECT  * FROM clinic  Where token = '$tokenlogin'";
		mysqli_set_charset($conn, 'UTF8');
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			while($row = $result->fetch_assoc()) {
				return TRUE;            
			}
		}
		else {
				return FALSE;
			}
		}
	}


function service_type_service($identifier){
	include('connect.php');
	$sql = "SELECT service FROM  service_type WHERE identifier='$identifier'";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$data[] = $row["service"];
		}
	}
	$conn->close();
	return $data;
}


function countSchedule($schedule){
	include('connect.php');
	$sql = "SELECT COUNT(DISTINCT customer_identifier) AS countCustomer  FROM medical_diary WHERE  schedule = '$schedule'";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$data[] = $row["countCustomer"];
		}
	}
	$conn->close();
	return $data[0];
}

function get_customer_date_clinic3($customer_identifier, $time) {
	include('connect.php');
	$data = [];
	$sql = "SELECT DISTINCT clinic, status FROM medical_diary WHERE schedule='$time' AND customer_identifier = '$customer_identifier' AND (status = 'Đang khám' OR status = 'Chờ khám')";
	//echo $sql;
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$data["status"] = $row["status"];
			$data["clinic"] = $row["clinic"];

		}
	} else {
		$sql = "SELECT DISTINCT clinic, status FROM medical_diary WHERE schedule='$time' AND customer_identifier = '$customer_identifier' AND status = 'Chưa khám'";
		//echo $sql;
		mysqli_set_charset($conn, 'UTF8');
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			$data["status"] = "Chưa khám";
			//$data[] = $data_new; 
		} else {
			$data["status"]= "Đã khám";
			//$data[] = $data_new; 
		}
	}

	$sql = "SELECT DISTINCT service_type FROM medical_diary WHERE schedule = '$time' AND customer_identifier = '$customer_identifier'";
	$result = $conn->query($sql);
	//echo $sql;
	$data2 = [];
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$data2[] = $row["service_type"];
		}
	}

	$data["sevice_type"] = $data2;

	$sql = "SELECT DISTINCT time_go, group_info FROM medical_diary WHERE schedule = '$time' AND customer_identifier = '$customer_identifier'";
	$result = $conn->query($sql);
	//echo $sql;
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$data["time_go"] = $row["time_go"];
			$data["group_info"] = $row["group_info"];
			//echo $row["time_go"];
		}
	}

	echo "\n".$customer_identifier.json_encode($data)."\n";
	$data_arr[] = $data;

	$conn->close();
	return $data_arr[0];
}


function get_customer_date_clinic($customer_identifier, $time) {
	include('connect.php');
	$data = [];
	$sql = "SELECT DISTINCT clinic, status, time_input FROM medical_diary WHERE schedule='$time' AND customer_identifier = '$customer_identifier' AND (status = 'Đang khám' OR status = 'Chờ khám')";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			// $data[] = $row;
			$data_new["status"] = $row["status"];
			$data_new["clinic"] = $row["clinic"];
			if($row["status"] == "Chờ khám") {
				$data_new["time_wait"] = countClinic_waite_check($row["clinic"], $customer_identifier, $row["time_input"]);
			} 
			else {
				$timeClinic_are = strtotime(check_clinic_are_time_info($row["clinic"])) ;
				$time_today = strtotime(date('G:i:s'));
				$kq = ceil(($timeClinic_are - $time_today)/60);
				if($kq < 0) {
					$kq = 0;
				}
				$data_new["time_wait"] = $kq;
			}
		}
	} else {
		$sql = "SELECT DISTINCT clinic, status FROM medical_diary WHERE schedule='$time' AND customer_identifier = '$customer_identifier' AND status = 'Chưa khám'";
		//echo $sql;
		mysqli_set_charset($conn, 'UTF8');
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			$data_new["status"] = "Chưa khám";
			//$data[] = $data_new; 
		} else {
			$data_new["status"] = "Đã khám";
			//$data[] = $data_new; 
		}
	}

	$sql = "SELECT DISTINCT service_type FROM medical_diary WHERE schedule = '$time' AND customer_identifier = '$customer_identifier'";
	$result = $conn->query($sql);
	//echo $sql;
	$data2 = [];
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$data2[] = $row["service_type"];
		}
	}

	$data_new["sevice_type"] = $data2;

	$sql = "SELECT DISTINCT time_go, group_info, vip FROM medical_diary WHERE schedule = '$time' AND customer_identifier = '$customer_identifier'";
	$result = $conn->query($sql);
	//echo $sql;
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$data_new["time_go"] = $row["time_go"];
			$data_new["group_info"] = $row["group_info"];
			$data_new["vip"] = $row["vip"];
			$data[] = $data_new;
			//echo $row["time_go"];
		}
	}

	//echo "\n".$customer_identifier.json_encode($data)."\n";

	$conn->close();
	return $data[0];
}


function getClinic_info_service($service_id){
	include('connect.php');
	$sql = "SELECT name FROM `service` WHERE stt = '$service_id'";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$data[] = $row["name"];
		}
	}
	$conn->close();
	return $data[0];
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

function getCountService($customer_identifier, $time, $clinic_identifier){
	include('connect.php');
	$sql = "SELECT COUNT(*) AS sum FROM medical_diary WHERE customer_identifier  = '$customer_identifier' AND service IN (SELECT identifier FROM service WHERE clinic = '$clinic_identifier') AND schedule = '$time' ORDER BY customer_identifier ASC";
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

function getCountService_are($customer_identifier, $time, $clinic_identifier){
	include('connect.php');
	$sql = "SELECT COUNT(*) AS are FROM medical_diary WHERE status='Đã khám' AND customer_identifier  = '$customer_identifier' AND service IN (SELECT identifier FROM service WHERE clinic = '$clinic_identifier') AND schedule = '$time' ORDER BY customer_identifier ASC";
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

function getClinic_name($identifier){
	include('connect.php');
	$sql = "SELECT * FROM `clinic` WHERE identifier = '$identifier'";
	mysqli_set_charset($conn, 'UTF8');
	//echo $sql;
	$data = "null";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$data = $row["identifier"]." - ".$row["name"];
		}
	}
	$conn->close();
	return $data;
}


class api extends restful_api {

	function __construct(){
		parent::__construct();
	}


	function getClinic_info() {
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			if(isset($_GET["clinic_id"])) {
				include('connect.php');
				$clinic_id = $_GET["clinic_id"];
				$sql = "SELECT * FROM clinic WHERE identifier = '$clinic_id'";
				// echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$data[] = $row ;
					}
				}
				$conn->close();
			}
			$this->response(200, $data);
		}
	}



	function get_service_type(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			$sql = "SELECT DISTINCT name, identifier, note FROM  service_type   ORDER BY name ASC";
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$service["service"] = service_type_service($row["identifier"]);
					$data[] = $row + $service;
				}
			}
			$conn->close();
			$this->response(200, $data);
		}
    }

	function get_timeid_clinic(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			if(isset($_GET["clinic"])) {
				$clinic_id = $_GET["clinic"];
				$sql = "SELECT time_id FROM clinic WHERE identifier = '$clinic_id'";
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$data = $row["time_id"];
					}
				}
				$conn->close();
			} else $data = 0;

			$this->response(200, $data);
		}
    }

	function get_timeid_max(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			$sql = "SELECT MAX(time_id) AS time_id FROM clinic";
			mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data = $row["time_id"];
				}
			}
			$conn->close();
			$this->response(200, $data);
		}
    }

	function get_service_type2(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			$sql = "SELECT DISTINCT identifier, name, note FROM  service_type   ORDER BY name ASC";
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data[] = $row;
				}
			}
			$conn->close();
			$this->response(200, $data);
		}
    }

	function getSchedule_info_table(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			$sql = "SELECT DISTINCT schedule FROM medical_diary";
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$countCustomer["countCustomer"] = countSchedule($row["schedule"]);
					$data[] = $row + $countCustomer;
				}
			}
			$conn->close();
			$this->response(200, $data);
		}
    }

	function get_date_medical_diary(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			if(isset($_GET["identifier"])) {
				$identifier = $_GET["identifier"];
				$sql = "SELECT DISTINCT schedule  FROM  medical_diary  WHERE customer_identifier='$identifier'";
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$data[] = $row;
					}
				}
				$conn->close();
				$this->response(200, $data);
			}
		}
	}

	function get_date_medical_diary_vip(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			if(isset($_GET["identifier"])) {
				$identifier = $_GET["identifier"];
				$sql = "SELECT DISTINCT schedule  FROM  medical_diary_vip  WHERE customer_identifier='$identifier'";
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$data[] = $row;
					}
				}
				$conn->close();
				$this->response(200, $data);
			}
		}
	}


	function clinic_service_info() {
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			if(isset($_GET["identifier"])) {
				$identifier = $_GET["identifier"];
				include('connect.php');
				$sql = "SELECT *  FROM  service  WHERE clinic='$identifier'";
				//echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {

					while($row = $result->fetch_assoc()) {
						$data[] = $row;
					}
				}
				$conn->close();
				$this->response(200, $data);
			}
		}
	}

	function get_data_medical_diary_date(){
		if ($this->method == 'GET' ){
			include('connect.php');
			if(isset($_GET["identifier"])  && $_GET["date"] ) {
				$identifier = $_GET["identifier"];
				$schedule = $_GET["date"];

				$sql = "SELECT *  FROM  medical_diary  WHERE customer_identifier='$identifier' AND schedule ='$schedule'  ORDER BY stt ASC";
				//echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						if($row["clinic"] != "null") {
							$row["clinic"] = getClinic_name($row["clinic"]);
						}
						$data[] = $row;
					}
				}
				$conn->close();
				$this->response(200, $data);
			}
		}
	}

	function get_data_medical_diary_date_clinic_expected(){
		if ($this->method == 'GET' ){
			include('connect.php');
			if(isset($_GET["clinic"])  && $_GET["date"] ) {
				$clinic = $_GET["clinic"];
				$schedule = $_GET["date"];

				$sql = "SELECT *  FROM  medical_diary  WHERE schedule ='$schedule' AND  clinic_expected='$clinic'  ORDER BY customer_identifier ASC";
				// echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$data[] = $row + getCustomer_info($row["customer_identifier"]);
					}
				}
				$conn->close();
				$this->response(200, $data);
			}
		}
	}

	function get_data_medical_diary_date_service_expected(){
		if ($this->method == 'GET' ){
			include('connect.php');
			if(isset($_GET["service"])  && $_GET["date"] ) {
				$service = $_GET["service"];
				$schedule = $_GET["date"];

				$sql = "SELECT *  FROM  medical_diary  WHERE schedule ='$schedule' AND  service='$service'  ORDER BY customer_identifier ASC";
				// echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$data[] = $row + getCustomer_info($row["customer_identifier"]);
					}
				}
				$conn->close();
				$this->response(200, $data);
			}
		}
	}

	function get_data_medical_diary_date_vip(){
		if ($this->method == 'GET' ){
			include('connect.php');
			if(isset($_GET["identifier"])  && $_GET["date"] ) {
				$identifier = $_GET["identifier"];
				$schedule = $_GET["date"];

				$sql = "SELECT *  FROM  medical_diary_vip  WHERE customer_identifier='$identifier' AND schedule ='$schedule'  ORDER BY time_input ASC";
				//echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						if($row["clinic"] != "null") {
							$row["clinic"] = getClinic_name($row["clinic"]);
						}
						$data[] = $row;
					}
				}
				$conn->close();
				$this->response(200, $data);
			}
		}

	}


	function get_data_medical_diary_date2(){
		if ($this->method == 'GET' ){
			include('connect.php');
			if(isset($_GET["identifier"])  && $_GET["date"] ) {
				$identifier = $_GET["identifier"];
				$schedule = $_GET["date"];

				$sql = "SELECT *  FROM  medical_diary  WHERE customer_identifier='$identifier' AND schedule ='$schedule'  ORDER BY status ASC";
				//echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						if($row["clinic"] != "null") {
							$row["clinic"] = getClinic_name($row["clinic"]);
						}
						$data[] = $row;
					}
				}
				$conn->close();
				$this->response(200, $data);
			}
		}

	}

	function get_service_type_identifier(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			if(isset($_GET["identifier"])) {
				$identifier = $_GET["identifier"];
				$sql = "SELECT * FROM  service_type  WHERE identifier='$identifier' ORDER BY location ASC";
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$service["service"] = service_type_service($row["identifier"]);
						$data[] = $row + $service;
					}
				}
				$conn->close();
				$this->response(200, $data);
			}
		}
    }

	function get_service_type_identifier2(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			if(isset($_GET["identifier"])) {
				$identifier = $_GET["identifier"];
				$sql = "SELECT * FROM  service_type  WHERE identifier='$identifier' ORDER BY stt ASC";
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$data[] = $row;
					}
				}
				$conn->close();
				$this->response(200, $data);
			}
		}
    }

	function get_service_type_service(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			if(isset($_GET["identifier"])) {
				$identifier = $_GET["identifier"];
				include('connect.php');
				$sql = "SELECT service FROM  service_type WHERE identifier='$identifier' ";
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$data[] = $row;
					}
				}
				$conn->close(); 
			}
			$this->response(200, $data);
		}
    }

    function get_service(){
		if ($this->method == 'GET'){
			include('connect.php');
			$sql = " SELECT  DISTINCT  identifier, name, group_service, intendTime, location   FROM  service ORDER BY name ASC  ";
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			// $duplicateFilter = "";
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					// if($duplicateFilter !=  ($row["identifier"])) {
						$data[] = $row;
						// $duplicateFilter = $row["identifier"];
					// } 
				}

			}
			$conn->close();
			$this->response(200, $data);
		}
    }

	function get_service2(){
		if ($this->method == 'GET'){
			include('connect.php');
			$sql = "SELECT  * FROM  service ORDER BY name ASC ";
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
						$data[] = $row;
				}

			}
			$conn->close();
			$this->response(200, $data);
		}
    }

    function get_customer(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			$token = tokenlogin($_GET["tokenlogin"]);
			include('connect.php');
			$sql = "SELECT  * FROM  customer";
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data[] = $row;
				}
			}
			$conn->close();
			$this->response(200, $data);
		}
    }

	function get_customer_shearch(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			$token = tokenlogin($_GET["tokenlogin"]);
			include('connect.php');
			$key = $_GET["key"];
			$sql = "SELECT  * FROM  customer WHERE (name = '$key') OR (identifier = '$key')  ";
			//echo $sql;
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data[] = $row;
				}
			} else {
				$data = "erro";
			}
			$conn->close();
			$this->response(200, $data);
		}
    }

	function get_customer_date(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			$time = date('Y-m-d');
			if(isset($_GET["customer_date"])) {
				$time = $_GET["customer_date"];
			}
			$sql = "SELECT * FROM `customer` WHERE identifier IN (SELECT DISTINCT(customer_identifier) FROM `medical_diary` WHERE schedule = '$time')";
			mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data[] = $row + get_customer_date_clinic($row["identifier"], $time) ;
				}
			}
			$conn->close();
			$this->response(200, $data);
		}
    }


	function get_customer_date_vip(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			$time = date('Y-m-d');
			if(isset($_GET["customer_date"])) {
				$time = $_GET["customer_date"];
			}
			$sql = "SELECT * FROM `customer` WHERE identifier IN (SELECT DISTINCT(customer_identifier) FROM `medical_diary_vip` WHERE schedule = '$time' AND vip < 5)";
			mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data[] = $row + get_customer_date_clinic($row["identifier"], $time);
				}
			}
			$conn->close();
			$this->response(200, $data);
		}
    }

    function get_doctor(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			$sql = "SELECT  * FROM  doctor";
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data[] = $row;
				}
			}
			$conn->close();
			$this->response(200, $data);
		}
    }

	function get_acc(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			$check_acc =  tokenlogin($_GET["tokenlogin"]);
            if(!($check_acc["manager"] == "admin" && $check_acc["setting"] == "edit" && $check_acc["acc"] == "admin")){
                $data = "Bạn không đủ quyền.";
            } else {
				include('connect.php');
				$sql = "SELECT  stt, acc, name, manager, setting, clinic, note  FROM  acc WHERE acc <> 'admin'";
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$data[] = $row;
					}
				}
				$conn->close();
			}

			$this->response(200, $data);
		}
    }

	function get_shearch_info(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			if(isset($_GET["key"])) {
				$key =  $_GET["key"];
				include('connect.php');
				$sql = "SELECT  * FROM  doctor";
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						if($key) {
							if(!(strpos($row["name"], $key) === false)) {
								$data[] = $row;
							}
						} else {
							$data[] = $row;
						}
					}
				} 
				$conn->close();
				$this->response(200, $data);
			}
		}
    }

    function get_clinic(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			$sql = "SELECT  * FROM  clinic ORDER BY identifier ASC  ";
			// echo $sql;
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data[] = $row;
				}
			}
			$conn->close();
			$this->response(200, $data);
		}
    }


	function clinic_status_all(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			$sql = "SELECT DISTINCT day_activate FROM clinic_status ORDER BY day_activate DESC  ";
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data[] = $row;
				}
			}
			$conn->close();
			$this->response(200, $data);
		}
    }


	
	function get_clinic_status(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			$day_clinic = $_GET["day_clinic"];
			$sql = "SELECT  * FROM  clinic_status WHERE day_activate = '$day_clinic'  ORDER BY identifier ASC";
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data[] = $row;
				}
			}
			$conn->close();
			$this->response(200, $data);
		}
    }

	function get_clinic_status_all(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			include('connect.php');
			$sql = "SELECT  * FROM  clinic_status  ORDER BY day_activate, identifier ASC";
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data[] = $row;
				}
			}
			$conn->close();
			$this->response(200, $data);
		}
    }

	function get_clinic_statistics(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			if(isset($_GET["date_statistics"])) {
				include('connect.php');
				$data = [];
				$date_statistics = $_GET["date_statistics"];
				$sql = "SELECT DISTINCT clinic FROM medical_diary WHERE schedule = '$date_statistics'";
				//echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						if( $row["clinic"]) {
							$row["sum"] = count_customer_inClinic($date_statistics, $row["clinic"]);
							$row["clinic_name"] = getClinic_name( $row["clinic"]);
							$data[] = $row;
						}

					}
				}
				$conn->close();
				$this->response(200, $data);
			}

		}
    }

	function get_clinic_expected(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			if(isset($_GET["date_statistics"])) {
				include('connect.php');
				$data = [];
				$date_statistics = $_GET["date_statistics"];
				$sql = "SELECT DISTINCT clinic_expected FROM medical_diary WHERE schedule = '$date_statistics'";
				//echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						if( $row["clinic_expected"]) {
							$row["sum"] = count_customer_inClinic_expected($date_statistics, $row["clinic_expected"]);
							$row["clinic_name"] = getClinic_name( $row["clinic_expected"]);
							$data[] = $row;
						}

					}
				}
				$conn->close();
				$this->response(200, $data);
			}

		}
    }

	function get_service_statistics(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			if(isset($_GET["date_statistics"])) {
				include('connect.php');
				$data = [];
				$date_statistics = $_GET["date_statistics"];
				$sql = "SELECT DISTINCT service, service_name FROM medical_diary WHERE schedule = '$date_statistics'";
				//echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						if( $row["service"]) {
							$row["sum"] = count_customer_inService($date_statistics, $row["service"]);
							$data[] = $row;
						}
					}
				}
				$conn->close();
				$this->response(200, $data);
			}
		}
    }

	function get_customer_info(){
		if ($this->method == 'GET'){
			if(isset($_GET["identifier"]))
			{
				$identifier = $_GET["identifier"];
				include('connect.php');
				$sql = "SELECT  * FROM  customer WHERE identifier = '$identifier' ;";
				//echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$data[] = $row;
					}
				}
			} else {
				$data = "Erro";
			}

			$conn->close();
			$this->response(200, $data);
		}
    }

	function get_clinic_customer_info(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			if($_GET["clinic_identifier"] )
			{
                $time = date('Y-m-d');
				$clinic_identifier = $_GET["clinic_identifier"];
				include('connect.php');
				$sql = "SELECT DISTINCT customer_identifier, vip  FROM medical_diary WHERE service IN (SELECT identifier FROM service WHERE clinic = '$clinic_identifier') AND schedule = '$time' ORDER BY vip, customer_identifier ASC";
				//echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$data[] = $row + getCustomer_info($row["customer_identifier"]) + getCountService($row["customer_identifier"], $time, $clinic_identifier) + getCountService_are($row["customer_identifier"], $time, $clinic_identifier);
						#$data[] = $row; 
					}
				}
			} else {
				$data = "Erro";
			}
			$conn->close();
			$this->response(200, $data);
		}
    }

	function get_clinic_customer_info2(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			if($_GET["clinic_identifier"] && $_GET["customer_identifier"])
			{
                $time = date('Y-m-d');
				$clinic_identifier = $_GET["clinic_identifier"];
				$customer_identifier = $_GET["customer_identifier"];
				include('connect.php');
				$sql = "SELECT * FROM medical_diary WHERE customer_identifier  = '$customer_identifier' AND service IN (SELECT identifier FROM service WHERE clinic = '$clinic_identifier') AND schedule = '$time' ORDER BY customer_identifier ASC";
				//echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$data[] = $row + getCustomer_info($row["customer_identifier"]) ;
						#$data[] = $row; 
					}
				}
			} else {
				$data = "Erro";
			}
			$conn->close();
			$this->response(200, $data);
		}
    }


	function get_clinic_customer_info_wait(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			if($_GET["clinic"])
			{
                $time = date('Y-m-d');
				$clinic = $_GET["clinic"];
				include('connect.php');
				$sql = "SELECT  DISTINCT customer_identifier, time_input , vip FROM  medical_diary WHERE  schedule = '$time' AND status = 'Chờ khám' AND clinic = '$clinic'  ORDER BY vip, time_input ASC";
				//echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$data[] = $row + getCustomer_info($row["customer_identifier"]);
					}
				} else {
					$data = null;
				}
			} else {
				$data = "Erro";
			}
			$conn->close();
			$this->response(200, $data);
		}
    }

	function get_clinic_customer_info_are(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			if($_GET["clinic_identifier"] )
			{
                $time = date('Y-m-d');
				//echo $time;
				$clinic_identifier = $_GET["clinic_identifier"];
				include('connect.php');
				$sql = "SELECT DISTINCT customer_identifier, time_input , vip FROM  medical_diary WHERE clinic = '$clinic_identifier' AND schedule = '$time' AND status = 'Đang khám'  ORDER BY vip, time_input ASC";
				//echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$data[] = $row + getCustomer_info($row["customer_identifier"]);
					}
				} else {
					$data = null;
				}
			} else {
				$data = "Erro";
			}
			$conn->close();
			$this->response(200, $data);
		}
    }

	function get_clinic_location(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			$time = date('Y-m-d');
			include('connect.php');
			$sql = "SELECT DISTINCT location FROM clinic ORDER BY location ASC;";
			//echo $sql;
			mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data[] = $row ;
				}
				$conn->close();
				$this->response(200, $data);
			}
    	}
	}


	function get_location_info(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			if(isset($_GET["location"]))
			{
				$location = $_GET["location"]; 
				include('connect.php');
				$sql = "SELECT identifier, name, status, doctor FROM clinic WHERE location = '$location'  ORDER BY location ASC";
				//echo $sql;
				mysqli_set_charset($conn, 'UTF8');
				$result = $conn->query($sql);
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$row["sl"] = check_clinic_info($row["identifier"]);
						$row["clinic_are"] = check_clinic_are_info($row["identifier"]);
						$row["clinic_has"] = check_clinic_had_info($row["identifier"]);

						//check_clinic_had_info
						if($row["clinic_are"] > 0)
						{
							$row["clinic_are_time"] = check_clinic_are_time_info($row["identifier"]);
						}
						else {
							$row["clinic_are_time"] = "Trống";
						}

						if ($row["status"] != "Hoạt động") {
							$row["clinic_are_time"] = "Offline";
						}
						$data[] = $row;
					}
					$conn->close();
					$this->response(200, $data);
				}
			}
    	}
	}

	function get_location_info_all(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){

			include('connect.php');
			$sql = "SELECT identifier, name, status, doctor, location FROM clinic  ORDER BY location ASC";
			//echo $sql;
			mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$row["sl"] = check_clinic_info($row["identifier"]);
					$row["clinic_are"] = check_clinic_are_info($row["identifier"]);
					$row["clinic_has"] = check_clinic_had_info($row["identifier"]);


					//echo "\n".$row["identifier"]." :  ".$row["sl"]."\n";


					if($row["clinic_are"] > 0)
					{
						$row["clinic_are_time"] = check_clinic_are_time_info($row["identifier"]);
					}
					else {
						$row["clinic_are_time"] = "Trống";
					}

					if ($row["status"] != "Hoạt động") {
						$row["clinic_are_time"] = "Offline";
					}
					$data[] = $row;
				}
				$conn->close();
				$this->response(200, $data);
			}
			
    	}
	}

	function test(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) != false){
			
			$data = $_GET["data"];
			$data = explode(',', $data);
			$this->response(200, $data);
		}
    }

}

$user_api = new api();

?>
