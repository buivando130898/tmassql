<?php
require 'restful_api.php';
date_default_timezone_set('Asia/Ho_Chi_Minh');

function tokenlogin($token) {
	include('connect.php');

	$sql = "SELECT manager FROM acc_ex WHERE token = '$token'";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row["manager"];            
		}
	}
	return false;
}

function double_customer2( $idd, $name, $bir, $address, $phone, $ck, $service_id, $service_name, $service_name_el, $service_group, $retail_price, $list_price, $floor_price, $cost_price, $manager, $time ) {
    include('connect.php');
    $name = $name." Copy";
    $sql = "INSERT INTO history_ex(idd, name, birthday, phone, address, ck, service_id, service_name, service_name_el, service_group, retail_price, list_price, floor_price, cost_price, manager, time_input) VALUE ('$idd', '$name','$bir','$phone','$address', $ck,'$service_id','$service_name','$service_name_el','$service_group', $retail_price, $list_price, $floor_price, $cost_price, '$manager', '$time')";
    //  echo $sql;
    mysqli_set_charset($conn, 'UTF8');
    $result = $conn->query($sql);
    $conn->close();
}

function tokenlogin_check($token) {
	include('connect.php');

	$sql = "SELECT acc FROM acc_ex WHERE token = '$token'";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row["acc"];            
		}
	}
	return false;
}


function check_price($id) {
	include('connect.php');

	$sql = "SELECT * FROM service_ex WHERE identifier = '$id'";
	mysqli_set_charset($conn, 'UTF8');
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			return $row;            
		}
	}
	return false;
}


class api extends restful_api {

	function __construct(){
		parent::__construct();
	}

	function get_history_info(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="admin"){
			include('connect.php');
			$sql = "SELECT DISTINCT idd, manager, name, birthday, address, phone, ck, time_input,  DATE(time_input) as day_add FROM history_ex ORDER BY day_add, name ASC ";
            // echo $sql;
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			// $duplicateFilter = "";
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data[] = $row;
				}
			}
			$conn->close();
			$this->response(200, $data);
		}
    }

    function delete_idd_admin(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="admin"){
            if(isset($_GET["idd"])) {
                include('connect.php');
                $idd = $_GET["idd"];
                $sql = "DELETE FROM history_ex WHERE idd = '$idd'";
                // echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if ($result) {
                    $data = "Xóa khách hàng thành công";
                } else $data = "Erro: Mysql";
                $conn->close();
            }
            $this->response(200, $data);
		}
    }

    function delete_idd_user(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="user"){
            if(isset($_GET["idd"])) {
                include('connect.php');
                $idd = $_GET["idd"];
                $user = tokenlogin_check($_GET["tokenlogin"]);
                $sql = "DELETE FROM history_ex WHERE idd = '$idd' AND manager = '$user'";
                // echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if ($result) {
                    $data = "Xóa khách hàng thành công";
                } else $data = "Erro: Mysql";
                $conn->close();
            }
            $this->response(200, $data);
		}
    }

    function get_history_info_user(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="user"){
            if(isset($_GET["acc"])) {
                include('connect.php');
                $acc = $_GET["acc"] ;
                $sql = "SELECT DISTINCT idd, manager, name, birthday, address, phone, ck, time_input, DATE(time_input) as day_add FROM history_ex WHERE manager = '$acc' ORDER BY day_add, name ASC ";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                // $duplicateFilter = "";
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


    function get_history_info_idd(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="admin"){
			include('connect.php');
            $idd = $_GET["idd"];
			$sql = "SELECT * FROM history_ex WHERE idd = '$idd'  ORDER BY service_group, service_name DESC ";
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			// $duplicateFilter = "";
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data[] = $row;
				}
			}
			$conn->close();
			$this->response(200, $data);
		}
    }

    function get_history_info_idd_user(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="user"){
            if(isset($_GET["idd"]) ) {
                include('connect.php');
                $idd = $_GET["idd"];
                $acc = tokenlogin_check($_GET["tokenlogin"]);
                $sql = "SELECT  stt, service_id, service_name, service_name_el, service_group, retail_price, list_price FROM history_ex WHERE idd = '$idd'  AND manager = '$acc'  ORDER BY service_group, service_name DESC";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                // $duplicateFilter = "";
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


    function get_service(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="admin"){
			include('connect.php');
			$sql = " SELECT * FROM  service_ex  ORDER BY group_service, name DESC  ";
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			// $duplicateFilter = "";
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
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="admin"){
			include('connect.php');
			$sql = " SELECT stt, acc, manager FROM acc_ex  ";
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			// $duplicateFilter = "";
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data[] = $row;
				}

			}
			$conn->close();
			$this->response(200, $data);
		}
    }

	function get_service_user(){
		if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="user"){
			include('connect.php');
			$sql = " SELECT stt, identifier, name, service_name, group_service, retail_price, list_price  FROM  service_ex ORDER BY group_service, name DESC  ";
            mysqli_set_charset($conn, 'UTF8');
			$result = $conn->query($sql);
			// $duplicateFilter = "";
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$data[] = $row;
				}

			}
			$conn->close();
			$this->response(200, $data);
		}
    }

	function add_service_new(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="admin"){
            $data = "Erro";
            if(isset( $_GET["name"]) && isset( $_GET["group_service"])  && isset($_GET["id"]) && isset($_GET["retail_price"])  && isset($_GET["list_price"]) && isset($_GET["floor_price"]))
            {
                include('connect.php');
				$id= $_GET["id"];
				$name= $_GET["name"];
                $service_name = $_GET["service_name"];
				$group_service= $_GET["group_service"];
				$retail_price= $_GET["retail_price"];
				$list_price= $_GET["list_price"];
				$floor_price= $_GET["floor_price"];
				$cost_price= $_GET["cost_price"];
                $time = date('Y-m-d');
                $sql = "INSERT INTO service_ex(identifier, name, service_name, group_service, retail_price, list_price, floor_price, cost_price) VALUE ('$id', '$name', '$service_name', '$group_service' ,$retail_price , $list_price, $floor_price, $cost_price)";
                // echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $conn->close();
                $data = "Success";
            } 
            $this->response(200, $data);
        }
    }

    function add_service_new_check(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="admin"){
            $data = "Erro";
            if(isset( $_GET["name"]) && isset( $_GET["group_service"])  && isset($_GET["id"]) && isset($_GET["retail_price"])  && isset($_GET["list_price"]) && isset($_GET["floor_price"]))
            {
                include('connect.php');
                $idd = $_GET["idd"];
                $customer_name = $_GET["customer_name"];
                $customer_date = $_GET["customer_date"];
                $customer_address = $_GET["customer_address"];
                $customer_phone = $_GET["customer_phone"];
                $customer_ck = $_GET["customer_ck"];

                $manager = $_GET["manager"];

				$service_id= $_GET["id"];
				$service_name= $_GET["name"];
                $service_name_el = $_GET["service_name"];
				$service_group= $_GET["group_service"];
				$retail_price= $_GET["retail_price"];
				$list_price= $_GET["list_price"];
				$floor_price= $_GET["floor_price"];
				$cost_price= $_GET["cost_price"];
                $time = $_GET["time_input"];

                $sql = "INSERT INTO history_ex(idd, name, birthday, address, phone, ck, service_id, service_name, service_name_el, service_group, retail_price, list_price, floor_price, cost_price, manager, time_input) VALUE ('$idd', '$customer_name', '$customer_date', '$customer_address', '$customer_phone', $customer_ck, '$service_id', '$service_name', '$service_name_el', '$service_group', $retail_price, $list_price, $floor_price, $cost_price, '$manager', '$time')";
                // echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Thêm dịch vụ thành công";
                } else $data = "Erro: Mysql";
                $conn->close();
            } 
            $this->response(200, $data);
        }
    }

    function update_customer_check(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="admin"){
            if(isset( $_GET["customer_name"]) && isset( $_GET["customer_date"])  && isset($_GET["customer_address"]) && isset($_GET["customer_phone"])  && isset($_GET["customer_ck"]) && isset($_GET["idd"]))
            {
                include('connect.php');
                $idd = $_GET["idd"];
                $customer_name = $_GET["customer_name"];
                $customer_date = $_GET["customer_date"];
                $customer_address = $_GET["customer_address"];
                $customer_phone = $_GET["customer_phone"];
                $customer_ck = $_GET["customer_ck"];
                $sql = "UPDATE history_ex SET name='$customer_name', birthday='$customer_date', address='$customer_address', phone='$customer_phone', ck='$customer_ck' WHERE idd='$idd' ";
                // echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Thêm dịch vụ thành công";
                } else $data = "Erro: Mysql";
                $conn->close();
            } 
            $this->response(200, $data);
        }
    }


    function update_customer_check_user(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="user"){
            if(isset( $_GET["customer_name"]) && isset( $_GET["customer_date"])  && isset($_GET["customer_address"]) && isset($_GET["customer_phone"])  && isset($_GET["customer_ck"]) && isset($_GET["idd"]))
            {
                include('connect.php');
                $idd = $_GET["idd"];
                $customer_name = $_GET["customer_name"];
                $customer_date = $_GET["customer_date"];
                $customer_address = $_GET["customer_address"];
                $customer_phone = $_GET["customer_phone"];
                $customer_ck = $_GET["customer_ck"];
                $manager = tokenlogin_check($_GET["tokenlogin"]);

                $sql = "UPDATE history_ex SET name='$customer_name', birthday='$customer_date', address='$customer_address', phone='$customer_phone', ck='$customer_ck', manager = '$manager' WHERE idd='$idd'";
                // echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Thêm dịch vụ thành công";
                } else $data = "Erro: Mysql";
                $conn->close();
            } 
            $this->response(200, $data);
        }
    }

    function add_service_new_check_user(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="user"){
            $data = "Erro";
            if(isset( $_GET["name"]) && isset( $_GET["group_service"])  && isset($_GET["id"]) && isset($_GET["retail_price"])  && isset($_GET["list_price"]) )
            {
                include('connect.php');
                $idd = $_GET["idd"];
                $customer_name = $_GET["customer_name"];
                $customer_date = $_GET["customer_date"];
                $customer_address = $_GET["customer_address"];
                $customer_phone = $_GET["customer_phone"];
                $customer_ck = $_GET["customer_ck"];

                $manager = tokenlogin_check($_GET["tokenlogin"]);

				$service_id= $_GET["id"];
				$service_name= $_GET["name"];
                $service_name_el = $_GET["service_name"];
				$service_group= $_GET["group_service"];
				$retail_price= $_GET["retail_price"];
				$list_price= $_GET["list_price"];
                $floor_price = check_price($service_id)["floor_price"];
                $cost_price = check_price($service_id)["cost_price"];
                $time = $_GET["time_input"];
                $sql = "INSERT INTO history_ex(idd, name, birthday, address, phone, ck, service_id, service_name, service_name_el, service_group, retail_price, list_price, floor_price, cost_price, manager, time_input) VALUE ('$idd', '$customer_name', '$customer_date', '$customer_address', '$customer_phone', $customer_ck, '$service_id', '$service_name', '$service_name_el', '$service_group', $retail_price, $list_price, $floor_price, $cost_price, '$manager', '$time')";
                // echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if($result) {
                    $data = "Thêm dịch vụ thành công";
                } else $data = "Erro: Mysql";
                $conn->close();
            } 
            $this->response(200, $data);
        }
    }


	function add_acc_new(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="admin"){
            $data = "Erro";
            if(isset( $_GET["acc"]) && isset( $_GET["pass"])  && isset($_GET["manager"]))
            {
                include('connect.php');
				$acc= $_GET["acc"];
				$pass= $_GET["pass"];
				$manager= $_GET["manager"];

				$token = md5($acc.$pass);

                $time = date('Y-m-d');
                $sql = "INSERT INTO acc_ex(acc, pass, manager, token) VALUE ('$acc', '$pass', '$manager', '$token')";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $conn->close();
                $data = "Success";
            } 
            $this->response(200, $data);
        }
    }

    function save_service_excel(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"])){
            $data = "Erro";
            if(isset( $_GET["name"]) && isset( $_GET["bir"])  && isset($_GET["address"]) && isset($_GET["phone"]) && isset($_GET["ck"]) && isset($_GET["service_id"]) && isset($_GET["service_name"]))
            {
                include('connect.php');
                $name = $_GET["name"];
                $bir =  $_GET["bir"];
                
                $address = $_GET["address"];
                
                $phone = $_GET["phone"];

                $ck = $_GET["ck"];
                $service_id = $_GET["service_id"];
                $service_name = $_GET["service_name"];
                $service_name_el = $_GET["service_name_el"];
                $service_group = $_GET["service_group"];
                $retail_price = $_GET["retail_price"];
                if(isset($_GET["list_price"])){
                    $list_price =  $_GET["list_price"];
                } else $list_price = check_price($service_id)["list_price"];

                if(isset($_GET["floor_price"])){
                    $floor_price = $_GET["floor_price"];
                } else $floor_price = check_price($service_id)["floor_price"];

                if(isset($_GET["cost_price"])){
                    $cost_price = $_GET["cost_price"];
                } else $cost_price = check_price($service_id)["cost_price"];
                
                $time = date('Y-m-d');
                $manager = $_GET["acc"];
                $idd = $_GET["idd"];

                // echo check_price($service_id)["cost_price"];


                $sql = "INSERT INTO history_ex(idd, name, birthday, phone, address, ck, service_id, service_name, service_name_el, service_group, retail_price, list_price, floor_price, cost_price, manager, time_input) VALUE ('$idd', '$name','$bir','$phone','$address', $ck,'$service_id','$service_name','$service_name_el','$service_group', $retail_price, $list_price, $floor_price, $cost_price, '$manager', '$time')";
                //  echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $conn->close();
                $data = "Success";
            } 
            $this->response(200, $data);
        }
    }




    function double_customer() {
        if ($this->method == 'GET'  && (tokenlogin($_GET["tokenlogin"]) =="admin" || tokenlogin($_GET["tokenlogin"]) =="user"  )){
            if(isset($_GET["idd"])  &&  isset($_GET["time_idd"])) {
                include('connect.php');
                $idd = $_GET["idd"];
                $time_idd = $_GET["time_idd"];
                $sql = "SELECT * FROM `history_ex` WHERE idd = '$idd'";
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        double_customer2( $time_idd, $row["name"], $row["birthday"], $row["address"], $row["phone"], $row["ck"], $row["service_id"], $row["service_name"], $row["service_name_el"], $row["service_group"], $row["retail_price"], $row["list_price"], $row["floor_price"], $row["cost_price"], $row["manager"], $row["time_input"]);
                    }
                    $data = "Nhân bản thành công";
                }
                $conn->close();
            } else $data = "Erro: Thiếu dữ liệu";

            $this->response(200, $data);
        }
    }



	function update_service_new(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="admin"){
            $data = "Erro";
            if(isset( $_GET["stt"]) && isset( $_GET["name"]) && isset( $_GET["group_service"])  && isset($_GET["id"]) && isset($_GET["retail_price"])  && isset($_GET["list_price"]) && isset($_GET["floor_price"]))
            {
                include('connect.php');
				$stt = $_GET["stt"];
				$id= $_GET["id"];
				$name= $_GET["name"];
				$service_name= $_GET["service_name"];
				$group_service= $_GET["group_service"];
				$retail_price= $_GET["retail_price"];
				$list_price= $_GET["list_price"];
				$floor_price= $_GET["floor_price"];
                $cost_price = $_GET["cost_price"];
                $time = date('Y-m-d');
                $sql = "UPDATE service_ex SET identifier = '$id', name = '$name',service_name = '$service_name', group_service = '$group_service', retail_price = $retail_price, list_price = $list_price, floor_price = $floor_price, cost_price = '$cost_price' WHERE stt=$stt";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $conn->close();
                $data = "Success";
            } 
            $this->response(200, $data);
        }
    }

    function update_service_new_check(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="admin"){
            $data = "Erro";
            if(isset( $_GET["stt"]) && isset( $_GET["name"]) && isset( $_GET["group_service"])  && isset($_GET["id"]) && isset($_GET["retail_price"])  && isset($_GET["list_price"]) && isset($_GET["floor_price"]))
            {
                include('connect.php');
				$stt = $_GET["stt"];
				$id= $_GET["id"];
				$name= $_GET["name"];
				$service_name= $_GET["service_name"];
				$group_service= $_GET["group_service"];
				$retail_price= $_GET["retail_price"];
				$list_price= $_GET["list_price"];
				$floor_price= $_GET["floor_price"];
                $cost_price = $_GET["cost_price"];
                $time = date('Y-m-d');
                $sql = "UPDATE history_ex SET service_id = '$id', service_name = '$name',service_name_el = '$service_name', service_group = '$group_service', retail_price = $retail_price, list_price = $list_price, floor_price = $floor_price, cost_price = '$cost_price' WHERE stt=$stt";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $conn->close();
                $data = "Success";
            } 
            $this->response(200, $data);
        }
    }

    function update_service_new_check_user(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="user"){
            $data = "Erro";
            if(isset( $_GET["stt"]) && isset( $_GET["name"]) && isset( $_GET["group_service"])  && isset($_GET["id"]) && isset($_GET["retail_price"])  && isset($_GET["list_price"]))
            {
                include('connect.php');
				$stt = $_GET["stt"];
				$id= $_GET["id"];
				$name= $_GET["name"];
				$service_name= $_GET["service_name"];
				$group_service= $_GET["group_service"];
				$retail_price= $_GET["retail_price"];
				$list_price= $_GET["list_price"];
                $user = tokenlogin_check($_GET["tokenlogin"]);
                $time = date('Y-m-d');
                $sql = "UPDATE history_ex SET service_id = '$id', service_name = '$name',service_name_el = '$service_name', service_group = '$group_service', retail_price = $retail_price, list_price = $list_price WHERE stt= $stt AND manager='$user'";
                // echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $conn->close();
                $data = "Success";
            } 
            $this->response(200, $data);
        }
    }

	function update_acc_new(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="admin"){
            $data = "Erro";
            if(isset( $_GET["stt"]) && isset( $_GET["acc"]) && isset( $_GET["pass"]) )
            {
                include('connect.php');
				$stt = $_GET["stt"];
				$acc = $_GET["acc"];
				$pass = $_GET["pass"];
				$token = md5($acc.$pass);

                $time = date('Y-m-d');
                $sql = "UPDATE acc_ex SET acc = '$acc', pass = '$pass', token = '$token' WHERE stt=$stt";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $conn->close();
                $data = "Success";
            } 
            $this->response(200, $data);
        }
    }

	function delete_service(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="admin"){
            $data = "Erro";
            if(isset( $_GET["stt"]))
            {
                include('connect.php');
				$stt= $_GET["stt"];
                $sql = "DELETE FROM service_ex WHERE stt = $stt";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $conn->close();
                $data = "Success";
            } 
            $this->response(200, $data);
        }
    }

    function delete_service_check(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="admin"){
            $data = "Erro";
            if(isset( $_GET["stt"]))
            {
                include('connect.php');
				$stt= $_GET["stt"];
                $sql = "DELETE FROM history_ex WHERE stt = $stt";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $conn->close();
                $data = "Success";
            } 
            $this->response(200, $data);
        }
    }

    function delete_service_check_user(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="user"){
            $data = "Erro";
            if(isset( $_GET["stt"]))
            {
                $user = tokenlogin_check($_GET["tokenlogin"]);
                include('connect.php');
				$stt= $_GET["stt"];
                $sql = "DELETE FROM history_ex WHERE stt = $stt AND manager = '$user'";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $conn->close();
                $data = "Success";
            } 
            $this->response(200, $data);
        }
    }

	function delete_acc(){
        if ($this->method == 'GET'  && tokenlogin($_GET["tokenlogin"]) =="admin"){
            $data = "Erro";
            if(isset( $_GET["stt"]))
            {
                include('connect.php');
				$stt= $_GET["stt"];
                $sql = "DELETE FROM acc_ex WHERE stt = $stt";
                //echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
                $conn->close();
                $data = "Success";
            } 
            $this->response(200, $data);
        }
    }

    



}
$user_api = new api();
?>
