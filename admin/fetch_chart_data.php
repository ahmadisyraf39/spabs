<?php
// fetch_chart_data.php

include_once ("../session.php");

try {

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $category = $_POST['category'];
    // $category = 'U10';


    $sql = "
                    SELECT 
    k.kemahiranID,
    k.jenis_kemahiran,
    ROUND(SUM(subquery.avg_progress_status) / COUNT(DISTINCT subquery.pemainID), 0) AS overall_avg_progress_status
FROM (
    SELECT 
        p.pemainID,
        k.kemahiranID, 
        COALESCE(SUM(COALESCE(pr.status_capai, 0)), 0) AS total_progress_status,
        COALESCE(
            CEIL(SUM(COALESCE(pr.status_capai, 0)) / (
                SELECT COUNT(DISTINCT m.modulID)
                FROM tbl_spabs_modul m
                WHERE m.kemahiranID = k.kemahiranID
            )), 
            0
        ) AS avg_progress_status
    FROM 
        tbl_spabs_pemain p
        LEFT JOIN tbl_spabs_kemahiran k ON p.kategori = k.kategori
        LEFT JOIN tbl_spabs_penilaian pr ON p.pemainID = pr.pemainID AND k.kemahiranID = pr.kemahiranID
    WHERE p.kategori = :kategori
    GROUP BY 
        p.pemainID, k.kemahiranID
) AS subquery
LEFT JOIN tbl_spabs_kemahiran k ON subquery.kemahiranID = k.kemahiranID
GROUP BY 
    k.kemahiranID;
                    ";

    // Prepare the statement
    $stmt3 = $conn->prepare($sql);
    $stmt3->bindParam(':kategori', $category, PDO::PARAM_STR);
    $stmt3->execute();
    $progressChartData = $stmt3->fetchAll();




    $sql = "
                  SELECT 
    EXTRACT(YEAR FROM a.tarikh_aktiviti) AS year,
    DATE_FORMAT(a.tarikh_aktiviti, '%b') AS month,
    p.kategori,
    ROUND(AVG(CASE WHEN k.status_kehadiran = 'Attend' THEN 1 ELSE 0 END) * 100, 0) AS avg_attendance_rate
FROM 
    tbl_spabs_kehadiran k
JOIN 
    tbl_spabs_aktiviti a ON k.aktivitiID = a.aktivitiID
JOIN 
    tbl_spabs_pemain p ON k.pemainID = p.pemainID
WHERE 
    EXTRACT(YEAR FROM a.tarikh_aktiviti) = EXTRACT(YEAR FROM CURRENT_DATE)
    AND p.kategori = :kategori
GROUP BY 
    EXTRACT(YEAR FROM a.tarikh_aktiviti), 
    DATE_FORMAT(a.tarikh_aktiviti, '%b'), 
    p.kategori
ORDER BY 
    year, 
    STR_TO_DATE(DATE_FORMAT(a.tarikh_aktiviti, '%b'), '%b'), 
    p.kategori;


                    ";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':kategori', $category, PDO::PARAM_STR);
    $stmt->execute();
    $atdChartData = $stmt->fetchAll();



    // Prepare data to return to JavaScript
    $responseData = [
        'progressChartLabels' => array_column($progressChartData, 'jenis_kemahiran'),
        'progressChartValues' => array_column($progressChartData, 'overall_avg_progress_status'),
        'atdChartLabels' => array_column($atdChartData, 'month'),
        'atdChartValues' => array_column($atdChartData, 'avg_attendance_rate'),
    ];

    echo json_encode($responseData);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}



?>