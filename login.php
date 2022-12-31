<?php
require 'restful_api.php';

class api extends restful_api {

	function __construct(){
		parent::__construct();
	}

	function login_clinic(){
		if ($this->method == 'GET'){
            if(isset($_GET["user"]) && isset($_GET["pass"]) && isset($_GET["clinic_id"]) )
            {
                include('connect.php');
                $user = addslashes($_GET["user"]);
                $pass = addslashes($_GET["pass"]);
                $clinic_id = addslashes($_GET["clinic_id"]);

                $sql = "SELECT  * FROM acc  Where acc = BINARY '$user' AND pass = BINARY '$pass' AND clinic LIKE '%$clinic_id%' ";
                mysqli_set_charset($conn, 'UTF8');
                // echo $sql;
                $result = $conn->query($sql);
        
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $data[]=$row;
                    }
                }
                $conn->close();
                $this->response(200, $data);
            }
		}
	}

    function login_admin(){
		if ($this->method == 'GET'){
            if(isset($_GET["user"]) && isset($_GET["pass"]))
            {
                include('connect.php');
                $user = addslashes($_GET["user"]);
                $pass = addslashes($_GET["pass"]);

                $sql = "SELECT  * FROM acc  Where acc = BINARY '$user' AND pass = BINARY '$pass' AND manager = 'admin'";
                mysqli_set_charset($conn, 'UTF8');
                //echo $sql;
                $result = $conn->query($sql);
        
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $data[]=$row;
                    }
                }
                $conn->close();
                $this->response(200, $data);
            }
		}
	}

    function login_cskh(){
		if ($this->method == 'GET'){
            if(isset($_GET["user"]) && isset($_GET["pass"]))
            {
                include('connect.php');
                $user = addslashes($_GET["user"]);
                $pass = addslashes($_GET["pass"]);
                $sql = "SELECT  * FROM acc  Where acc = BINARY '$user' AND pass = BINARY '$pass' AND manager = 'cskh'";
                // echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
        
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $data[]=$row;
                    }
                }
                $conn->close();
                $this->response(200, $data);
            }
		}
	}

	function pass_new(){
		if ($this->method == 'GET'){
            if(isset($_GET["user"]) && isset($_GET["pass"]) && isset($_GET["pass_new"]))
            {
                include('connect.php');
                $user = addslashes($_GET["user"]);
                $pass = addslashes($_GET["pass"]);
				$pass_new = addslashes($_GET["pass_new"]);
				$token = md5($user.$pass_new);

				$sql = "SELECT acc FROM acc  Where acc = BINARY '$user' AND pass = BINARY '$pass'";
				// echo $sql;
                mysqli_set_charset($conn, 'UTF8');
                $result = $conn->query($sql);
				if($result->num_rows > 0) {
					$sql = "UPDATE acc SET pass = '$pass_new', token = '$token' WHERE acc = (SELECT * FROM ( SELECT  acc FROM acc  Where acc = BINARY '$user' AND pass = BINARY '$pass') AS x)";
					// echo "/n".$sql;
					mysqli_set_charset($conn, 'UTF8');
					$result = $conn->query($sql);
					$data = "Cập nhật mật khẩu mới thành công";
				} else {
					$data = "Thông tin tài khoản hoặc mật khẩu không chính xác";
				}

                $conn->close();
                $this->response(200, $data);
            }
		}
	}

}

$user_api = new api();

?>
