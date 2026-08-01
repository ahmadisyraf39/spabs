<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Admin") {
    header("location:../admin/index.php");
    exit;
}

try {

    $parentID = $_SESSION['userID'];
    $result = [];

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all categories associated with the parentID
    $stmt = $conn->prepare("SELECT kategori FROM tbl_spabs_pemain WHERE ibubapaID = :pid");
    $stmt->bindParam(':pid', $parentID, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);



    // Extract categories into an array
    $categories = array_column($rows, 'kategori');

    if (!empty($categories)) {
        // Prepare a placeholder for each category in the IN clause
        $placeholders = implode(',', array_fill(0, count($categories), '?'));

        // Fetch announcements for the given categories
        $stmt2 = $conn->prepare("SELECT * FROM tbl_spabs_pengumuman WHERE kategori IN ($placeholders)
                                OR kategori = 'ALL' ");

        // Bind the category values to the placeholders
        foreach ($categories as $index => $category) {
            $stmt2->bindValue($index + 1, $category, PDO::PARAM_STR);
        }

        $stmt2->execute();
        $result = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }

    $sql = "
                    SELECT 
                        pemainID,
                        nama_pemain,
                        COALESCE(SUM(total_progress_status), 0) AS total_progress_status,
                        COALESCE(CEIL(AVG(avg_progress_status)), 0) AS avg_of_avg_progress_status,
                        COALESCE(MAX(latest_evaluation_date), '-') AS latest_evaluation_date
                    FROM (
                        SELECT 
                            p.pemainID,
                            p.nama_pemain,
                            k.kemahiranID, 
                            k.jenis_kemahiran, 
                            COALESCE(SUM(COALESCE(pr.status_capai, 0)), 0) AS total_progress_status,
                            COALESCE(
                                CEIL(SUM(COALESCE(pr.status_capai, 0)) / (
                                    SELECT COUNT(DISTINCT m.modulID)
                                    FROM tbl_spabs_modul m
                                    WHERE m.kemahiranID = k.kemahiranID
                                )), 
                                0
                            ) AS avg_progress_status,
                            COALESCE(MAX(pr.tarikh_penilaian), '-') AS latest_evaluation_date
                        FROM 
                            tbl_spabs_pemain p
                            LEFT JOIN tbl_spabs_kemahiran k ON p.kategori = k.kategori
                            LEFT JOIN tbl_spabs_penilaian pr ON p.pemainID = pr.pemainID AND k.kemahiranID = pr.kemahiranID
                        WHERE 
                            p.ibubapaID = :ibubapaID
                        GROUP BY 
                            p.pemainID, p.nama_pemain, k.kemahiranID, k.jenis_kemahiran
                    ) AS subquery
                    GROUP BY 
                        pemainID, nama_pemain;
                    ";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':ibubapaID', $parentID, PDO::PARAM_STR);
    $stmt->execute();
    $progressChartData = $stmt->fetchAll();

    $progressChartLabels = array_column($progressChartData, 'nama_pemain');
    $progressChartValues = array_column($progressChartData, 'avg_of_avg_progress_status');
    $jsonProgressChartLabels = json_encode($progressChartLabels);
    $jsonProgressChartValues = json_encode($progressChartValues);

    $sql = "
                     SELECT 
                            p.pemainID,
                            p.nama_pemain,
                            p.kategori,
                            SUM(CASE WHEN a.kategori = p.kategori OR a.kategori = 'ALL' THEN 1 ELSE 0 END) AS total_aktiviti,
                            SUM(CASE WHEN (a.kategori = p.kategori OR a.kategori = 'ALL') AND k.status_kehadiran = 'attend' THEN 1 ELSE 0 END) AS total_attend,
                            SUM(CASE WHEN (a.kategori = p.kategori OR a.kategori = 'ALL') AND k.status_kehadiran = 'absent' THEN 1 ELSE 0 END) AS total_absent,
                            CASE 
                                WHEN SUM(CASE WHEN a.kategori = p.kategori OR a.kategori = 'ALL' THEN 1 ELSE 0 END) > 0 THEN 
                                    ROUND((SUM(CASE WHEN (a.kategori = p.kategori OR a.kategori = 'ALL') AND k.status_kehadiran = 'attend' THEN 1 ELSE 0 END) * 100.0) / 
                                    SUM(CASE WHEN a.kategori = p.kategori OR a.kategori = 'ALL' THEN 1 ELSE 0 END))
                                ELSE 0
                            END AS avg_attend_percentage
                        FROM 
                            tbl_spabs_pemain p
                        JOIN 
                            tbl_spabs_kehadiran k ON p.pemainID = k.pemainID
                        JOIN 
                            tbl_spabs_aktiviti a ON k.aktivitiID = a.aktivitiID
                        WHERE 
                            p.ibubapaID = :ibubapaID
                        GROUP BY 
                            p.pemainID, p.nama_pemain, p.kategori

                    ";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':ibubapaID', $parentID, PDO::PARAM_STR);
    $stmt->execute();
    $atdChartData = $stmt->fetchAll();

    $atdChartLabels = array_column($atdChartData, 'nama_pemain');
    $atdChartValues = array_column($atdChartData, 'avg_attend_percentage');
    $jsonAtdChartLabels = json_encode($atdChartLabels);
    $jsonAtdChartValues = json_encode($atdChartValues);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Home</title>
    <link rel="icon" href="../pictures/icons/logo.png" type="image/png">


    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <link href="../css/main.css" rel="stylesheet">

    <style>
        :root {
            --fc-border-color: black;
            --fc-daygrid-event-dot-width: 0px;
        }

        .emoji-text {
            display: inline-block;
            margin-right: 5px;
            /* Adjust the margin as needed */
        }

        .fc-col-header-cell-cushion {
            color: green;
        }

        .fc-daygrid-day-number,
        .fc-event-time {
            color: green;
        }

        .fc-event-title {
            color: darkgreen;
        }

        .fc-event-container,
        .fc-event {
            cursor: pointer;
        }

        .fc-list-day-side-text,
        .fc-list-day-text,
        .fc-next-button {
            color: darkgreen;
        }

        .fc-daygrid-day-frame {
            color: lightgreen;
        }

        .card,
        .accordion,
        .form-container {
            box-shadow: 10px 10px 5px #888888;
            /* horizontal offset, vertical offset, blur radius, shadow color */
        }

        .accordion-item .form-container,
        .accordion-item .card {
            box-shadow: 0px 0px 0px 0px;
        }

        .card {
            box-shadow: 10px 10px 5px #888888;
            /* Adjust height to be flexible */
            height: 100%;
        }

        .card-body {
            /* Make the card body flexible to accommodate the canvas */
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        canvas {
            /* Make the canvas element take up available space */
            flex-grow: 1;
        }
    </style>

</head>


<body>

    <div class="wrapper">

        <?php include_once 'sidebar.php'; ?>

        <div class="main">

            <header>
                <span class="ms-2">Home</span>
                <span class="user-role">Parent</span>
            </header>

            <div class="container p-5">
                <div class="accordion accordion-flush mb-5" id="accordionFlushExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseOne" aria-expanded="false"
                                aria-controls="flush-collapseOne">
                                <span class="emoji-text">📢</span> Announcement (<?php echo count($result); ?>)
                            </button>
                        </h2>
                        <div id="flush-collapseOne" class="accordion-collapse collapse"
                            aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">

                                <div class="form-container">

                                    <?php
                                    // Check if there are any results
                                    if (count($result) > 0) {
                                        foreach ($result as $readrow2) {

                                            $time = $readrow2['masa'];
                                            // Convert 24-hour format to 12-hour format with AM/PM
                                            $formatted_time = date("h:i A", strtotime($time));

                                            $date = $readrow2['tarikh'];
                                            $formatted_date = date("d/m/Y", strtotime($date));
                                            ?>

                                            <div class="card mb-2">
                                                <div class="card-body">
                                                    <div class="row ">
                                                        <div class="col text-center">
                                                            <h5 class="card-title d-inline" style="font-weight: bold;">
                                                                <?php echo $readrow2['tajuk']; ?>
                                                            </h5>
                                                        </div>
                                                        <div class="col-auto text-end">
                                                            <span><?php echo $readrow2['kategori']; ?></span>
                                                        </div>
                                                    </div>
                                                    <hr>


                                                    <p class="card-text">
                                                        <?php echo htmlspecialchars($readrow2['penerangan']); ?>
                                                    </p>

                                                    <div class="row">
                                                        <div class="col-md-6 d-flex align-items-baseline">
                                                            <p class="card-text"><small class="text-muted">
                                                                    <?php echo $formatted_time; ?> -
                                                                    <?php echo $formatted_date; ?></small></p>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>

                                        <?php }
                                    } else { ?>

                                        <div class="row mb-2 text-center">
                                            <div class="col-md-12">
                                                No Announcement Has Been Posted.
                                            </div>
                                        </div>

                                    <?php } ?>

                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <?php if (empty($rows)) { ?>
                    <div class="row mb-3 justify-content-center align-items-center">
                        <div class="card mb-3" style="width: 98%; border: 2px solid black; padding: 0;">
                            <div class="row g-0">
                                <div class="col-md-12 d-flex flex-column justify-content-center align-items-center">
                                    <div class="card-body">
                                        No Registered Player
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <?php
                    exit;
                } ?>

                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <canvas id="progressChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <canvas id="attendanceChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="card">
                    <div class="card-body">
                        <div id='calendar'></div>
                    </div>
                </div>




                <!-- Modal -->
                <div class="modal fade" id="activityModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="activityModal">Activity Details</h5>
                                <button type="button" class="custom-close-button" data-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Activity details will be loaded here -->
                            </div>
                            <!-- <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div> -->
                        </div>

                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- jQuery, Popper.js, and Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    <script src="https://unpkg.com/@popperjs/core@2"></script>
    <script src="https://unpkg.com/tippy.js@6"></script>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar/index.global.min.js'></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <?php
    // Get the current date in the format MySQL expects (YYYY-MM-DD)
    $currentDate = date('Y-m-d');

    $placeholders = implode(',', array_fill(0, count($categories), '?'));

    $stmt = $conn->prepare("SELECT aktivitiID, nama_aktiviti,tarikh_aktiviti, masa_mula, masa_tamat, kategori 
                            FROM tbl_spabs_aktiviti  WHERE  (kategori IN ($placeholders) or kategori = 'ALL')
                            AND jenis_aktiviti != 'Tournament'");

    // Bind the category values to the placeholders
    foreach ($categories as $index => $category) {
        $stmt->bindValue($index + 1, $category, PDO::PARAM_STR);
    }

    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $eventData = [];
    foreach ($events as $event) {
        $startDateTime = $event['tarikh_aktiviti'] . 'T' . $event['masa_mula']; // Combine date and time for start
        $endDateTime = $event['tarikh_aktiviti'] . 'T' . $event['masa_tamat']; // Combine date and time for end
    
        // Set background color based on the category
        $bgColor = '#D3D3D3'; // Default color
        switch ($event['kategori']) {
            case 'u8':
                $bgColor = 'red';
                break;
            case 'u10':
                $bgColor = 'blue';
                break;
            case 'u12':
                $bgColor = 'green';
                break;
            // Add more cases if needed
        }

        $eventData[] = [
            'id' => $event['aktivitiID'],
            'title' => $event['nama_aktiviti'],
            'start' => $startDateTime,
            'end' => $endDateTime,
            'backgroundColor' => $bgColor,
            'category' => htmlspecialchars($event['kategori']) // Add category data
        ];
    }
    $jsonEventData1 = json_encode($eventData);

    ?>

    <?php
    // Get the current date in the format MySQL expects (YYYY-MM-DD)
    $currentDate = date('Y-m-d');

    $stmt = $conn->prepare("SELECT *
                                FROM tbl_spabs_aktiviti a
                                JOIN tbl_spabs_pemilihan p ON a.aktivitiID = p.aktivitiID
                                JOIN tbl_spabs_pemain m ON p.pemainID = m.pemainID
                                WHERE m.ibubapaID = :ibubapaID
                                AND a.tarikh_aktiviti > CURRENT_DATE
                                AND p.status_pemilihan = 'Selected'
                                ORDER BY a.tarikh_aktiviti ASC, a.masa_mula ASC");

    $stmt->bindParam(':ibubapaID', $parentID, PDO::PARAM_STR);

    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $eventData = [];
    foreach ($events as $event) {
        $startDateTime = $event['tarikh_aktiviti'] . 'T' . $event['masa_mula']; // Combine date and time for start
        $endDateTime = $event['tarikh_aktiviti'] . 'T' . $event['masa_tamat']; // Combine date and time for end
    
        // Set background color based on the category
        $bgColor = '#D3D3D3'; // Default color
        switch ($event['kategori']) {
            case 'u8':
                $bgColor = 'red';
                break;
            case 'u10':
                $bgColor = 'blue';
                break;
            case 'u12':
                $bgColor = 'green';
                break;
            // Add more cases if needed
        }

        $eventData[] = [
            'id' => $event['aktivitiID'],
            'title' => $event['nama_aktiviti'],
            'start' => $startDateTime,
            'end' => $endDateTime,
            'backgroundColor' => $bgColor,
            'category' => htmlspecialchars($event['kategori']) // Add category data
        ];
    }
    $jsonEventData2 = json_encode($eventData);

    ?>




    <script>


        document.addEventListener('DOMContentLoaded', function () {
            const currentDate = new Date().toISOString().split('T')[0]; // Get current date in YYYY-MM-DD format

            // Assuming `eventData1` and `eventData2` are defined and contain the events data
            var eventData1 = <?php echo $jsonEventData1; ?>;
            var eventData2 = <?php echo $jsonEventData2; ?>;

            // Combine the two arrays into a single array
            const combinedEventData = [...eventData1, ...eventData2];

            // Filter events to only include those starting today or later
            const filteredEventData = combinedEventData.filter(event => {
                const eventStartDate = event.start.split('T')[0];
                return eventStartDate >= currentDate;
            });

            const calendarEl = document.getElementById('calendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: filteredEventData, // Pass the combined event data here
                eventTimeFormat: { // Specify the time format for event display
                    hour: 'numeric',
                    minute: '2-digit',
                    meridiem: 'short'
                },
                headerToolbar: {
                    left: 'prev,next today', // Display prev, next, and today buttons on the left
                    center: 'title', // Display the calendar title in the center
                    right: 'dayGridMonth,timeGridWeek,listWeek' // Display month, week, and list buttons on the right
                },
                firstDay: 1, // Set Monday as the first day of the week
                eventClick: function (info) {
                    var modal = $('#activityModal');
                    var modalBody = modal.find('.modal-body');

                    // Clear previous content and show loading spinner
                    modalBody.html('<div class="text-center"><img src="../pictures/icons/loading.gif" alt="Loading..."></div>');

                    // Open activityModal and load activity_details.php with activityID as a parameter
                    modalBody.load('activity_details.php?aid=' + info.event.id, function () {
                        // Callback function after content is loaded
                        modal.modal('show');
                    });
                }
            });
            calendar.render();
        });

    </script>

    <script>
        const progressChartLabels = <?php echo $jsonProgressChartLabels; ?>;
        const progressChartValues = <?php echo $jsonProgressChartValues; ?>;

        const atdChartLabels = <?php echo $jsonAtdChartLabels; ?>;
        const atdChartValues = <?php echo $jsonAtdChartValues; ?>;

        const ctx = document.getElementById('progressChart');
        const atd = document.getElementById('attendanceChart');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: progressChartLabels,
                datasets: [{
                    label: '% of Progress',
                    data: progressChartValues,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(201, 203, 207, 0.2)'
                    ],
                    borderColor: [
                        'rgb(255, 99, 132)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)',
                        'rgb(201, 203, 207)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            font: {
                                family: 'Poppins',
                                weight: 'bold'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Value(%)',
                            font: {
                                family: 'Poppins',
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                family: 'Poppins',
                                weight: 'bold'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Player Name',
                            font: {
                                family: 'Poppins',
                                weight: 'bold'
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false, // Disable legend
                        labels: {
                            font: {
                                family: 'Poppins',
                                weight: 'bold'
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Progress Rate',
                        font: {
                            family: 'Poppins',
                            weight: 'bold'
                        }
                    }
                }
            }
        });

        new Chart(atd, {
            type: 'bar',
            data: {
                labels: atdChartLabels,
                datasets: [{
                    label: '% of Attendance',
                    data: atdChartValues,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(201, 203, 207, 0.2)'
                    ],
                    borderColor: [
                        'rgb(255, 99, 132)',
                        'rgb(255, 159, 64)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(54, 162, 235)',
                        'rgb(153, 102, 255)',
                        'rgb(201, 203, 207)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            font: {
                                family: 'Poppins',
                                weight: 'bold'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Value(%)',
                            font: {
                                family: 'Poppins',
                                weight: 'bold'
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                family: 'Poppins',
                                weight: 'bold'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Player Name',
                            font: {
                                family: 'Poppins',
                                weight: 'bold'
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false, // Disable legend
                        labels: {
                            font: {
                                family: 'Poppins',
                                weight: 'bold'
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Attendance Rate',
                        font: {
                            family: 'Poppins',
                            weight: 'bold'
                        }
                    }
                }
            }
        });
    </script>


</body>

</html>