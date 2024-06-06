<?php
require_once 'config.php';

header('Content-Type: application/json');

$headers = apache_request_headers();
if (isset($headers['token'])) {
    $token = $headers['token'];
    $kd_peg = $_POST['kd_peg'] ?? '';
    
    if (!empty($kd_peg)) {
        $check_query = "SELECT jasa.id_jasa, jasa.kd_peg, pegawai.nama_lengkap, jasa.jumlah, jasa.status, jasa.blntahun 
                        FROM pegawai 
                        INNER JOIN jasa ON jasa.kd_peg = pegawai.kd_peg 
                        WHERE pegawai.token = ? AND pegawai.kd_peg = ? 
                        ORDER BY jasa.id_jasa DESC 
                        LIMIT 1";
        $stmt = $conn->prepare($check_query);
        
        if ($stmt) {
            $stmt->bind_param("ss", $token, $kd_peg);
            $stmt->execute();
            $check_result = $stmt->get_result();
            
            if ($check_result && $check_result->num_rows > 0) {
                $data = $check_result->fetch_assoc();
                $response = array(
                    'status' => 'success',
                    'message' => 'Data berhasil ditemukan',
                    'data' => array(
                        'jumlah' => $data['jumlah'],
                        'blnthn' => $data['blntahun']
                    )
                );
                http_response_code(200);
                echo json_encode($response);
            } else {
                $response = array('status' => 'error', 'message' => 'Data Tidak Ditemukan');
                http_response_code(404);
                echo json_encode($response);
            }
        } else {
            $response = array('status' => 'error', 'message' => 'Kesalahan dalam menyiapkan pernyataan SQL');
            http_response_code(500);
            echo json_encode($response);
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Kode pegawai tidak tersedia');
        http_response_code(400);
        echo json_encode($response);
    }
} else {
    $response = array('status' => 'error', 'message' => 'Token tidak tersedia');
    http_response_code(400);
    echo json_encode($response);
}
$conn->close();
?>