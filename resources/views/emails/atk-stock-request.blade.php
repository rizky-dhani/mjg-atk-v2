<!DOCTYPE html>
<html>
<head>
    <title>ATK Stock Request Update</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>ATK Stock Request Update</h2>
    <p>Dear User,</p>
    <p>The following ATK stock request has been <strong>{{ $actionStatus }}</strong>.</p>
    
    <ul>
        <li><strong>Request Number:</strong> {{ $stockRequest->request_number }}</li>
        <li><strong>Requester:</strong> {{ $stockRequest->requester->name }}</li>
        <li><strong>Division:</strong> {{ $stockRequest->division->name }}</li>
        @if($actor)
            <li><strong>Action By:</strong> {{ $actor->name }}</li>
        @endif
        @if($notes)
            <li><strong>Notes:</strong> {{ $notes }}</li>
        @endif
    </ul>

    <h3>Request Items:</h3>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Item</th>
                <th>Quantity Requested</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockRequest->atkStockRequestItems as $item)
                <tr>
                    <td>{{ $item->category->name }}</td>
                    <td>{{ $item->item->name }}</td>
                    <td>{{ $item->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>Thank you.</p>
</body>
</html>
