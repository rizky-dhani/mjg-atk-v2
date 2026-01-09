<!DOCTYPE html>
<html>
<head>
    <title>ATK Stock Request Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3b82f6;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>ATK Stock Request Update</h2>
        <p>Dear {{ $recipientName ?? 'User' }},</p>

        @php
            $statusText = match ($actionStatus) {
                'submitted' => 'Submitted',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'partially_approved' => 'Partially Approved',
                default => ucfirst($actionStatus),
            };
            
            $actorName = $actor ? $actor->name : 'System';
            $isApprover = false;
            
            // Check for the custom header we added in the service
            if (isset($this->callbacks)) {
                // This is a bit hacky for Blade but we can check the status and context
                // Alternatively, we could have passed $isApprover as a prop to the mailable
            }
        @endphp

        @if($actionStatus === 'submitted')
            <p>The following ATK Stock Request has been <strong>Submitted</strong> by <strong>{{ $stockRequest->requester->name }}</strong>.</p>
            @if(($recipientName ?? '') !== ($stockRequest->requester->name ?? ''))
                <p>Please review this request to <strong>Approve</strong> or <strong>Reject</strong>.</p>
            @endif
        @elseif($actionStatus === 'partially_approved')
            <p>The following ATK Stock Request has been <strong>{{ $statusText }}</strong> by <strong>{{ $actorName }}</strong> and is now awaiting your action.</p>
            <p>Please review this request to <strong>Approve</strong> or <strong>Reject</strong>.</p>
        @else
            <p>The following ATK Stock Request has been <strong>{{ $statusText }}</strong> by <strong>{{ $actorName }}</strong>.</p>
        @endif
        
        @if($actionStatus === 'rejected' && $notes)
            <p><strong>Reason for Rejection:</strong> {{ $notes }}</p>
        @endif

        <div style="background-color: #f9fafb; padding: 15px; border-radius: 8px; margin-top: 20px;">
            <ul style="list-style: none; padding: 0;">
                <li><strong>Request Number:</strong> {{ $stockRequest->request_number }}</li>
                <li><strong>Requester:</strong> {{ $stockRequest->requester->name }} ({{ $stockRequest->division->name }})</li>
            </ul>
        </div>

        @if($viewUrl)
            <a href="{{ $viewUrl }}" class="button">View Request Details</a>
        @endif

        <h3>Request Items:</h3>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Item</th>
                    <th style="text-align: center;">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockRequest->atkStockRequestItems as $item)
                    <tr>
                        <td>{{ $item->category->name }}</td>
                        <td>{{ $item->item->name }}</td>
                        <td style="text-align: center;">{{ $item->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p style="margin-top: 30px; font-size: 0.9em; color: #666;">
            Thank you,<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
