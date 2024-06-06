<?php
require_once 'config.php';

header('Content-Type: application/json');

$headers = apache_request_headers();
if (isset($headers['token'])) {
    $token = $headers['token'];

    $check_token_query = "SELECT * FROM pegawai WHERE token = ?";
    $stmt_check_token = $conn->prepare($check_token_query);

    if ($stmt_check_token) {
        $stmt_check_token->bind_param("s", $token);
        $stmt_check_token->execute();
        $check_token_result = $stmt_check_token->get_result();

        if ($check_token_result && $check_token_result->num_rows > 0) {
            $blnthn = $_POST['blnthn'] ?? '';
            $pasien = $_POST['pasien'] ?? '';
            if (!empty($blnthn) && !empty($pasien)) {
                $check_query = "SELECT pegawai.nama_lengkap, jasa_rinci.pasien, jasa_rinci.tindakan, jasa_rinci.jumlah, jasa_rinci.klem 
                                FROM pegawai 
                                INNER JOIN jasa_rinci ON jasa_rinci.kd_peg = pegawai.kd_peg 
                                WHERE jasa_rinci.blnthn = ? AND jasa_rinci.pasien = ?";
                $stmt_check_data = $conn->prepare($check_query);
                if ($stmt_check_data) {
                    $stmt_check_data->bind_param("ss", $blnthn, $pasien);
                    $stmt_check_data->execute();
                    $check_result = $stmt_check_data->get_result();
                    if ($check_result && $check_result->num_rows > 0) {
                        $data = array();
                        while ($row = $check_result->fetch_assoc()) {
                            $nama_lengkap = $row['nama_lengkap'];
                            $pasien = $row['pasien'];
                            $tindakan = $row['tindakan'];
                            $jumlah = $row['jumlah'];
                            $klem = $row['klem'];
                        
                            $data_pasien = array(
                                'tindakan' => $tindakan,
                                'jumlah' => $jumlah,
                                'klem' => $klem
                            );
                            if (!isset($data[$nama_lengkap])) {
                                $data[$nama_lengkap] = array(
                                    'nama_lengkap' => $nama_lengkap,
                                    'data_nama_lengkap' => array()
                                );
                            }
                            
                            $data[$nama_lengkap]['data_nama_lengkap'][] = array(
                                'pasien' => $pasien,
                                'data_pasien' => array($data_pasien)
                            );
                        }
                        $response = array(
                            'status' => 'success',
                            'message' => 'Data berhasil ditemukan',
                            'data' => array_values($data)
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
                $response = array('status' => 'error', 'message' => 'Parameter blnthn dan pasien tidak tersedia');
                http_response_code(400);
                echo json_encode($response);
            }
        } else {
            $response = array('status' => 'error', 'message' => 'Token tidak valid');
            http_response_code(401);
            echo json_encode($response);
        }
    } else {
        $response = array('status' => 'error', 'message' => 'Kesalahan dalam menyiapkan pernyataan SQL');
        http_response_code(500);
        echo json_encode($response);
    }
} else {
    $response = array('status' => 'error', 'message' => 'Token tidak tersedia');
    http_response_code(400);
    echo json_encode($response);
}

$conn->close();
?>