<?php
//include_once ("../session.php");


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPABS: Home</title>

    <!-- <link href="css/bootstrap.css" rel="stylesheet"> -->
    <link href="../css/main.css" rel="stylesheet">

    <style>

    </style>

</head>


<body>

    <div class="wrapper">

        <?php
        include_once 'admin/sidebar.php'
            ?>

        <div class="main">

            <header>
                <span>Home</span>
                <span class="user-role">Coach</span>
            </header>

            <div class="container p-5">

                <!DOCTYPE html>
                <html lang="en">

                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Invoice</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            margin: 0;
                            padding: 0;
                            background-color: #f4f4f4;
                        }

                        .invoice-container {
                            width: 80%;
                            margin: 20px auto;
                            padding: 20px;
                            background-color: #fff;
                            border-radius: 8px;
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        }

                        .header {
                            text-align: center;
                            margin-bottom: 40px;
                        }

                        .header img {
                            width: 150px;
                        }

                        .invoice-details {
                            margin-bottom: 20px;
                        }

                        .invoice-details h2 {
                            margin-bottom: 10px;
                        }

                        .info {
                            display: flex;
                            justify-content: space-between;
                        }

                        .info div {
                            width: 48%;
                        }

                        .info h3 {
                            margin-bottom: 5px;
                        }

                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 20px;
                        }

                        table,
                        th,
                        td {
                            border: 1px solid #ddd;
                        }

                        th,
                        td {
                            padding: 15px;
                            text-align: left;
                        }

                        th {
                            background-color: #f4f4f4;
                        }

                        .total {
                            text-align: right;
                            margin-top: 20px;
                        }
                    </style>
                </head>

                <body>
                    <div class="invoice-container">
                        <div class="header">
                            <h1>School Name</h1>
                            <p>School Address, City, State, Zip Code</p>
                            <p>Phone: (123) 456-7890 | Email: info@school.com</p>
                        </div>
                        <div class="invoice-details">
                            <h2>Invoice</h2>
                            <p><strong>Invoice Number:</strong> #INV-123456</p>
                            <p><strong>Date:</strong> June 21, 2024</p>
                        </div>
                        <div class="info">
                            <div>
                                <h3>Parent Information</h3>
                                <p>Name: John Doe</p>
                                <p>Address: 123 Main Street, City, State, Zip Code</p>
                                <p>Phone: (123) 456-7890</p>
                                <p>Email: johndoe@example.com</p>
                            </div>
                            <div>
                                <h3>Student Information</h3>
                                <p>Name: Jane Doe</p>
                                <p>Grade: 5</p>
                                <p>Student ID: 78910</p>
                            </div>
                        </div>
                        <table>
                            <tr>
                                <th>Description</th>
                                <th>Amount</th>
                            </tr>
                            <tr>
                                <td>Tuition Fee</td>
                                <td>$500.00</td>
                            </tr>
                            <tr>
                                <td>Books and Supplies</td>
                                <td>$150.00</td>
                            </tr>
                            <tr>
                                <td>Lab Fee</td>
                                <td>$50.00</td>
                            </tr>
                            <tr>
                                <td>Sports Fee</td>
                                <td>$100.00</td>
                            </tr>
                        </table>
                        <div class="total">
                            <h3>Total: $800.00</h3>
                        </div>
                    </div>
                </body>

                </html>


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

    <?php
    // Get the current date in the format MySQL expects (YYYY-MM-DD)
    $currentDate = date('Y-m-d');

    $stmt = $conn->prepare("SELECT aktivitiID, nama_aktiviti, tarikh_aktiviti, masa_mula, masa_tamat, kategori 
                            FROM tbl_spabs_aktiviti WHERE kategori = :category AND tarikh_aktiviti >= CURDATE()");
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



</body>

</html>