<?php
include_once ("../session.php");

if ($role == "Coach") {
    header("location:../coach/index.php");
    exit;
} elseif ($role == "Parent") {
    header("location:../parent/index.php");
    exit;
}

$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt2 = $conn->prepare("SELECT * FROM tbl_spabs_pengumuman");
$stmt2->execute();
$result = $stmt2->fetchAll();

$sql = "
SELECT user_role, COUNT(*) AS role_count
FROM tbl_spabs_akaun
GROUP BY user_role;
";

// Prepare the statement
$stmt = $conn->prepare($sql);
$stmt->execute();
$userChartData = $stmt->fetchAll();

$userChartLabels = array_column($userChartData, 'user_role');
$userChartValues = array_column($userChartData, 'role_count');
$jsonUserChartLabels = json_encode($userChartLabels);
$jsonUserChartValues = json_encode($userChartValues);

$sql = "
    SELECT kategori, COUNT(*) AS kategori_count
    FROM tbl_spabs_pemain
    GROUP BY kategori;
";

// Prepare the statement
$stmt = $conn->prepare($sql);
$stmt->execute();
$playerChartData = $stmt->fetchAll();

$playerChartLabels = array_column($playerChartData, 'kategori');
$playerChartValues = array_column($playerChartData, 'kategori_count');
$jsonPlayerChartLabels = json_encode($playerChartLabels);
$jsonPlayerChartValues = json_encode($playerChartValues);
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
        .fc-list-day-text {
            color: darkgreen;
        }

        .fc-daygrid-day-frame {
            color: lightgreen;
        }

        h5 {
            font-weight: bold;
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
                <span class="user-role">Admin</span>
            </header>

            <div class="container p-5">
                <div class="accordion accordion-flush mb-5" id="accordionFlushExample">
                    <div class="accordion-item mb-3">
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
                                                    <div class="row">
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

                                                    <div class="row">
                                                        <div class="col-md-6  d-flex align-items-center">
                                                            <p class="card-text"><small class="text-muted">
                                                                    <?php echo $formatted_time; ?> -
                                                                    <?php echo $formatted_date; ?></small></p>
                                                        </div>
                                                        <div class="col-md-6 text-end">
                                                            <button
                                                                data-href="edit_announcement.php?edit=<?php echo $readrow2['pengumumanID']; ?>"
                                                                class="btn btn-outline-primary btn-xs mb-2" role="button"
                                                                data-toggle="modal"
                                                                data-target="#editAnnouncementModal">Edit</button>
                                                            <a href="announcement_crud.php?delete=<?php echo $readrow2['pengumumanID']; ?>"
                                                                onclick="return confirm('Are you sure you want to delete this skill/attribute?');"
                                                                class="btn btn-outline-danger btn-xs mb-2"
                                                                role="button">Delete</a>

                                                        </div>
                                                    </div>



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

                                    <div class="row mt-3">
                                        <div class="col-md-12 text-center">
                                            <button id="createAnnouncement" data-href="create_announcement.php ?>"
                                                class="btn btn-success rounded-circle" data-toggle="modal"
                                                data-target="#createAnnouncementModal"
                                                data-tippy-content="Create Announcement">
                                                <i class="fa-solid fa-plus text-white"></i>
                                            </button>
                                        </div>
                                    </div>




                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                <!-- Modal -->
                <div class="modal fade" id="createAnnouncementModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="createAnnouncementModal">Create Announcement</h5>
                                <button type="button" class="custom-close-button" data-dismiss="modal"
                                    aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">


                            </div>
                            <!-- <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div> -->
                        </div>

                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="editAnnouncementModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editAnnouncementModal">Edit Announcement</h5>
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

                <div class="row mb-5">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <canvas id="userChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <canvas id="playerChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="row mb-3">
                        <div class="col text-center"> <!-- Added text-center class to center align content -->
                            <button class="btn btn-success filter-button" data-category="U8">U8</button>
                            <button class="btn btn-success filter-button" data-category="U10">U10</button>
                            <button class="btn btn-success filter-button" data-category="U12">U12</button>
                        </div>
                    </div>


                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <canvas id="progressChartCanvas"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <canvas id="atdChartCanvas"></canvas>
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

    $stmt = $conn->prepare("SELECT aktivitiID, nama_aktiviti, tarikh_aktiviti, masa_mula, masa_tamat, kategori 
                            FROM tbl_spabs_aktiviti WHERE tarikh_aktiviti >= :currentDate");
    $stmt->bindParam(':currentDate', $currentDate, PDO::PARAM_STR);
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
        // Initialize Tippy.js tooltips
        tippy('#createAnnouncement', {
            placement: 'left'
        });




        $('#createAnnouncementModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var url = button.data('href'); // Extract info from data-* attributes
            var modal = $(this);


            // Clear previous content before making the AJAX request
            modal.find('.modal-body').html('<div class="text-center"><img src="../pictures/icons/loading.gif" alt="Loading..."></div>');


            // Use jQuery to load the content of the URL into the modal body
            $.ajax({
                url: url,
                success: function (data) {
                    modal.find('.modal-body').html(data);
                }
            });
        });

        $('#editAnnouncementModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var url = button.data('href'); // Extract info from data-* attributes
            var modal = $(this);

            // Clear previous content before making the AJAX request
            modal.find('.modal-body').html('<div class="text-center"><img src="../pictures/icons/loading.gif" alt="Loading..."></div>');


            // Use jQuery to load the content of the URL into the modal body
            $.ajax({
                url: url,
                success: function (data) {
                    modal.find('.modal-body').html(data);
                }
            });
        });

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
        const userChartLabels = <?php echo $jsonUserChartLabels; ?>;
        const userChartValues = <?php echo $jsonUserChartValues; ?>;

        const plyrChartLabels = <?php echo $jsonPlayerChartLabels; ?>;
        const plyrChartValues = <?php echo $jsonPlayerChartValues; ?>;

        const user = document.getElementById('userChart');
        const plyr = document.getElementById('playerChart');

        new Chart(user, {
            type: 'bar',
            data: {
                labels: userChartLabels,
                datasets: [{
                    label: 'NUmber of User',
                    data: userChartValues,
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
                        ticks: {
                            font: {
                                family: 'Poppins',
                                weight: 'bold'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Value',
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
                            text: 'User Type',
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
                        text: 'Number of User',
                        font: {
                            family: 'Poppins',
                            weight: 'bold'
                        }
                    }
                }
            }
        });

        new Chart(plyr, {
            type: 'bar',
            data: {
                labels: plyrChartLabels,
                datasets: [{
                    label: 'Number of Player',
                    data: plyrChartValues,
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
                        ticks: {
                            font: {
                                family: 'Poppins',
                                weight: 'bold'
                            },
                            callback: function (value) {
                                return Number.isInteger(value) ? value : '';
                            }
                        },
                        title: {
                            display: true,
                            text: 'Value',
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
                            text: 'Team Category',
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
                        text: 'Number of Player',
                        font: {
                            family: 'Poppins',
                            weight: 'bold'
                        }
                    }
                }
            }
        });
    </script>

    <script>
        // Wait for the document to load
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize chart variables
            let progressChart, atdChart;

            // Function to initialize charts
            const initializeCharts = () => {
                const progressChartCanvas = document.getElementById('progressChartCanvas');
                const atdChartCanvas = document.getElementById('atdChartCanvas');

                progressChart = new Chart(progressChartCanvas, {
                    type: 'bar',
                    data: {
                        labels: [],
                        datasets: [{
                            label: '% of Progress',
                            data: [],
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
                        }, plugins: {
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

                atdChart = new Chart(atdChartCanvas, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [{
                            label: '% of Attendance',
                            data: [],
                            fill: false,
                            borderColor: 'rgba(255, 99, 132, 1)',
                            tension: 0.1
                        }]
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
            };

            // Function to handle chart update based on category
            const updateCharts = (category) => {
                $.ajax({
                    url: 'fetch_chart_data.php',
                    method: 'POST',
                    data: { category: category },
                    success: function (data) {
                        try {
                            const chartData = JSON.parse(data);
                            if (chartData) {
                                // Update progress chart if progressChart is defined
                                if (progressChart) {
                                    progressChart.data.labels = chartData.progressChartLabels;
                                    progressChart.data.datasets[0].data = chartData.progressChartValues;
                                    progressChart.update();
                                } else {
                                    console.error('progressChart is undefined');
                                }

                                // Update atd chart if atdChart is defined
                                if (atdChart) {
                                    atdChart.data.labels = chartData.atdChartLabels;
                                    atdChart.data.datasets[0].data = chartData.atdChartValues.map(val => parseFloat(val));
                                    atdChart.update();
                                } else {
                                    console.error('atdChart is undefined');
                                }
                            } else {
                                console.error('Invalid data format received.');
                            }
                        } catch (error) {
                            console.error('Error parsing JSON:', error);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error fetching data:', error);
                    }
                });
            };

            // Attach click event listener to each filter button after charts are initialized
            initializeCharts();
            const filterButtons = document.querySelectorAll('.filter-button');

            // Activate "U8" button and update charts on initial load
            const initialCategory = 'U8';
            const initialButton = document.querySelector(`[data-category="${initialCategory}"]`);
            initialButton.classList.add('active'); // Add active class to make it visually active
            updateCharts(initialCategory); // Update charts for initial category

            filterButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const category = this.getAttribute('data-category');
                    updateCharts(category); // Update charts for selected category

                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to the clicked button
                    this.classList.add('active');
                });
            });
        });
    </script>



</body>

</html>