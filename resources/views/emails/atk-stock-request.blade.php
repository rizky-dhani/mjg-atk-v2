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

    @php
        $statusText = match ($actionStatus) {
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'partially_approved' => 'Partially Approved',
            default => ucfirst($actionStatus),
        };
        
        $actorName = $actor ? $actor->name : 'System';
    @endphp

    @if($actionStatus === 'submitted')
        <p>The following ATK Stock Request has been <strong>Submitted</strong> by <strong>{{ $stockRequest->requester->name }}</strong>.</p>
    @else
        <p>The following ATK Stock Request has been <strong>{{ $statusText }}</strong> by <strong>{{ $actorName }}</strong>.</p>
    @endif
    
    @if($actionStatus === 'rejected' && $notes)
        <p><strong>Reason for Rejection:</strong> {{ $notes }}</p>
    @endif

    <ul>
        <li><strong>Request Number:</strong> {{ $stockRequest->request_number }}</li>
        <li><strong>Requester:</strong> {{ $stockRequest->requester->name }} ({{ $stockRequest->division->name }})</li>
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