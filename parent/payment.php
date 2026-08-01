<?php

include_once '../database.php';
include_once '../session.php';
require __DIR__ . "/../vendor/autoload.php";

$stripe_secret_key = "YOUR_STRIPE_TEST_KEY";

\Stripe\Stripe::setApiKey($stripe_secret_key);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay'])) {
    // Check if all required POST parameters are set
    if (isset($_POST['jumlah_yuran'], $_POST['nama_yuran'], $_POST['bayaranID'], $_POST['yuranID'])) {
        // Set the timezone to Kuala Lumpur
        date_default_timezone_set('Asia/Kuala_Lumpur');

        $parentID = $_SESSION['userID'];
        $feeAmountStripe = $_POST['jumlah_yuran'] * 100; // Amount in cents
        $feeName = $_POST['nama_yuran'];
        $desc = $_POST['nama_pemain'];
        $pemainID = $_POST['pemainID'];
        $paymentID = $_POST['bayaranID'];
        $feeID = $_POST['yuranID'];
        $status_bayaran = "Pending"; // Initial status
        $feeAmount = $_POST['jumlah_yuran'];

        $paymentDate = date('Y-m-d');  // Current date in YYYY-MM-DD format

        // Store payment details in session to use after redirect from Stripe
        $_SESSION['payment_data'] = [
            'bayaranID' => $paymentID,
            'yuranID' => $feeID,
            'ibubapaID' => $parentID,
            'pemainID' => $pemainID,
            'jumlah_bayaran' => $feeAmount,
            'status_bayaran' => $status_bayaran,
            'tarikh_bayaran' => $paymentDate
        ];

        // Create a new Stripe Checkout Session
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card', 'grabpay'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'myr',
                        'product_data' => [
                            'name' => $feeName . ' - ' . $desc,
                        ],
                        'unit_amount' => $feeAmountStripe,
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => 'http://localhost/spabs/parent/payment.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost/spabs/parent/fee_list.php',
        ]);

        http_response_code(303);
        header("Location: " . $checkout_session->url);
        exit();
    } else {
        echo "Missing required POST parameters.";
    }
}

if (isset($_GET['session_id']) && isset($_SESSION['payment_data'])) {
    $session_id = $_GET['session_id'];
    $payment_data = $_SESSION['payment_data'];

    try {
        $session = \Stripe\Checkout\Session::retrieve($session_id);
        $payment_intent = \Stripe\PaymentIntent::retrieve($session->payment_intent);

        if ($payment_intent->status == 'succeeded') {
            // Update payment status
            $payment_data['status_bayaran'] = 'Paid';
            $payment_method = 'Stripe Online Payment';

            // Check if the record already exists
            $stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_spabs_bayaran WHERE yuranID = :yuranID AND ibubapaID = :ibubapaID AND pemainID = :pemainID");
            $stmt->bindParam(':yuranID', $payment_data['yuranID'], PDO::PARAM_STR);
            $stmt->bindParam(':ibubapaID', $payment_data['ibubapaID'], PDO::PARAM_STR);
            $stmt->bindParam(':pemainID', $payment_data['pemainID'], PDO::PARAM_STR);
            $stmt->execute();

            $exists = $stmt->fetchColumn();

            if ($exists) {
                // Update existing record
                $stmt = $conn->prepare("UPDATE tbl_spabs_bayaran 
                                        SET jumlah_bayaran = :jumlah_bayaran, status_bayaran = :status_bayaran, tarikh_bayaran = :tarikh_bayaran, cara_bayaran = :cara_bayaran 
                                        WHERE yuranID = :yuranID AND ibubapaID = :ibubapaID AND pemainID = :pemainID");

                $stmt->bindParam(':jumlah_bayaran', $payment_data['jumlah_bayaran'], PDO::PARAM_INT);
                $stmt->bindParam(':status_bayaran', $payment_data['status_bayaran'], PDO::PARAM_STR);
                $stmt->bindParam(':tarikh_bayaran', $payment_data['tarikh_bayaran'], PDO::PARAM_STR);
                $stmt->bindParam(':cara_bayaran', $payment_method, PDO::PARAM_STR);
                $stmt->bindParam(':yuranID', $payment_data['yuranID'], PDO::PARAM_STR);
                $stmt->bindParam(':ibubapaID', $payment_data['ibubapaID'], PDO::PARAM_STR);
                $stmt->bindParam(':pemainID', $payment_data['pemainID'], PDO::PARAM_STR);

                $stmt->execute();
            } else {
                // Insert new record
                $stmt = $conn->prepare("INSERT INTO tbl_spabs_bayaran (bayaranID, yuranID, ibubapaID, pemainID, jumlah_bayaran, status_bayaran, tarikh_bayaran, cara_bayaran)
                                        VALUES (:bayaranID, :yuranID, :ibubapaID, :pemainID, :jumlah_bayaran, :status_bayaran, :tarikh_bayaran, :cara_bayaran)");

                $stmt->bindParam(':bayaranID', $payment_data['bayaranID'], PDO::PARAM_STR);
                $stmt->bindParam(':yuranID', $payment_data['yuranID'], PDO::PARAM_STR);
                $stmt->bindParam(':ibubapaID', $payment_data['ibubapaID'], PDO::PARAM_STR);
                $stmt->bindParam(':pemainID', $payment_data['pemainID'], PDO::PARAM_STR);
                $stmt->bindParam(':jumlah_bayaran', $payment_data['jumlah_bayaran'], PDO::PARAM_INT);
                $stmt->bindParam(':status_bayaran', $payment_data['status_bayaran'], PDO::PARAM_STR);
                $stmt->bindParam(':tarikh_bayaran', $payment_data['tarikh_bayaran'], PDO::PARAM_STR);
                $stmt->bindParam(':cara_bayaran', $payment_method, PDO::PARAM_STR);

                $stmt->execute();
            }

            // Clear session data
            unset($_SESSION['payment_data']);

            header("Location: success.php");
            exit();
        } else {
            echo "Payment was not successful.";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No session ID or payment data provided.";
}
?>