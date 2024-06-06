<?php
require_once 'config.php';

header('Content-Type: application/json');

if(isset($_POST['kd_peg'])) {

    $kdPegawai = $_POST['kd_peg'];

    $sql = "SELECT * FROM pegawai WHERE kd_peg = '$kdPegawai'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $pegawai = $result->fetch_assoc();
        $nama = $pegawai['nama_lengkap'];

        if (!empty($pegawai['password'])) {
            http_response_code(403);
            $response = array('status' => 'error', 'message' => 'Pegawai sudah memiliki password');
        } else {
            http_response_code(200);
            $response = array('status' => 'success', 'message' => 'Data Berhasil Di Temukan', "Data" => array(
                "kd_peg" => $pegawai['kd_peg'],
                "nama" => $nama,
            ));
        }
    } else {
        http_response_code(404);
        $response = array('status' => 'error', 'message' => 'Kode Tidak Ditemukan');
    }

    echo json_encode($response);
} else {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'Data Harus Di Isi'));
}

$conn->close();
?>