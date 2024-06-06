<?php
require_once 'config.php';

header('Content-Type: application/json');

function generateToken() {
    return bin2hex(random_bytes(16));
}

if(isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM pegawai WHERE kd_peg='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if ($row['password'] != '') {
            $hashed_password = md5($password);
            if ($row['password'] === $hashed_password) {
                $token = generateToken();
                $update_sql = "UPDATE pegawai SET token='$token' WHERE kd_peg='$username'";
                if ($conn->query($update_sql) === TRUE) {
                    $response = array(
                        "status" => "success",
                        "message" => "Login Berhasil",
                        "token" => $token,
                        "Data" => array(
                            "kd_peg" => $row['kd_peg'],
                            "nama" => $row['nama_lengkap'],
                        )
                    );
                    echo json_encode($response);
                } else {
                    http_response_code(500);
                    echo json_encode(array("status" => "error", "message" => "Gagal memperbarui token"));
                }
            } else {
                http_response_code(401);
                echo json_encode(array("status" => "error", "message" => "Password Salah"));
            }
        } else {
            http_response_code(403);
            echo json_encode(array("status" => "error", "message" => "Pegawai belum memiliki password silahkan registrasi"));
        }
    } else {
        http_response_code(404);
        echo json_encode(array("status" => "error", "message" => "Kode Pegawai Tidak Ditemukan"));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Nama pengguna atau kata sandi tidak boleh kosong"));
}
$conn->close();
?>