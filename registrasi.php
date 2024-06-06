<?php
require_once 'config.php';

header('Content-Type: application/json');

if(isset($_POST['kd_peg']) && isset($_POST['password'])) {
    $kdPegawai = $_POST['kd_peg'];
    $newPassword = $_POST['password'];

    $cekPeg = "SELECT * FROM pegawai WHERE kd_peg = '$kdPegawai'";
    $result_ceck = $conn->query($cekPeg);
    if ($result_ceck->num_rows > 0) {
        $encryptedPassword = md5($newPassword);
        $sql = "UPDATE pegawai SET password = '$encryptedPassword' WHERE kd_peg = '$kdPegawai'";
        if ($conn->query($sql) === TRUE) {
            $response = array('status' => 'success', 'message' => 'Password berhasil dibuat');
            http_response_code(200);
            echo json_encode($response);
        } else {
            $response = array('status' => 'error', 'message' => 'Gagal membuat password: ' . $conn->error);
            http_response_code(500);
            echo json_encode($response);
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Kode Pegawai Tidak Ditemukan');
        http_response_code(404);
        echo json_encode($response);
    }
} else {
    $response = array('status' => 'error', 'message' => 'Data tidak lengkap');
    http_response_code(400);
    echo json_encode($response);
}

$conn->close();
?>