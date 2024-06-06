<?php
require_once 'config.php';

header('Content-Type: application/json');

$headers = apache_request_headers();
if (isset($headers['token'])) {
    $token = $headers['token'];
    $kd_peg = isset($_POST['kd_peg']) ? $_POST['kd_peg'] : '';

    if (!empty($kd_peg)) {
        $kd_peg = $conn->real_escape_string($kd_peg);

        $check_query = "SELECT jasa.id_jasa,
                        LEFT(jasa.blntahun,2) AS bulan,
                        RIGHT(jasa.blntahun,4) AS tahun,
                        jasa.blntahun,
                        jasa.jumlah, 
                        jasa.status 
                        FROM pegawai 
                        INNER JOIN jasa ON jasa.kd_peg = pegawai.kd_peg 
                        WHERE pegawai.token = ? 
                        AND pegawai.kd_peg = ? 
                        ORDER BY id_jasa DESC";

        $stmt = $conn->prepare($check_query);

        if ($stmt) {
            $stmt->bind_param("ss", $token, $kd_peg);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $data = array();
                while ($row = $result->fetch_assoc()) {
                    $row['bulan'] = convertbulan($row['bulan']);
                    $data[] = $row;
                }
                $response = array(
                    'status' => 'success',
                    'message' => 'Data berhasil ditemukan',
                    'data' => $data
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

function convertbulan($angka) {
    $bulan = array(
        "01" => "Januari",
        "02" => "Februari",
        "03" => "Maret",
        "04" => "April",
        "05" => "Mei",
        "06" => "Juni",
        "07" => "Juli",
        "08" => "Agustus",
        "09" => "September",
        "10" => "Oktober",
        "11" => "November",
        "12" => "Desember"
    );

    return isset($bulan[$angka]) ? $bulan[$angka] : $angka;
}

$conn->close();
?>