<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fake Payment Gateway</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .payment-container {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-logo {
            width: 60px;
            margin-bottom: 15px;
        }
        .fake-card-info {
            font-size: 0.9rem;
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="payment-container">
        <h4 class="text-center mb-3">Sewidan Fake Payment Gateway</h4>
        
        <!-- Fake Card Information -->
        <div class="fake-card-info text-center mb-3">
            <p class="mb-1"><strong>Success Test Card:</strong> 4242 4242 4242 1111</p>
            <p class="mb-1"><strong>Failed Test Card:</strong> 4242 4242 4242 0000</p>
            <p class="mb-1"><strong>MM/YY:</strong> 12/34</p>
            <p class="mb-1"><strong>CVV:</strong> 123</p>
        </div>
        
        <!-- Fake Card Information -->
        <div class="fake-card-info text-center mb-3">
            <p class="mb-1"><strong>Amount:</strong> {{$amount}}</p>
            <p class="mb-1"><strong>Client name:</strong> {{$userName}}</p>
        </div>

        <form action="{{ route('fake-gateway.process',$transactionId) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="cardNumber" class="form-label">Card Number</label>
                <input type="text" class="form-control" id="cardNumber" name="card_number" placeholder="•••• •••• •••• ••••" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="expiryDate" class="form-label">Expiration (MM/YY)</label>
                    <input type="text" class="form-control" id="expiryDate" name="expiry_date" placeholder="MM/YY" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="cvv" class="form-label">CVV</label>
                    <input type="text" class="form-control" id="cvv" name="cvv" placeholder="•••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Pay Now</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
