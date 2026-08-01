<?php
include_once ("../session.php");

if ($role=="Admin"){
    header("location:../admin/index.php");
    exit;
} elseif($role=="Parent"){
    header("location:../parent/index.php");
    exit;
}

try {

    $coachID = $_SESSION['userID'];

    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM tbl_spabs_jurulatih WHERE jurulatihID = :cid");

    $stmt->bindParam(':cid', $coachID, PDO::PARAM_STR);

    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $category = $row['kategori'];

    $stmt2 = $conn->prepare("SELECT * FROM tbl_spabs_pengumuman WHERE kategori = :category OR kategori = 'ALL'");
    $stmt2->bindParam(':category', $category, PDO::PARAM_STR);
    $stmt2->execute();
    $result = $stmt2->fetchAll();


    $sql = "
                    SELECT 
    k.kemahiranID,
    k.jenis_kemahiran,
    ROUND(SUM(subquery.avg_progress_status) / COUNT(DISTINCT subquery.pemainID),0) AS overall_avg_progress_status
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

    $progressChartLabels = array_column($progressChartData, 'jenis_kemahiran');
    $progressChartValues = array_column($progressChartData, 'overall_avg_progress_status');
    $jsonProgressChartLabels = json_encode($progressChartLabels);
    $jsonProgressChartValues = json_encode($progressChartValues);

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

    $atdChartLabels = array_column($atdChartData, 'month');
    $atdChartValues = array_column($atdChartData, 'avg_attendance_rate');
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
        .emoji-text {
            display: inline-block;
            margin-right: 5px;
            /* Adjust the margin as needed */
        }

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
        .fc-list-day-text {
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

        <?php
        include_once 'sidebar.php'
            ?>

        <div class="main">

            <header>
                <span class="ms-2">Home</span>
                <span class="user-role">Coach</span>
            </header>

            <div class="container p-5">
                <div class="accordion accordion-flush mb-5" id="accordionFlushExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#flush-collapseOne" aria-expanded="false"
                                aria-controls="flush-collapseOne">
                                <span class="emoji-text">📢</span> Announcement (<?php echo count($result) ?>)
                            </button>
                        </h2>
                        <div id="flush-collapseOne" class="accordion-collapse collapse"
                            aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body">

                                 <div class="form-container ">

                                        <?php
                                        // Check if there are any results
                                        if (count($result) > 0) { ?>


                                                <?php
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
                                                                <p class="card-text"><?php echo $readrow2['penerangan']; ?></p>
                                                                <p class="card-text"><small class="text-muted"> <?php echo $formatted_time; ?> - 
                                                                    <?php echo $formatted_date; ?></small></p>                                
                                                          </div>
                                                        </div>

                                               

                                                <?php }
                                        } else {
                                            ?>

                                                <div class="row mb-2 text-center">
                                                    <div class="col-md-12">
                                                        No Announcement Has Been Posted.
                                                    </div>
                                                </div>

                                                <?php
                                        }

                                        ?>

                                    </div>
                                             
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row mb-5" >
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

                <!-- <div class="card">
                    <div class="card-body">
                        <canvas id="progressChart"></canvas>
                    </div>
                </div> -->


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

    $stmt = $conn->prepare("SELECT aktivitiID, nama_aktiviti, tarikh_aktiviti, masa_mula, masa_tamat, kategori 
                            FROM tbl_spabs_aktiviti WHERE (kategori = :category OR kategori = 'ALL') AND tarikh_aktiviti >= CURDATE()");
    $stmt->bindParam(':category', $category, PDO::PARAM_STR);
    // $stmt->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
    $stmt->execute();
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $eventData = [];
    foreach ($events as $event) {
        $startDateTime = $event['tarikh_aktiviti'] . 'T' . $event['masa_mula']; // Combine date and time for start
        $endDateTime = $event['tarikh_aktiviti'] . 'T' . $event['masa_tamat']; // Combine date and time for end
        $eventData[] = [
            'id' => $event['aktivitiID'],
            'title' => $event['nama_aktiviti'],
            'start' => $startDateTime,
            'end' => $endDateTime,
            'backgroundColor' => 'white ',
            'category' => $event['kategori'] // Add category data
        ];
    }
    $jsonEventData = json_encode($eventData);

    ?>

    <script>
        var eventData = <?php echo $jsonEventData; ?>;
    </script>


    <script>


        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: eventData, // Pass the event data here
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
                            text: 'Skill/Attribute Type',
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
    type: 'bar', // Base type is bar
    data: {
        labels: atdChartLabels,
        datasets: [
            {
                type: 'bar',
                label: '% of Attendance (Bar)',
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
            },
            {
                type: 'line',
                label: '% of Attendance (Line)',
                data: atdChartValues, // Use the same data as bar chart
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 2,
                fill: false,
                tension: 0.1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                
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
                    text: 'Month',
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