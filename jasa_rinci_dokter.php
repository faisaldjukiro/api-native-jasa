<?php
require_once 'config.php';

header('Content-Type: application/json');

$headers = apache_request_headers();
if (isset($headers['token'])) {
    $token = $headers['token'];
    $kd_peg = $_POST['kd_peg'] ?? '';
    $bulan = $_POST['blnthn'] ?? '';

    if (!empty($kd_peg) && !empty($bulan)) {
        $check_query = "SELECT jasa_rinci.id_rinci, pegawai.kd_peg, jasa_rinci.kasus, jasa_rinci.pasien, jasa_rinci.tindakan, jasa_rinci.jumlah, jasa_rinci.blnthn, jasa_rinci.klem 
                        FROM pegawai 
                        INNER JOIN jasa_rinci ON jasa_rinci.kd_peg = pegawai.kd_peg 
                        WHERE pegawai.token = ? 
                        AND pegawai.kd_peg = ? 
                        AND jasa_rinci.blnthn = ?
                        ORDER BY jasa_rinci.id_rinci DESC";

        $stmt = $conn->prepare($check_query);

        if ($stmt) {
            $stmt->bind_param("sss", $token, $kd_peg, $bulan);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $data = array();
                while ($row = $result->fetch_assoc()) {
                    $kasus = $row['kasus'];
                    $pasien = $row['pasien'];
                    if (!isset($data[$kasus][$pasien])) {
                        $data[$kasus][$pasien] = array();
                    }
                    $data_pasien = array(
                        'tindakan' => $row['tindakan'],
                        'jumlah' => $row['jumlah'],
                        'klem' => $row['klem']
                    );
                    $data[$kasus][$pasien][] = $data_pasien;
                }
                $groupedData = array();
                foreach ($data as $kasus => $group) {
                    $groupedKasus = array(
                        'kasus' => $kasus,
                        'data_kasus' => array()
                    );
                    foreach ($group as $pasien => $pasienData) {
                        $groupedKasus['data_kasus'][] = array(
                            'pasien' => $pasien,
                            'data_pasien' => $pasienData
                        );
                    }
                    $groupedData[] = $groupedKasus;
                }

                $response = array(
                    'status' => 'success',
                    'message' => 'Data berhasil ditemukan',
                    'data' => $groupedData
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
        $response = array('status' => 'error', 'message' => 'Kode pegawai atau bulan tidak tersedia');
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